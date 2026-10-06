<?php
/**
 * Expose auth config to the browser (CSRF token only — Google OAuth runs server-side).
 */
require_once __DIR__ . '/auth/bootstrap.php';

$csrf = auth_csrf_token();
$user = AuthSession::user();
$userPayload = null;

if ($user) {
    $userPayload = [
        'email' => (string) ($user['email'] ?? ''),
        'name' => (string) ($user['name'] ?? ''),
    ];
}
?>
<script>
window.EDDILICIOUS_AUTH = {
    csrf: <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
    loggedIn: <?= AuthSession::isLoggedIn() ? 'true' : 'false' ?>,
    user: <?= json_encode($userPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
};
</script>
