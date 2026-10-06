<?php
/**
 * Edilicious Foods — Terms and conditions
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/legal-content.php';

$legal = get_legal_page('terms');

$page_title = 'Terms and conditions — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/legal.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/partials/legal-page.php';
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
