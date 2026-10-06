<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/analytics/AnalyticsRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
    exit;
}

$body = auth_read_json_body();
$result = AnalyticsRepository::recordPageview($body);

if (!$result['ok']) {
    auth_json_response(['ok' => false, 'error' => $result['error'] ?? 'Could not save page view.'], 422);
    exit;
}

auth_json_response(['ok' => true]);
