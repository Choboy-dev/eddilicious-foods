<?php
/**
 * Start Google OAuth — redirects the browser to Google's consent screen.
 */
require_once dirname(__DIR__) . '/includes/auth/bootstrap.php';

$redirect = auth_safe_redirect($_GET['redirect'] ?? 'account.php');
AuthSession::requireGuest($redirect);

$purpose = (string) ($_GET['purpose'] ?? 'signin');
if (!in_array($purpose, ['signin', 'signup'], true)) {
    $purpose = 'signin';
}

$returnPage = $purpose === 'signup' ? 'signup.php' : 'signin.php';
$returnQuery = $redirect !== 'account.php' ? '?redirect=' . urlencode($redirect) : '';

if (!GoogleOAuth::isConfigured()) {
    header('Location: ' . $returnPage . $returnQuery . ($returnQuery ? '&' : '?') . 'auth_error=google_not_configured');
    exit;
}

header('Location: ' . GoogleOAuth::buildAuthorizationUrl($purpose, $redirect));
exit;
