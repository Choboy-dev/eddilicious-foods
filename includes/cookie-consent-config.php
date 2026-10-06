<?php
/**
 * Front-end cookie consent configuration (public site only).
 */

if (!function_exists('auth_env')) {
    require_once __DIR__ . '/auth/bootstrap.php';
}

if (!function_exists('site_page_url')) {
    require_once __DIR__ . '/config.php';
}

require_once __DIR__ . '/analytics/GoogleAnalyticsClient.php';

/** Bump when the cookie policy or categories change so visitors can choose again. */
$cookie_consent_version = '2026-06-17';

$cookie_consent_ga4_id = GoogleAnalyticsClient::measurementId();
$cookie_consent_policy_url = site_page_url('cookies.php');
$cookie_consent_save_url = site_page_url('api/cookie-consent/save.php');
