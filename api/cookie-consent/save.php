<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/analytics/CookieConsentRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
    exit;
}

$body = auth_read_json_body();
$result = CookieConsentRepository::record($body);

if (!$result['ok']) {
    auth_json_response(['ok' => false, 'error' => $result['error'] ?? 'Could not save consent.'], 422);
    exit;
}

$consent = [
    'necessary' => true,
    'functional' => !empty($body['functional']),
    'analytics' => !empty($body['analytics']),
    'marketing' => !empty($body['marketing']),
    'savedAt' => time(),
    'version' => substr((string) ($body['consent_version'] ?? ''), 0, 32),
];

$cookieValue = base64_encode(json_encode($consent, JSON_UNESCAPED_SLASHES));
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

setcookie(
    'edd_cookie_consent',
    $cookieValue,
    [
        'expires' => time() + (365 * 24 * 60 * 60),
        'path' => auth_session_cookie_path(),
        'secure' => $secure,
        'httponly' => false,
        'samesite' => 'Lax',
    ]
);

if (!empty($body['marketing'])) {
    setcookie(
        'edd_marketing_opt_in',
        '1',
        [
            'expires' => time() + (365 * 24 * 60 * 60),
            'path' => auth_session_cookie_path(),
            'secure' => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );
} else {
    setcookie(
        'edd_marketing_opt_in',
        '',
        [
            'expires' => time() - 3600,
            'path' => auth_session_cookie_path(),
            'secure' => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );
}

auth_json_response(['ok' => true, 'consent' => $consent]);
