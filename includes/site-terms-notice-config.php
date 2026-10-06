<?php
/**
 * Site-wide terms and allergy policy notice (public site only).
 */

if (!function_exists('site_page_url')) {
    require_once __DIR__ . '/config.php';
}

require_once __DIR__ . '/data/legal-content.php';

/** Content version. Bump when terms or allergy policy change to show the notice again on the next visit. */
$site_terms_notice_version = '2026-06-17';

$site_terms_notice_terms_url = site_page_url('terms.php');
$site_terms_notice_allergy_url = site_page_url(get_food_allergy_policy_pdf_url());
