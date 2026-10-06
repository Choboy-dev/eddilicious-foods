<?php

require_once dirname(__DIR__, 2) . '/includes/admin/helpers.php';

admin_require_api();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    auth_json_response([
        'ok' => true,
        'active_coupon' => CouponAdminRepository::getActivePromo(),
        'coupons' => CouponAdminRepository::listAll(),
    ]);
    exit;
}

admin_require_csrf();
$body = admin_read_json_body();
$action = (string) ($body['action'] ?? 'save');

try {
    if ($action === 'delete') {
        CouponAdminRepository::delete((int) ($body['id'] ?? 0));
        auth_json_response([
            'ok' => true,
            'active_coupon' => CouponAdminRepository::getActivePromo(),
            'coupons' => CouponAdminRepository::listAll(),
        ]);
        exit;
    }

    $coupon = CouponAdminRepository::save($body);
    auth_json_response([
        'ok' => true,
        'coupon' => $coupon,
        'active_coupon' => CouponAdminRepository::getActivePromo(),
        'coupons' => CouponAdminRepository::listAll(),
    ]);
} catch (InvalidArgumentException $exception) {
    auth_json_response(['ok' => false, 'error' => $exception->getMessage()], 422);
}
