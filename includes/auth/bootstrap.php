<?php
/**
 * Auth session bootstrap.
 */
require_once __DIR__ . '/helpers.php';

auth_load_env();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => auth_session_cookie_path(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/OtpRepository.php';
require_once __DIR__ . '/ResendMailer.php';
require_once __DIR__ . '/GoogleVerifier.php';
require_once __DIR__ . '/GoogleOAuth.php';
require_once __DIR__ . '/AuthSession.php';

auth_database_migrate();

require_once __DIR__ . '/../site-license.php';
site_license_enforce();

require_once __DIR__ . '/../site-gate.php';
site_gate_enforce();
