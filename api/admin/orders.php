<?php

require_once dirname(__DIR__, 2) . '/includes/admin/helpers.php';

admin_require_api();

$status = $_GET['status'] ?? 'all';
$limit = min(100, max(1, (int) ($_GET['limit'] ?? 50)));
$offset = max(0, (int) ($_GET['offset'] ?? 0));

$allowed = ['all', 'paid', 'pending', 'failed', 'cancelled'];
if (!in_array($status, $allowed, true)) {
    $status = 'all';
}

$filter = $status === 'all' ? null : $status;
$orders = OrderRepository::listForAdmin($filter, $limit, $offset);

auth_json_response([
    'ok' => true,
    'status' => $status,
    'total' => OrderRepository::countByStatus($filter),
    'orders' => array_map('admin_format_order', $orders),
]);
