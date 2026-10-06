<?php
require_once __DIR__ . '/includes/config.php';

$page_title = 'Eddilicious Foods — Home';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/hero.css',
    'assets/css/categories.css',
    'assets/css/promo.css',
    'assets/css/catering-cta.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/products.js',
    'assets/js/promo.js',
    'assets/js/catering-cta.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
    <?php include __DIR__ . '/includes/hero.php'; ?>
    <?php include __DIR__ . '/includes/categories.php'; ?>
    <?php include __DIR__ . '/includes/promo-banner.php'; ?>
    <?php include __DIR__ . '/includes/rice-dishes.php'; ?>
    <?php include __DIR__ . '/includes/catering-cta.php'; ?>
    <?php include __DIR__ . '/includes/peppered-proteins.php'; ?>
    <?php include __DIR__ . '/includes/platters.php'; ?>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
