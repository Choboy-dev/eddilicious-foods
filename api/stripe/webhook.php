<?php
require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/payments/StripeService.php';

$payload = file_get_contents('php://input');
if ($payload === false) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
$result = StripeService::handleWebhook($payload, is_string($signature) ? $signature : null);

http_response_code((int) ($result['status'] ?? ($result['ok'] ? 200 : 400)));
echo json_encode(['ok' => (bool) ($result['ok'] ?? false)]);
