<?php

class ResendMailer
{
    public static function sendOtp(string $email, string $code, string $purpose): array
    {
        $subject = $purpose === 'signup'
            ? 'Your Eddilicious Foods sign-up code'
            : 'Your Eddilicious Foods sign-in code';

        $html = self::buildOtpEmailHtml($code, $purpose);
        $text = self::buildOtpEmailText($code, $purpose);

        return self::send([auth_sanitize_email($email)], $subject, $html, $text);
    }

    public static function sendNewsletterSignup(string $subscriberEmail): array
    {
        $notifyEmail = auth_site_notify_email();
        $subscriberEmail = auth_sanitize_email($subscriberEmail);
        $when = gmdate('j M Y, H:i') . ' UTC';

        $ownerHtml = self::buildNewsletterOwnerHtml($subscriberEmail, $when);
        $ownerText = "New newsletter signup\n\nEmail: {$subscriberEmail}\nTime: {$when}\n";

        $ownerSend = self::send(
            [$notifyEmail],
            'New newsletter signup — Eddilicious Foods',
            $ownerHtml,
            $ownerText
        );

        if (!$ownerSend['ok']) {
            return $ownerSend;
        }

        $welcomeHtml = self::buildNewsletterWelcomeHtml();
        $welcomeText = "Thanks for joining our deals list.\n\nWe will email you when we have offers and news. You can unsubscribe any time using the link in our emails.\n";

        self::send(
            [$subscriberEmail],
            'You are on our deals list — Eddilicious Foods',
            $welcomeHtml,
            $welcomeText
        );

        return ['ok' => true];
    }

    /**
     * @param array{name: string, email: string, phone: string, topic: string, topic_label: string, message: string} $data
     */
    public static function sendContactMessage(array $data): array
    {
        $notifyEmail = auth_site_notify_email();
        $name = trim($data['name']);
        $email = auth_sanitize_email($data['email']);
        $phone = trim($data['phone']);
        $topicLabel = trim($data['topic_label']);
        $message = trim($data['message']);
        $when = gmdate('j M Y, H:i') . ' UTC';
        $isComplaint = ($data['topic'] ?? '') === 'complaint';

        $subject = $isComplaint
            ? 'Complaint from ' . $name . ' — Eddilicious Foods'
            : 'Contact form: ' . $topicLabel . ' — Eddilicious Foods';

        $ownerHtml = self::buildContactOwnerHtml($name, $email, $phone, $topicLabel, $message, $when, $isComplaint);
        $ownerText = self::buildContactOwnerText($name, $email, $phone, $topicLabel, $message, $when, $isComplaint);

        return self::send(
            [$notifyEmail],
            $subject,
            $ownerHtml,
            $ownerText,
            $email
        );
    }

    /**
     * @param list<string> $to
     */
    private static function send(array $to, string $subject, string $html, string $text, ?string $replyTo = null): array
    {
        $apiKey = auth_env('RESEND_API_KEY');
        $from = auth_env('RESEND_FROM_EMAIL', 'Eddilicious Foods <onboarding@resend.dev>');

        if (!$apiKey) {
            return ['ok' => false, 'error' => 'Email is not set up yet. Add RESEND_API_KEY to your .env file.'];
        }

        $payload = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
        ];

        if ($replyTo !== null && auth_is_valid_email($replyTo)) {
            $payload['reply_to'] = $replyTo;
        }

