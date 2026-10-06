<?php
/**
 * Edilicious Foods — Wishlist
 */
require_once __DIR__ . '/includes/config.php';

$page_title = 'My Wishlist — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/categories.css',
    'assets/css/wishlist.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/navbar.js',
    'assets/js/products.js',
    'assets/js/wishlist-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="wishlist-page">
    <div class="wishlist-page__container">
        <header class="wishlist-header">
            <div class="wishlist-header__title-row">
                <svg class="wishlist-header__icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                </svg>
                <h1 class="wishlist-header__title">My wishlist</h1>
            </div>
            <p class="wishlist-header__lead">Food you saved. Add it to your basket when you are ready.</p>
        </header>

        <div class="wishlist-layout" data-wishlist-layout>
            <section class="wishlist-main" aria-labelledby="wishlist-heading">
                <h2 id="wishlist-heading" class="visually-hidden">Saved items</h2>

                <div class="wishlist-items product-section__grid" data-wishlist-items role="list"></div>

                <div class="wishlist-empty" data-wishlist-empty hidden>
                    <div class="wishlist-empty__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                        </svg>
                    </div>
                    <p>Your wishlist is empty.</p>
                    <a href="menu.php?category=soups" class="wishlist-empty__link">Browse our menu</a>
                </div>
            </section>

            <aside class="wishlist-summary" aria-label="Wishlist summary">
                <div class="wishlist-summary__panel">
                    <p class="wishlist-summary__count">
                        <strong data-wishlist-count>0</strong>
                        <span data-wishlist-count-label>items saved</span>
                    </p>

                    <p class="wishlist-summary__hint">Tap the heart on any dish to save it here.</p>

                    <a href="menu.php?category=soups" class="wishlist-summary__browse">Keep browsing</a>

                    <a href="cart.php" class="wishlist-summary__cart">
                        <span class="wishlist-summary__cart-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                            </svg>
                        </span>
                        Go to basket
                    </a>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
