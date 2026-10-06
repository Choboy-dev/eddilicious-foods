<?php

/**
 * Under-construction gate — public visitors see coming soon unless they use the preview key.
 *
 * Preview URL (any page): ?preview=YOUR_SITE_PREVIEW_KEY
 * Sets a cookie so they can browse the full site without adding the key every time.
 */
function site_gate_is_under_construction(): bool
{
    $value = strtolower(trim((string) auth_env('SITE_UNDER_CONSTRUCTION', 'false')));

    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function site_gate_preview_key(): string
{
    return trim((string) auth_env('SITE_PREVIEW_KEY', ''));
}

function site_gate_request_path(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }

    $base = auth_session_cookie_path();
    if ($base !== '/' && str_starts_with($path, rtrim($base, '/'))) {
        $path = substr($path, strlen(rtrim($base, '/')));
        if ($path === '') {
            $path = '/';
        }
    }

    return $path;
}

function site_gate_is_excluded_path(string $path): bool
{
    $normalized = '/' . ltrim(strtolower($path), '/');

    $excluded = [
        '/api/stripe/webhook.php',
        '/api/newsletter/subscribe.php',
    ];

    if (str_starts_with($normalized, '/api/')) {
        return true;
    }

    foreach ($excluded as $prefix) {
        if ($normalized === $prefix || str_starts_with($normalized, $prefix)) {
            return true;
        }
    }

    return false;
}

function site_gate_has_preview_access(): bool
{
    $key = site_gate_preview_key();
    if ($key === '') {
        return false;
    }

    $queryKey = isset($_GET['preview']) ? (string) $_GET['preview'] : '';
    if ($queryKey !== '' && hash_equals($key, $queryKey)) {
        site_gate_set_preview_cookie();
        return true;
    }

    $cookie = $_COOKIE['edd_site_preview'] ?? '';
    if (is_string($cookie) && $cookie !== '' && hash_equals(hash('sha256', $key), $cookie)) {
        return true;
    }

    return false;
}

function site_gate_set_preview_cookie(): void
{
    $key = site_gate_preview_key();
    if ($key === '') {
        return;
    }

    setcookie(
        'edd_site_preview',
        hash('sha256', $key),
        [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => auth_session_cookie_path(),
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function site_gate_enforce(): void
{
    if (!site_gate_is_under_construction()) {
        return;
    }

    if (site_gate_has_preview_access()) {
        return;
    }

    $path = site_gate_request_path();
    if (site_gate_is_excluded_path($path)) {
        return;
    }

    site_gate_render_coming_soon();
    exit;
}

function site_gate_render_coming_soon(): void
{
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/data/contact-info.php';

    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Retry-After: 3600');

    $contact = get_contact_info();
    $social = get_social_links();
    $whatsapp = get_brand_whatsapp_url();
    $asset_prefix = auth_session_cookie_path() === '/' ? '' : rtrim(auth_session_cookie_path(), '/') . '/';
    $csrf = auth_csrf_token();

    include __DIR__ . '/partials/coming-soon-page.php';
}
