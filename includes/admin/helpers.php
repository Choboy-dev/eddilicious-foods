<?php

/**
 * Admin helpers — require logged-in admin user.
 */
require_once dirname(__DIR__) . '/config.php';

function admin_require_api(): void
{
    if (!AuthSession::isAdmin()) {
        auth_json_response(['ok' => false, 'error' => 'You do not have access to the admin area.'], 403);
        exit;
    }
}

function admin_require_csrf(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null);
    if (!auth_verify_csrf(is_string($token) ? $token : null)) {
        auth_json_response(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 419);
        exit;
    }
}

/**
 * @return array<string, mixed>
 */
function admin_read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function admin_format_pence(int $pence): string
{
    return format_price($pence / 100);
}

function admin_format_order(array $order): array
{
    $cart = json_decode((string) ($order['cart_json'] ?? '[]'), true);

    return [
        'id' => (int) ($order['id'] ?? 0),
        'order_ref' => (string) ($order['order_ref'] ?? ''),
        'status' => (string) ($order['status'] ?? ''),
        'email' => (string) ($order['email'] ?? ''),
        'customer_name' => (string) ($order['customer_name'] ?? ''),
        'phone' => (string) ($order['phone'] ?? ''),
        'fulfillment' => (string) ($order['fulfillment'] ?? ''),
        'subtotal_pence' => (int) ($order['subtotal_pence'] ?? 0),
        'discount_pence' => (int) ($order['discount_pence'] ?? 0),
        'total_pence' => (int) ($order['total_pence'] ?? 0),
        'total_label' => admin_format_pence((int) ($order['total_pence'] ?? 0)),
        'coupon_code' => $order['coupon_code'] ?? null,
        'item_count' => is_array($cart) ? count($cart) : 0,
        'created_at' => (string) ($order['created_at'] ?? ''),
        'paid_at' => $order['paid_at'] ?? null,
    ];
}
