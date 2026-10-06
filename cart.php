<?php
/**
 * Edilicious Foods — Shopping cart
 */
require_once __DIR__ . '/includes/config.php';

$page_title = 'My Basket — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/cart.css',
    'assets/css/order-lead-notice.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/cart-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/auth-config.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="cart-page">
    <div class="cart-page__container">
        <div class="cart-page__layout">
            <section class="cart-basket" aria-labelledby="cart-heading">
                <header class="cart-basket__header">
                    <svg class="cart-basket__icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <h1 id="cart-heading" class="cart-basket__title">My basket</h1>
                </header>

                <div class="cart-basket__body">
                    <ul class="cart-items" data-cart-items role="list"></ul>

                    <div class="cart-basket__notice" data-cart-lead-notice hidden>
                        <?php include __DIR__ . '/includes/partials/order-lead-notice.php'; ?>
                    </div>

                    <div class="cart-basket__empty" data-cart-empty hidden>
                        <p>Your basket is empty.</p>
                        <a href="menu.php?category=soups" class="cart-basket__shop-link">Go to our menu</a>
                    </div>
                </div>
            </section>

            <aside class="cart-summary" aria-label="Order summary">
                <div class="cart-summary__panel">
                    <dl class="cart-summary__rows">
                        <div class="cart-summary__row">
                            <dt>Sub total</dt>
                            <dd data-cart-subtotal>£0.00</dd>
                        </div>
                        <div class="cart-summary__row">
                            <dt>Discounts</dt>
                            <dd data-cart-discount>-£0.00</dd>
                        </div>
                        <div class="cart-summary__row">
                            <dt>Delivery fee</dt>
                            <dd data-cart-delivery>Free</dd>
                        </div>
                    </dl>

                    <div class="cart-summary__total">
                        <span>Total to pay</span>
                        <strong data-cart-total>£0.00</strong>
                    </div>

                    <div class="cart-summary__field">
                        <label class="cart-summary__label" for="cart-coupon">Coupon code</label>
                        <div class="cart-summary__input-row">
                            <input
                                type="text"
                                id="cart-coupon"
                                class="cart-summary__input"
                                placeholder="Type your code here"
                                data-cart-coupon-input
                                autocomplete="off"
                            >
                            <button type="button" class="cart-summary__apply" data-cart-apply-coupon aria-label="Apply coupon code">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                        <p class="cart-summary__hint" data-cart-coupon-message hidden></p>
                        <button type="button" class="cart-summary__remove-coupon" data-cart-remove-coupon hidden>
                            Remove coupon
                        </button>
                    </div>

                    <div class="cart-fulfillment" role="radiogroup" aria-label="How you get your food">
                        <label class="cart-fulfillment__option">
                            <input type="radio" name="fulfillment" value="delivery" data-cart-fulfillment checked>
                            <span class="cart-fulfillment__card">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                                </svg>
                                <span class="cart-fulfillment__label">Delivery</span>
                                <span class="cart-fulfillment__meta"><?= htmlspecialchars(get_order_lead_delivery_meta()) ?></span>
                            </span>
                        </label>
                        <label class="cart-fulfillment__option">
                            <input type="radio" name="fulfillment" value="collection" data-cart-fulfillment>
                            <span class="cart-fulfillment__card">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <span class="cart-fulfillment__label">Collection</span>
                                <span class="cart-fulfillment__meta"><?= htmlspecialchars(get_order_lead_collection_meta()) ?></span>
                            </span>
                        </label>
                    </div>

                    <a href="checkout.php" class="cart-checkout" data-cart-checkout>
                        <span class="cart-checkout__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </span>
                        Checkout
                    </a>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
