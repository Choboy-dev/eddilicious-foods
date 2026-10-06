<?php

class StripeService
{
    public static function client(): ?\Stripe\StripeClient
    {
        if (!auth_stripe_configured()) {
            return null;
        }

        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_readable($autoload)) {
            return null;
        }

        require_once $autoload;

        return new \Stripe\StripeClient((string) auth_env('STRIPE_SECRET_KEY'));
    }

    /**
     * @param array<string, mixed> $order
     * @param list<array<string, mixed>> $stripeLineItems
     * @return array{ok: bool, error?: string, url?: string, session_id?: string}
     */
    public static function createCheckoutSession(array $order, array $stripeLineItems): array
    {
        $stripe = self::client();
        if (!$stripe) {
            return [
                'ok' => false,
                'error' => 'Card payments are not set up yet. Add your Stripe keys to the .env file.',
            ];
        }

        $appUrl = rtrim(auth_app_url(), '/');
        $orderRef = (string) ($order['order_ref'] ?? '');

        try {
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => $stripeLineItems,
                'customer_email' => (string) ($order['email'] ?? ''),
                'client_reference_id' => $orderRef,
                'success_url' => $appUrl . '/checkout-success.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $appUrl . '/checkout.php?cancelled=1',
                'metadata' => [
                    'order_ref' => $orderRef,
                    'fulfillment' => (string) ($order['fulfillment'] ?? 'delivery'),
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_ref' => $orderRef,
                    ],
                ],
            ]);

            if (empty($session->id) || empty($session->url)) {
                return ['ok' => false, 'error' => 'We could not start payment. Try again.'];
            }

            OrderRepository::attachStripeSession($orderRef, (string) $session->id);

            return [
                'ok' => true,
                'url' => (string) $session->url,
                'session_id' => (string) $session->id,
            ];
        } catch (\Stripe\Exception\ApiErrorException $exception) {
            return [
                'ok' => false,
                'error' => 'Payment could not be started. Check your Stripe keys and try again.',
            ];
        }
    }

    /**
     * @return array{ok: bool, error?: string, session?: \Stripe\Checkout\Session, order?: array}
     */
    public static function verifyCheckoutSession(string $sessionId): array
    {
        $stripe = self::client();
        if (!$stripe) {
            return ['ok' => false, 'error' => 'Payments are not configured.'];
        }

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent'],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $exception) {
            return ['ok' => false, 'error' => 'We could not find that payment.'];
        }

        if (($session->payment_status ?? '') !== 'paid') {
            return ['ok' => false, 'error' => 'This payment is not complete yet.'];
        }

        $paymentIntent = is_object($session->payment_intent) ? $session->payment_intent->id : (string) ($session->payment_intent ?? '');
        $order = OrderRepository::markPaidBySessionId($sessionId, $paymentIntent !== '' ? $paymentIntent : null);

        if (!$order) {
            return ['ok' => false, 'error' => 'We could not match this payment to your order.'];
        }

        return [
            'ok' => true,
            'session' => $session,
            'order' => $order,
        ];
    }

    public static function handleWebhook(string $payload, ?string $signature): array
    {
        $secret = auth_env('STRIPE_WEBHOOK_SECRET');
        if (!$secret) {
            return ['ok' => false, 'error' => 'Webhook secret not configured.', 'status' => 503];
        }

        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_readable($autoload)) {
            return ['ok' => false, 'error' => 'Stripe library missing.', 'status' => 503];
        }

        require_once $autoload;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                (string) $signature,
                $secret
            );
        } catch (\UnexpectedValueException|\Stripe\Exception\SignatureVerificationException $exception) {
            return ['ok' => false, 'error' => 'Invalid webhook.', 'status' => 400];
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            if (!empty($session->id)) {
                $paymentIntent = is_string($session->payment_intent) ? $session->payment_intent : null;
                OrderRepository::markPaidBySessionId((string) $session->id, $paymentIntent);
            }
        }

        if ($event->type === 'checkout.session.expired') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            if (!empty($session->id)) {
                OrderRepository::markFailedBySessionId((string) $session->id, 'Checkout session expired.');
            }
        }

        if ($event->type === 'payment_intent.payment_failed') {
            /** @var \Stripe\PaymentIntent $intent */
            $intent = $event->data->object;
            $sessionId = (string) ($intent->metadata->checkout_session_id ?? '');
            if ($sessionId !== '') {
                OrderRepository::markFailedBySessionId($sessionId, 'Card payment failed.');
            }
        }

        return ['ok' => true, 'status' => 200];
    }
}