        $payload = json_encode($payload);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'We could not send the email. ' . $curlError];
        }

        if ($status < 200 || $status >= 300) {
            $decoded = json_decode($response, true);
            $message = is_array($decoded) ? ($decoded['message'] ?? $decoded['error'] ?? 'Send failed') : 'Send failed';

            return ['ok' => false, 'error' => 'We could not send the email. ' . $message];
        }

        return ['ok' => true];
    }

    private static function buildOtpEmailHtml(string $code, string $purpose): string
    {
        $ttl = OtpRepository::ttlMinutes();
        $action = $purpose === 'signup' ? 'finish signing up' : 'sign in';
        $digits = str_split($code);
        $digitCells = '';

        foreach ($digits as $digit) {
            $digitCells .= '<td style="width:44px;height:52px;text-align:center;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:22px;font-weight:600;color:#0f172a;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;">'
                . htmlspecialchars($digit, ENT_QUOTES, 'UTF-8')
                . '</td><td style="width:8px;"></td>';
        }

        $appUrl = htmlspecialchars(auth_app_url(), ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your code</title>
</head>
<body style="margin:0;padding:0;background:#f6f8f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8f7;padding:40px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:440px;background:#ffffff;border:1px solid #e8eeeb;border-radius:12px;overflow:hidden;">
<tr><td style="padding:28px 28px 8px;">
<p style="margin:0 0 8px;font-size:12px;font-weight:600;letter-spacing:0.04em;color:#64748b;">Eddilicious Foods</p>
<h1 style="margin:0 0 12px;font-size:20px;line-height:1.3;font-weight:600;color:#0f172a;">Your one-time code</h1>
<p style="margin:0 0 24px;font-size:14px;line-height:1.5;color:#64748b;">Use this code to ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '. It expires in ' . $ttl . ' minutes.</p>
<table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px;"><tr>' . $digitCells . '</tr></table>
<p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#94a3b8;">If you did not request this, you can ignore this email.</p>
</td></tr>
<tr><td style="padding:16px 28px 24px;border-top:1px solid #f1f5f3;">
<p style="margin:0;font-size:12px;line-height:1.4;color:#94a3b8;">' . $appUrl . '</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }

    private static function buildOtpEmailText(string $code, string $purpose): string
    {
        $ttl = OtpRepository::ttlMinutes();
        $action = $purpose === 'signup' ? 'finish signing up' : 'sign in';

        return "Eddilicious Foods\n\nYour one-time code: {$code}\n\nUse this code to {$action}. It expires in {$ttl} minutes.\n\nIf you did not request this, you can ignore this email.\n";
    }

    private static function buildNewsletterOwnerHtml(string $subscriberEmail, string $when): string
    {
        $safeEmail = htmlspecialchars($subscriberEmail, ENT_QUOTES, 'UTF-8');
        $safeWhen = htmlspecialchars($when, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>New newsletter signup</title></head>
<body style="margin:0;padding:0;background:#f6f8f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8f7;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:440px;background:#ffffff;border:1px solid #e8eeeb;border-radius:12px;">
<tr><td style="padding:24px;">
<p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#64748b;">Eddilicious Foods</p>
<h1 style="margin:0 0 16px;font-size:18px;font-weight:600;">New newsletter signup</h1>
<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Email:</strong> ' . $safeEmail . '</p>
<p style="margin:0;font-size:14px;line-height:1.5;color:#334155;"><strong>Time:</strong> ' . $safeWhen . '</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }

    private static function buildNewsletterWelcomeHtml(): string
    {
        $appUrl = htmlspecialchars(auth_app_url(), ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>You are on our deals list</title></head>
<body style="margin:0;padding:0;background:#f6f8f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8f7;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:440px;background:#ffffff;border:1px solid #e8eeeb;border-radius:12px;">
<tr><td style="padding:24px;">
<p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#64748b;">Eddilicious Foods</p>
<h1 style="margin:0 0 12px;font-size:20px;font-weight:600;">You are on our deals list</h1>
<p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#475569;">Thanks for signing up. We will email you when we have offers and news about our food.</p>
<p style="margin:0;font-size:13px;line-height:1.5;color:#94a3b8;">You can unsubscribe any time using the link in our emails.</p>
</td></tr>
<tr><td style="padding:14px 24px 20px;border-top:1px solid #f1f5f3;">
<p style="margin:0;font-size:12px;color:#94a3b8;">' . $appUrl . '</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }

    private static function buildContactOwnerHtml(
        string $name,
        string $email,
        string $phone,
        string $topicLabel,
        string $message,
        string $when,
        bool $isComplaint
    ): string {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safePhone = htmlspecialchars($phone !== '' ? $phone : 'Not given', ENT_QUOTES, 'UTF-8');
        $safeTopic = htmlspecialchars($topicLabel, ENT_QUOTES, 'UTF-8');
        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $safeWhen = htmlspecialchars($when, ENT_QUOTES, 'UTF-8');
        $heading = $isComplaint ? 'New complaint' : 'New contact form message';

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</title></head>
<body style="margin:0;padding:0;background:#f6f8f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8f7;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border:1px solid #e8eeeb;border-radius:12px;">
<tr><td style="padding:24px;">
<p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#64748b;">Eddilicious Foods</p>
<h1 style="margin:0 0 16px;font-size:18px;font-weight:600;">' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h1>
<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Name:</strong> ' . $safeName . '</p>
<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Email:</strong> ' . $safeEmail . '</p>
<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Phone:</strong> ' . $safePhone . '</p>
<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Topic:</strong> ' . $safeTopic . '</p>
<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Time:</strong> ' . $safeWhen . '</p>
<p style="margin:16px 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>Message:</strong></p>
<p style="margin:0;font-size:14px;line-height:1.6;color:#334155;">' . $safeMessage . '</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }

    private static function buildContactOwnerText(
        string $name,
        string $email,
        string $phone,
        string $topicLabel,
        string $message,
        string $when,
        bool $isComplaint
    ): string {
        $heading = $isComplaint ? 'New complaint' : 'New contact form message';
        $phoneLine = $phone !== '' ? $phone : 'Not given';

        return "Eddilicious Foods\n\n{$heading}\n\n"
            . "Name: {$name}\n"
            . "Email: {$email}\n"
            . "Phone: {$phoneLine}\n"
            . "Topic: {$topicLabel}\n"
            . "Time: {$when}\n\n"
            . "Message:\n{$message}\n";
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function sendCateringBooking(array $data): array
    {
        $notifyEmail = auth_site_notify_email();
        $when = gmdate('j M Y, H:i') . ' UTC';

        $ownerHtml = self::buildCateringOwnerHtml($data, $when);
        $ownerText = self::buildCateringOwnerText($data, $when);

        $ownerSend = self::send(
            [$notifyEmail],
            'Catering booking request — ' . trim((string) ($data['name'] ?? 'Guest')) . ' — Eddilicious Foods',
            $ownerHtml,
            $ownerText,
            (string) ($data['email'] ?? '')
        );

        if (!$ownerSend['ok']) {
            return $ownerSend;
        }

        $customerEmail = auth_sanitize_email((string) ($data['email'] ?? ''));
        if (!auth_is_valid_email($customerEmail)) {
            return ['ok' => true];
        }

        $confirmHtml = self::buildCateringCustomerHtml($data);
        $confirmText = self::buildCateringCustomerText($data);

        self::send(
            [$customerEmail],
            'We got your catering request — Eddilicious Foods',
            $confirmHtml,
            $confirmText
        );

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function buildCateringOwnerHtml(array $data, string $when): string
    {
        $rows = self::cateringDetailRows($data);
        $rowHtml = '';

        foreach ($rows as $label => $value) {
            $rowHtml .= '<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#334155;"><strong>'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> '
                . nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'))
                . '</p>';
        }

        $safeWhen = htmlspecialchars($when, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>New catering booking</title></head>
<body style="margin:0;padding:0;background:#f6f8f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8f7;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e8eeeb;border-radius:12px;">
<tr><td style="padding:24px;">
<p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#64748b;">Eddilicious Foods</p>
<h1 style="margin:0 0 16px;font-size:18px;font-weight:600;">New catering booking request</h1>
' . $rowHtml . '
<p style="margin:16px 0 0;font-size:14px;line-height:1.5;color:#334155;"><strong>Received:</strong> ' . $safeWhen . '</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function buildCateringOwnerText(array $data, string $when): string
    {
        $lines = ["Eddilicious Foods\n", "New catering booking request\n"];

        foreach (self::cateringDetailRows($data) as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }

        $lines[] = 'Received: ' . $when;

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function buildCateringCustomerHtml(array $data): string
    {
        $safeName = htmlspecialchars(trim((string) ($data['name'] ?? '')), ENT_QUOTES, 'UTF-8');
        $eventDate = htmlspecialchars((string) ($data['event_date'] ?? ''), ENT_QUOTES, 'UTF-8');
        $eventTime = htmlspecialchars((string) ($data['event_time'] ?? ''), ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>We got your catering request</title></head>
<body style="margin:0;padding:0;background:#f6f8f7;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f8f7;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border:1px solid #e8eeeb;border-radius:12px;">
<tr><td style="padding:24px;">
<p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#64748b;">Eddilicious Foods</p>
<h1 style="margin:0 0 12px;font-size:20px;font-weight:600;">We got your catering request</h1>
<p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#475569;">Hi ' . $safeName . ', thanks for getting in touch. We have your event details for <strong>' . $eventDate . '</strong> at <strong>' . $eventTime . '</strong>.</p>
<p style="margin:0;font-size:14px;line-height:1.6;color:#475569;">We will reply soon using the contact method you picked.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function buildCateringCustomerText(array $data): string
    {
        $name = trim((string) ($data['name'] ?? ''));
        $eventDate = (string) ($data['event_date'] ?? '');
        $eventTime = (string) ($data['event_time'] ?? '');

        return "Eddilicious Foods\n\nHi {$name},\n\nThanks for your catering request. We have your event on {$eventDate} at {$eventTime}.\n\nWe will reply soon using the contact method you picked.\n";
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private static function cateringDetailRows(array $data): array
    {
        $drinks = $data['drinks'] ?? [];
        $drinkLines = [];

        if (is_array($drinks)) {
            foreach ($drinks as $drink) {
                if (!is_array($drink)) {
                    continue;
                }
                $drinkName = trim((string) ($drink['name'] ?? ''));
                $sizeLabel = trim((string) ($drink['size_label'] ?? ''));
                if ($drinkName !== '') {
                    $drinkLines[] = $drinkName . ($sizeLabel !== '' ? ' (' . $sizeLabel . ')' : '');
                }
            }
        }

        $location = trim((string) ($data['location'] ?? ''));

        return [
            'Name' => trim((string) ($data['name'] ?? '')),
            'Email' => (string) ($data['email'] ?? ''),
            'Phone' => trim((string) ($data['phone'] ?? '')),
            'Event type' => (string) ($data['event_type_label'] ?? $data['event_type'] ?? ''),
            'Date' => (string) ($data['event_date'] ?? ''),
            'Time' => (string) ($data['event_time'] ?? ''),
            'Location' => $location !== '' ? $location : 'Not given',
            'Guests' => (string) ((int) ($data['guest_count'] ?? 0)),
            'Contact via' => (string) ($data['channel_label'] ?? $data['channel'] ?? ''),
            'Drinks' => $drinkLines !== [] ? implode(', ', $drinkLines) : 'None selected',
            'Notes' => trim((string) ($data['notes'] ?? '')) !== '' ? trim((string) ($data['notes'] ?? '')) : 'None',
        ];
    }
}
