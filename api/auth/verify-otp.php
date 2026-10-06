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
$code = (string) ($body['code'] ?? '');
$purpose = (string) ($body['purpose'] ?? 'signin');
$redirect = auth_safe_redirect((string) ($body['redirect'] ?? 'account.php'));

if (!auth_is_valid_email($email)) {
    auth_json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

if (!in_array($purpose, ['signin', 'signup'], true)) {
    auth_json_response(['ok' => false, 'error' => 'Invalid request.'], 422);
}

$verify = OtpRepository::verify($email, $code, $purpose);
if (!$verify['ok']) {
    auth_json_response(['ok' => false, 'error' => $verify['error']], 422);
}

$user = UserRepository::findByEmail($email);

if ($purpose === 'signup') {
    if (!$user) {
        $user = UserRepository::createFromEmail($email);
    }
} elseif (!$user) {
    auth_json_response(['ok' => false, 'error' => 'We could not find an account with this email.'], 404);
}

UserRepository::touchLogin((int) $user['id']);
AuthSession::login($user);

auth_json_response([
    'ok' => true,
    'redirect' => $redirect,
    'user' => [
        'email' => $user['email'],
        'name' => $user['name'],
    ],
]);
