<?php
/**
 * Edilicious Foods — Sign up
 */
require_once __DIR__ . '/includes/config.php';

$auth_mode = 'signup';
$auth_title = 'Create your account';
$auth_lead = '';
$auth_switch_label = 'Already have an account?';
$auth_switch_href = 'signin.php';
$auth_switch_link_text = 'Sign in';

$page_title = 'Sign up — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/auth.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/auth-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/auth-config.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/partials/auth-shell.php';
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
