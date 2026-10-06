<?php

require_once dirname(__DIR__, 2) . '/includes/admin/helpers.php';

admin_require_api();

$period = isset($_GET['period']) ? (string) $_GET['period'] : '7d';
$from = isset($_GET['from']) ? (string) $_GET['from'] : null;
$to = isset($_GET['to']) ? (string) $_GET['to'] : null;

try {
    $stats = OrderRepository::dashboardStatsForPeriod($period, $from, $to);
} catch (InvalidArgumentException $exception) {
    auth_json_response(['ok' => false, 'error' => $exception->getMessage()], 422);
    exit;
}

$recent = OrderRepository::listForAdmin(null, 8, 0);

auth_json_response([
    'ok' => true,
    'stats' => [
        'period' => $stats['period'],
        'period_label' => $stats['period_label'],
        'period_start' => $stats['period_start'],
        'period_end' => $stats['period_end'],
        'revenue_total' => admin_format_pence((int) $stats['revenue_pence']),
        'orders_paid' => (int) $stats['orders_paid'],
        'orders_pending' => (int) $stats['orders_pending'],
        'orders_failed' => (int) $stats['orders_failed'],
        'orders_cancelled' => (int) $stats['orders_cancelled'],
        'stripe_configured' => (bool) $stats['stripe_configured'],
        'revenue_chart' => $stats['revenue_chart'],
        'chart_granularity' => $stats['chart_granularity'],
    ],
    'recent_orders' => array_map('admin_format_order', $recent),
]);
