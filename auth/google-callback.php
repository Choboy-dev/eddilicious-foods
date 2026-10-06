<?php
/**
 * Google OAuth callback — exchanges the code, verifies the user, and signs them in.
 */
require_once dirname(__DIR__) . '/includes/auth/bootstrap.php';

$result = GoogleOAuth::handleCallback(
    isset($_GET['code']) ? (string) $_GET['code'] : null,
    isset($_GET['state']) ? (string) $_GET['state'] : null,
    isset($_GET['error']) ? (string) $_GET['error'] : null
);

if ($result['ok']) {
    header('Location: ../' . ltrim((string) ($result['redirect'] ?? 'account.php'), '/'));
    exit;
}

$purpose = ($result['purpose'] ?? 'signin') === 'signup' ? 'signup' : 'signin';
$page = $purpose === 'signup' ? '../signup.php' : '../signin.php';
$redirect = auth_safe_redirect((string) ($result['redirect'] ?? 'account.php'));
$query = [];

if ($redirect !== 'account.php') {
    $query['redirect'] = $redirect;
}

$errorCode = (string) ($result['error_code'] ?? 'google_failed');
$query['auth_error'] = $errorCode;

header('Location: ' . $page . '?' . http_build_query($query));
exit;
