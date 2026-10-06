<?php

class TwilioWhatsAppNotifier
{
    /**
     * @param array<string, mixed> $order
     * @return array{ok: bool, error?: string}
     */
    public static function sendNewOrderAlert(array $order): array
    {
        if (!auth_twilio_whatsapp_configured()) {
            return ['ok' => false, 'error' => 'Twilio WhatsApp is not set up yet.'];
        }

        if (!auth_twilio_whatsapp_notify_enabled()) {
            return ['ok' => false, 'error' => 'WhatsApp order alerts are turned off.'];
        }

        if (auth_stripe_is_test_mode() && !auth_twilio_whatsapp_notify_test_orders()) {
            return ['ok' => false, 'error' => 'Test orders are not sent to WhatsApp.'];
        }

        $recipients = auth_twilio_notify_whatsapp_numbers();
        if ($recipients === []) {
            return ['ok' => false, 'error' => 'No WhatsApp number is set for order alerts.'];
        }

        $message = self::buildOrderMessage($order);
        $lastError = null;

        foreach ($recipients as $recipient) {
            $send = self::sendMessage($recipient, $message);
            if (!$send['ok']) {
                $lastError = $send['error'] ?? 'Send failed.';
            }
        }

        if ($lastError !== null) {
            return ['ok' => false, 'error' => $lastError];
        }

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $booking
     * @return array{ok: bool, error?: string}
     */
    public static function sendCateringBookingAlert(array $booking): array
    {
        if (!auth_twilio_whatsapp_configured()) {
            return ['ok' => false, 'error' => 'Twilio WhatsApp is not set up yet.'];
        }

        if (!auth_twilio_whatsapp_notify_enabled()) {
            return ['ok' => false, 'error' => 'WhatsApp alerts are turned off.'];
        }

        $recipients = auth_twilio_notify_whatsapp_numbers();
        if ($recipients === []) {
            return ['ok' => false, 'error' => 'No WhatsApp number is set for alerts.'];
        }

        $message = self::buildCateringMessage($booking);
        $lastError = null;

        foreach ($recipients as $recipient) {
            $send = self::sendMessage($recipient, $message);
            if (!$send['ok']) {
                $lastError = $send['error'] ?? 'Send failed.';
            }
        }

        if ($lastError !== null) {
            return ['ok' => false, 'error' => $lastError];
        }

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $order
     */
    private static function buildOrderMessage(array $order): string
    {
        $ref = (string) ($order['order_ref'] ?? '');
        $name = trim((string) ($order['customer_name'] ?? ''));
        $email = (string) ($order['email'] ?? '');
        $phone = trim((string) ($order['phone'] ?? ''));
        $fulfillment = (string) ($order['fulfillment'] ?? 'delivery');
        $fulfillmentLabel = $fulfillment === 'collection' ? 'Collection' : 'Delivery';
        $total = format_price(((int) ($order['total_pence'] ?? 0)) / 100);
        $discount = (int) ($order['discount_pence'] ?? 0);
        $coupon = trim((string) ($order['coupon_code'] ?? ''));
        $notes = trim((string) ($order['notes'] ?? ''));
        $adminUrl = rtrim(auth_app_url(), '/') . '/admin/orders.php?status=paid';

        $lines = [
            'New order — Eddilicious Foods',
            '',
            'Ref: ' . $ref,
            'Customer: ' . ($name !== '' ? $name : 'Not given'),
            'Phone: ' . ($phone !== '' ? $phone : 'Not given'),
            'Email: ' . $email,
            'Type: ' . $fulfillmentLabel,
            'Total: ' . $total,
        ];

        if ($discount > 0) {
            $lines[] = 'Discount: -' . format_price($discount / 100) . ($coupon !== '' ? ' (' . $coupon . ')' : '');
        }

        $itemLines = self::formatCartLines($order);
        if ($itemLines !== []) {
            $lines[] = '';
            $lines[] = 'Items:';
            $lines = array_merge($lines, $itemLines);
        }

        if ($notes !== '') {
            $lines[] = '';
            $lines[] = 'Notes: ' . $notes;
        }

        $lines[] = '';
        $lines[] = 'Admin: ' . $adminUrl;

        $message = implode("\n", $lines);

        if (strlen($message) > 1500) {
            return substr($message, 0, 1497) . '...';
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $order
     * @return list<string>
     */
    private static function formatCartLines(array $order): array
    {
        $cart = json_decode((string) ($order['cart_json'] ?? '[]'), true);
        if (!is_array($cart)) {
            return [];
        }

        $lines = [];
        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? 'Item'));
            $units = (int) ($item['stripe_quantity'] ?? $item['units'] ?? 1);
            $label = trim((string) ($item['quantity_label'] ?? ''));
            $line = $units > 1 ? $units . 'x ' . $name : $name;
            if ($label !== '' && $label !== $name) {
                $line .= ' (' . $label . ')';
            }
            $lines[] = '- ' . $line;
        }

        return $lines;
    }

    /**
     * @param array<string, mixed> $booking
     */
    private static function buildCateringMessage(array $booking): string
    {
        $name = trim((string) ($booking['name'] ?? ''));
        $phone = trim((string) ($booking['phone'] ?? ''));
        $email = (string) ($booking['email'] ?? '');
        $eventType = (string) ($booking['event_type_label'] ?? $booking['event_type'] ?? '');
        $eventDate = (string) ($booking['event_date'] ?? '');
        $eventTime = (string) ($booking['event_time'] ?? '');
        $guests = (int) ($booking['guest_count'] ?? 0);
        $channel = (string) ($booking['channel_label'] ?? $booking['channel'] ?? '');
        $location = trim((string) ($booking['location'] ?? ''));
        $notes = trim((string) ($booking['notes'] ?? ''));

        $lines = [
            'Catering booking — Eddilicious Foods',
            '',
            'Name: ' . ($name !== '' ? $name : 'Not given'),
            'Phone: ' . ($phone !== '' ? $phone : 'Not given'),
            'Email: ' . $email,
            'Event: ' . $eventType,
            'Date: ' . $eventDate . ' at ' . $eventTime,
            'Guests: ' . $guests,
            'Location: ' . ($location !== '' ? $location : 'Not given'),
            'Reply via: ' . $channel,
        ];

        $drinkLines = [];
        $drinks = $booking['drinks'] ?? [];
        if (is_array($drinks)) {
            foreach ($drinks as $drink) {
                if (!is_array($drink)) {
                    continue;
                }
                $drinkName = trim((string) ($drink['name'] ?? ''));
                $sizeLabel = trim((string) ($drink['size_label'] ?? ''));
                if ($drinkName !== '') {
                    $drinkLines[] = '- ' . $drinkName . ($sizeLabel !== '' ? ' (' . $sizeLabel . ')' : '');
                }
            }
        }

        $lines[] = '';
        $lines[] = 'Drinks:';
        $lines = array_merge($lines, $drinkLines !== [] ? $drinkLines : ['- None selected']);

        if ($notes !== '') {
            $lines[] = '';
            $lines[] = 'Notes: ' . $notes;
        }

        $message = implode("\n", $lines);

        if (strlen($message) > 1500) {
            return substr($message, 0, 1497) . '...';
        }

        return $message;
    }

    private static function sendMessage(string $toNumber, string $body): array
    {
        $accountSid = (string) auth_env('TWILIO_ACCOUNT_SID');
        $authToken = (string) auth_env('TWILIO_AUTH_TOKEN');
        $from = auth_twilio_whatsapp_from();
        $to = self::formatWhatsAppAddress($toNumber);

        if ($to === null) {
            return ['ok' => false, 'error' => 'That WhatsApp number is not valid.'];
        }

        $contentSid = trim((string) auth_env('TWILIO_WHATSAPP_CONTENT_SID', ''));
        $payload = [
            'From' => $from,
            'To' => $to,
        ];

        if ($contentSid !== '') {
            $payload['ContentSid'] = $contentSid;
            $payload['ContentVariables'] = json_encode(['1' => $body], JSON_UNESCAPED_UNICODE);
        } else {
            $payload['Body'] = $body;
        }

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($accountSid) . '/Messages.json';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $accountSid . ':' . $authToken,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'We could not reach Twilio. ' . $curlError];
        }

        if ($status < 200 || $status >= 300) {
            $decoded = json_decode($response, true);
            $message = is_array($decoded) ? (string) ($decoded['message'] ?? 'Send failed') : 'Send failed';

            return ['ok' => false, 'error' => 'Twilio could not send the WhatsApp message. ' . $message];
        }

        return ['ok' => true];
    }

    private static function formatWhatsAppAddress(string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '44' . substr($digits, 1);
        }

        if (!str_starts_with($digits, '44') && strlen($digits) === 10) {
            return null;
        }

        return 'whatsapp:+' . $digits;
    }
}
