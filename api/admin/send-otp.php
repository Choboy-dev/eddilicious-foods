<?php
require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';

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
    auth_json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

if (!auth_is_admin_email($email)) {
    auth_json_response([
        'ok' => false,
        'error' => 'This email is not set up for admin access.',
    ], 403);
}

if (!OtpRepository::canSend($email)) {
    auth_json_response([
        'ok' => false,
        'error' => 'Too many codes sent. Wait a few minutes and try again.',
    ], 429);
}

$code = OtpRepository::generateCode();
OtpRepository::store($email, $code, 'signin');

$send = ResendMailer::sendOtp($email, $code, 'signin');
if (!$send['ok']) {
    auth_json_response(['ok' => false, 'error' => $send['error']], 502);
}

auth_json_response([
    'ok' => true,
    'email' => $email,
    'expires_in' => OtpRepository::ttlMinutes(),
]);
