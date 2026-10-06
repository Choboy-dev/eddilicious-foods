<?php
/**
 * Edilicious Foods — Site configuration
 */

define('SITE_LOCALE', 'en_GB');
define('CURRENCY_CODE', 'GBP');
/** Pound sign — use a Unicode escape so uploads never corrupt this constant. */
define('CURRENCY_SYMBOL', "\u{00A3}");

/** Site logo (path from site root) */
define('SITE_LOGO_PATH', 'assets/images/images/eddilicious-logo.png');

function site_logo_path(): string
{
    return SITE_LOGO_PATH;
}

/**
 * @param string $asset_prefix Use '../' from admin pages, '' from site root.
 */
function site_logo_src(string $asset_prefix = ''): string
{
    return $asset_prefix . site_logo_path();
}

/** Start auth session before any HTML output (keeps login across pages). */
require_once __DIR__ . '/auth/bootstrap.php';

require_once __DIR__ . '/data/order-lead-time.php';

/**
 * GBP symbol for display (never use a bare number here).
 */
function currency_symbol(): string
{
    $symbol = CURRENCY_SYMBOL;

    if (!is_string($symbol) || $symbol === '' || is_numeric($symbol)) {
        return "\u{00A3}";
    }

    return $symbol;
}

/**
 * Normalise a catalog price to pounds (float).
 */
function catalog_price_pounds(mixed $value): float
{
    if (is_int($value) || is_float($value)) {
        return round((float) $value, 2);
    }

    if (is_string($value)) {
        $clean = preg_replace('/[^0-9.]/', '', $value) ?? '';

        return round((float) $clean, 2);
    }

    return 0.0;
}

/**
 * Format an amount in GBP for display.
 */
function format_price(float $amount): string
{
    $normalized = round(max(0, $amount), 2);

    return currency_symbol() . number_format($normalized, 2, '.', ',');
}

/**
 * Public URL for a static asset, with a filemtime cache-buster when the file exists.
 */
function site_asset(string $relativePath): string
{
    $relativePath = ltrim($relativePath, '/');
    $rootFile = dirname(__DIR__) . '/' . $relativePath;

    return site_page_url($relativePath, is_file($rootFile) ? filemtime($rootFile) : null);
}

/**
 * Public URL for a site page or API route from the site root.
 */
function site_page_url(string $relativePath, ?int $cacheBuster = null): string
{
    $relativePath = ltrim($relativePath, '/');
    $base = auth_session_cookie_path();
    $url = ($base === '/' ? '' : rtrim($base, '/')) . '/' . $relativePath;

    if ($cacheBuster !== null) {
        $url .= '?v=' . $cacheBuster;
    }

    return $url;
}
