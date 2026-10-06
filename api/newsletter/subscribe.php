<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/newsletter/NewsletterRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$body = auth_read_json_body();
$csrf = $body['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

if (!auth_verify_csrf(is_string($csrf) ? $csrf : null)) {
    auth_json_response(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 403);
}

$email = auth_sanitize_email((string) ($body['email'] ?? ''));

if (!auth_is_valid_email($email)) {
    auth_json_response(['ok' => false, 'error' => 'Type a real email address.'], 422);
}

$result = NewsletterRepository::subscribe($email);

if (!$result['is_new']) {
    auth_json_response([
        'ok' => true,
        'already_subscribed' => true,
        'message' => 'You are already on our deals list.',
    ]);
}

$send = ResendMailer::sendNewsletterSignup($email);

if (!$send['ok']) {
    $stmt = auth_database()->prepare('DELETE FROM newsletter_subscribers WHERE email = :email');
    $stmt->execute(['email' => $email]);
    auth_json_response(['ok' => false, 'error' => $send['error']], 502);
}

auth_json_response([
    'ok' => true,
    'already_subscribed' => false,
    'message' => 'You are on our deals list. Watch your inbox for offers.',
]);
