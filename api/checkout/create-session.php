<?php
require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/data/coupons.php';
require_once dirname(__DIR__, 2) . '/includes/payments/CartValidator.php';
require_once dirname(__DIR__, 2) . '/includes/payments/OrderRepository.php';
require_once dirname(__DIR__, 2) . '/includes/payments/StripeService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$body = auth_read_json_body();
$csrf = $body['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

if (!auth_verify_csrf(is_string($csrf) ? $csrf : null)) {
    auth_json_response(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 403);
}

if (!AuthSession::isLoggedIn()) {
    auth_json_response([
        'ok' => false,
        'error' => 'Please sign in or create an account before you pay.',
        'auth_required' => true,
        'redirect' => 'signup.php?redirect=' . rawurlencode('checkout.php'),
    ], 401);
}

$cartItems = $body['cart'] ?? [];
if (!is_array($cartItems)) {
    auth_json_response(['ok' => false, 'error' => 'Your basket looks wrong. Refresh and try again.'], 422);
}

$fulfillment = (string) ($body['fulfillment'] ?? 'delivery');
$couponCode = isset($body['coupon_code']) ? (string) $body['coupon_code'] : null;

$sessionUser = AuthSession::user();
$customer = [
    'first_name' => trim((string) ($body['first_name'] ?? '')),
    'last_name' => trim((string) ($body['last_name'] ?? '')),
    'email' => auth_sanitize_email((string) ($sessionUser['email'] ?? ($body['email'] ?? ''))),
    'phone' => trim((string) ($body['phone'] ?? '')),
    'address_line1' => trim((string) ($body['address_line1'] ?? '')),
    'address_line2' => trim((string) ($body['address_line2'] ?? '')),
    'city' => trim((string) ($body['city'] ?? '')),
    'postcode' => trim((string) ($body['postcode'] ?? '')),
];

if ($customer['first_name'] === '' || $customer['last_name'] === '' || $customer['email'] === '' || $customer['phone'] === '') {
    auth_json_response(['ok' => false, 'error' => 'Please fill in your name, email, and phone.'], 422);
}

if (!auth_is_valid_email($customer['email'])) {
    auth_json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

if ($fulfillment === 'delivery') {
    if ($customer['address_line1'] === '' || $customer['city'] === '' || $customer['postcode'] === '') {
        auth_json_response(['ok' => false, 'error' => 'Please fill in your delivery address.'], 422);
    }
}

if (empty($body['allergy_agreement_accepted'])) {
    auth_json_response(['ok' => false, 'error' => 'Please read and accept our allergy policy before you pay.'], 422);
}

if ($couponCode) {
    $coupon = find_coupon($couponCode);
    if (!$coupon) {
        auth_json_response(['ok' => false, 'error' => 'That coupon code did not work.'], 422);
    }

    if (!empty($coupon['once_per_customer']) && OrderRepository::couponUsedByEmail($customer['email'], $couponCode)) {
        auth_json_response(['ok' => false, 'error' => 'You have already used this coupon.'], 422);
    }
}

$pricing = CartValidator::validate($cartItems, $couponCode, $fulfillment);
if (!$pricing['ok']) {
    auth_json_response(['ok' => false, 'error' => $pricing['error'] ?? 'We could not price your order.'], 422);
}

try {
    $customerName = trim($customer['first_name'] . ' ' . $customer['last_name']);
    $notes = trim((string) ($body['notes'] ?? ''));

    $order = OrderRepository::createPending([
        'email' => $customer['email'],
        'customer_name' => $customerName,
        'phone' => $customer['phone'],
        'fulfillment' => $fulfillment,
        'subtotal_pence' => $pricing['subtotal_pence'],
        'discount_pence' => $pricing['discount_pence'],
        'delivery_pence' => $pricing['delivery_pence'],
        'total_pence' => $pricing['total_pence'],
        'coupon_code' => $pricing['coupon_code'],
        'cart' => $pricing['lines'],
        'customer' => $customer,
        'notes' => $notes,
    ]);

    $stripeLines = CartValidator::buildStripeLineItems(
        $pricing['lines'],
        (int) $pricing['discount_pence'],
        (int) $pricing['subtotal_pence']
    );

    $session = StripeService::createCheckoutSession($order, $stripeLines);
    if (!$session['ok']) {
        auth_json_response(['ok' => false, 'error' => $session['error'] ?? 'Payment failed to start.'], 502);
    }

    auth_json_response([
        'ok' => true,
        'checkout_url' => $session['url'],
        'order_ref' => $order['order_ref'] ?? '',
    ]);
} catch (Throwable $exception) {
    auth_json_response([
        'ok' => false,
        'error' => 'We could not start payment. Check that the storage folder is writable on the server.',
    ], 500);
}
