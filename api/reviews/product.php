<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/reviews/ProductReviewRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$productId = trim((string) ($_GET['product_id'] ?? ''));

if ($productId === '') {
    auth_json_response(['ok' => false, 'error' => 'Product id is required.'], 422);
}

$summary = ProductReviewRepository::summaryForProduct($productId);
$reviews = ProductReviewRepository::listForProduct($productId);

$viewer = [
    'logged_in' => AuthSession::isLoggedIn(),
    'can_review' => false,
    'has_reviewed' => false,
    'reason' => AuthSession::isLoggedIn() ? 'not_purchased' : 'sign_in',
    'user_review' => null,
];

$user = AuthSession::user();
if ($user) {
    $existing = ProductReviewRepository::findForUserAndProduct((int) $user['id'], $productId);
    if ($existing !== null) {
        $viewer['has_reviewed'] = true;
        $viewer['reason'] = 'already_reviewed';
        $viewer['user_review'] = $existing;
    } else {
        $eligibility = ProductReviewRepository::eligibilityForUser($user, $productId);
        $viewer['can_review'] = $eligibility['can_review'];
        $viewer['reason'] = $eligibility['reason'];
    }
}

auth_json_response([
    'ok' => true,
    'summary' => $summary,
    'reviews' => $reviews,
    'viewer' => $viewer,
]);
