<?php
/**
 * Shared page scripts — set $page_scripts before including.
 */
$page_scripts = $page_scripts ?? [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/search.js',
    'assets/js/navbar.js',
    'assets/js/custom-select.js',
    'assets/js/footer.js',
];

$search_script = 'assets/js/search.js';
if (!in_array($search_script, $page_scripts, true)) {
    $after = array_search('assets/js/coupon-store.js', $page_scripts, true);
    if ($after !== false) {
        array_splice($page_scripts, $after + 1, 0, [$search_script]);
    } else {
        $page_scripts[] = $search_script;
    }
}

$whatsapp_script = 'assets/js/whatsapp-widget.js';
if (!in_array($whatsapp_script, $page_scripts, true)) {
    $page_scripts[] = $whatsapp_script;
}

$cookie_consent_script = 'assets/js/cookie-consent.js';
if (!in_array($cookie_consent_script, $page_scripts, true)) {
    array_unshift($page_scripts, $cookie_consent_script);
}

$site_terms_notice_script = 'assets/js/site-terms-notice.js';
if (!in_array($site_terms_notice_script, $page_scripts, true)) {
    $cookie_index = array_search($cookie_consent_script, $page_scripts, true);
    if ($cookie_index !== false) {
        array_splice($page_scripts, $cookie_index + 1, 0, [$site_terms_notice_script]);
    } else {
        array_unshift($page_scripts, $site_terms_notice_script);
    }
}

$analytics_script = 'assets/js/analytics.js';
if (!in_array($analytics_script, $page_scripts, true)) {
    $page_scripts[] = $analytics_script;
}
?>
<?php include __DIR__ . '/cookie-consent-banner.php'; ?>
<?php include __DIR__ . '/site-terms-notice.php'; ?>
<?php foreach ($page_scripts as $script): ?>
    <script src="<?= htmlspecialchars(site_asset($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
