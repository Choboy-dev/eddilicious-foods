<?php
/**
 * Shared page <head> — set $page_title and optional $page_styles before including.
 */
$page_title = $page_title ?? 'Eddilicious Foods';
$page_styles = $page_styles ?? ['assets/css/navbar.css', 'assets/css/footer.css'];

if (!function_exists('format_price')) {
    require_once __DIR__ . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <?php include __DIR__ . '/fonts.php'; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/variables.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/brand-logo.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/custom-select.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/search.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/whatsapp-widget.css')) ?>">
    <?php foreach ($page_styles as $stylesheet): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(site_asset($stylesheet)) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/desktop-typography.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/mobile-typography.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/cookie-consent.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(site_asset('assets/css/site-terms-notice.css')) ?>">
    <?php include __DIR__ . '/coupon-config.php'; ?>
    <?php include __DIR__ . '/auth-config.php'; ?>
    <?php include __DIR__ . '/product-config.php'; ?>
    <?php include __DIR__ . '/cookie-consent-config.php'; ?>
    <?php include __DIR__ . '/site-terms-notice-config.php'; ?>
    <script>
        window.EddiliciousCookieConsentConfig = <?= json_encode([
            'ga4Id' => $cookie_consent_ga4_id,
            'saveUrl' => $cookie_consent_save_url,
            'policyUrl' => $cookie_consent_policy_url,
            'version' => $cookie_consent_version,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script>
        window.EddiliciousSiteTermsNoticeConfig = <?= json_encode([
            'version' => $site_terms_notice_version,
            'termsUrl' => $site_terms_notice_terms_url,
            'allergyUrl' => $site_terms_notice_allergy_url,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body<?= !empty($body_class) ? ' class="' . htmlspecialchars($body_class) . '"' : '' ?>>
