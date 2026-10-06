<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/reviews/ProductReviewRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$user = AuthSession::user();
if ($user === null) {
    auth_json_response(['ok' => false, 'error' => 'Sign in to leave a review.'], 401);
}

$body = auth_read_json_body();
$csrf = $body['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

if (!auth_verify_csrf(is_string($csrf) ? $csrf : null)) {
    auth_json_response(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 403);
}

$productId = trim((string) ($body['product_id'] ?? ''));
$rating = (int) ($body['rating'] ?? 0);
$text = trim((string) ($body['text'] ?? ''));

$result = ProductReviewRepository::submit($user, $productId, $rating, $text);

if (!$result['ok']) {
    auth_json_response(['ok' => false, 'error' => $result['error'] ?? 'Could not save your review.'], 422);
}

auth_json_response([
    'ok' => true,
    'review' => $result['review'] ?? null,
    'summary' => $result['summary'] ?? null,
]);
