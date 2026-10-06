<?php
/**
 * Load .env from project root (simple key=value parser).
 */
function auth_load_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    $path = dirname(__DIR__, 2) . '/.env';
    if (!is_readable($path)) {
        $loaded = true;
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        $loaded = true;
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }

    $loaded = true;
}

function auth_env(string $key, ?string $default = null): ?string
{
    auth_load_env();
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

function auth_stripe_configured(): bool
{
    return (bool) auth_env('STRIPE_SECRET_KEY') && (bool) auth_env('STRIPE_PUBLISHABLE_KEY');
}

function auth_stripe_is_test_mode(): bool
{
    $secret = (string) auth_env('STRIPE_SECRET_KEY', '');

    return str_starts_with($secret, 'sk_test_');
}

function auth_twilio_whatsapp_configured(): bool
{
    return (bool) auth_env('TWILIO_ACCOUNT_SID')
        && (bool) auth_env('TWILIO_AUTH_TOKEN')
        && auth_twilio_whatsapp_from() !== null;
}

function auth_twilio_whatsapp_from(): ?string
{
    $from = trim((string) auth_env('TWILIO_WHATSAPP_FROM', ''));
    if ($from === '') {
        return null;
    }

    if (!str_starts_with($from, 'whatsapp:')) {
        $from = 'whatsapp:' . ltrim($from, '+');
        if (!str_starts_with($from, 'whatsapp:+')) {
            $from = 'whatsapp:+' . preg_replace('/\D+/', '', $from);
        }
    }

    return $from !== 'whatsapp:+' && $from !== 'whatsapp:' ? $from : null;
}

function auth_twilio_whatsapp_notify_enabled(): bool
{
    $value = strtolower(trim((string) auth_env('TWILIO_WHATSAPP_ENABLED', 'true')));

    return !in_array($value, ['0', 'false', 'no', 'off'], true);
}

function auth_twilio_whatsapp_notify_test_orders(): bool
{
    $value = strtolower(trim((string) auth_env('TWILIO_WHATSAPP_NOTIFY_TEST_ORDERS', 'false')));

    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

/**
 * @return list<string>
 */
function auth_twilio_notify_whatsapp_numbers(): array
{
    $raw = trim((string) auth_env('TWILIO_NOTIFY_WHATSAPP', ''));
    if ($raw === '') {
        require_once dirname(__DIR__) . '/data/contact-info.php';

        return [get_brand_whatsapp()['href']];
    }

    $numbers = [];
    foreach (explode(',', $raw) as $part) {
        $number = trim($part);
        if ($number !== '') {
            $numbers[] = $number;
        }
    }

    return array_values(array_unique($numbers));
}

/**
 * @return list<string>
 */
function auth_admin_emails(): array
{
    $raw = auth_env('ADMIN_EMAILS', '');
    if ($raw === null || trim($raw) === '') {
        return [];
    }

    $emails = [];
    foreach (explode(',', $raw) as $part) {
        $email = strtolower(trim($part));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $email;
        }
    }

    return array_values(array_unique($emails));
}

function auth_is_admin_email(string $email): bool
{
    $normalized = strtolower(trim($email));

    return in_array($normalized, auth_admin_emails(), true);
}

function auth_app_url(): string
{
    $configured = auth_env('APP_URL');
    if ($configured) {
        return rtrim($configured, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

function auth_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function auth_read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST;
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}

function auth_sanitize_email(string $email): string
{
    return strtolower(trim($email));
}

function auth_is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function auth_site_notify_email(): string
{
    $email = auth_sanitize_email((string) auth_env('SITE_NOTIFY_EMAIL', ''));

    if (auth_is_valid_email($email)) {
        return $email;
    }

    $legacy = auth_sanitize_email((string) auth_env('NEWSLETTER_NOTIFY_EMAIL', ''));

    if (auth_is_valid_email($legacy)) {
        return $legacy;
    }

    return 'eddiliciousfoods@gmail.com';
}

function auth_newsletter_notify_email(): ?string
{
    return auth_site_notify_email();
}

function auth_csrf_token(): string
{
    if (empty($_SESSION['auth_csrf'])) {
        $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['auth_csrf'];
}

function auth_verify_csrf(?string $token): bool
{
    if ($token === null || $token === '') {
        return false;
    }

    return isset($_SESSION['auth_csrf']) && hash_equals($_SESSION['auth_csrf'], $token);
}

function auth_safe_redirect(string $url): string
{
    if ($url === '' || str_contains($url, '://') || str_starts_with($url, '//')) {
        return 'account.php';
    }

    if (!str_ends_with($url, '.php') && !str_contains($url, '.php?')) {
        return 'account.php';
    }

    return $url;
}

function auth_admin_safe_redirect(string $url): string
{
    if ($url === '' || str_contains($url, '://') || str_starts_with($url, '//')) {
        return 'index.php';
    }

    if (preg_match('#/admin/([^?]*)#', $url, $matches)) {
        $path = trim((string) $matches[1], '/');
        if ($path === '' || !str_contains($path, '.php')) {
            return 'index.php';
        }

        return $path;
    }

    if (str_contains($url, '.php')) {
        return ltrim($url, '/');
    }

    return 'index.php';
}

function auth_request_base_path(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === '' || $script === '/') {
        return '/';
    }

    $dir = dirname($script);
    if ($dir === '/' || $dir === '.') {
        return '/';
    }

    return rtrim($dir, '/') . '/';
}

function auth_session_cookie_path(): string
{
    $configured = auth_env('APP_URL');
    if ($configured) {
        $parts = parse_url($configured);
        $configHost = strtolower((string) ($parts['host'] ?? ''));
        $requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $requestHost = strtok($requestHost, ':') ?: $requestHost;

        if ($configHost !== '' && $requestHost !== '' && $configHost === $requestHost) {
            $path = $parts['path'] ?? '';
            if (is_string($path) && $path !== '' && $path !== '/') {
                return rtrim($path, '/') . '/';
            }

            return '/';
        }
    }

    return auth_request_base_path();
}
