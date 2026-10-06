<?php

require_once dirname(__DIR__, 2) . '/includes/admin/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/analytics/AnalyticsRepository.php';

admin_require_api();

$period = isset($_GET['period']) ? (string) $_GET['period'] : '7d';
$from = isset($_GET['from']) ? (string) $_GET['from'] : null;
$to = isset($_GET['to']) ? (string) $_GET['to'] : null;

try {
    $stats = AnalyticsRepository::dashboardStatsForPeriod($period, $from, $to);
} catch (InvalidArgumentException $exception) {
    auth_json_response(['ok' => false, 'error' => $exception->getMessage()], 422);
    exit;
}

auth_json_response([
    'ok' => true,
    'stats' => $stats,
]);
