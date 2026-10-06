<?php
/**
 * Edilicious Foods — Checkout
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/legal-content.php';
require_once __DIR__ . '/includes/data/contact-info.php';

AuthSession::requireUser('signup.php');

$allergy_policy_pdf = get_food_allergy_policy_pdf_url();
$allergy_policy_title = get_food_allergy_policy_title();
$checkout_contact = get_contact_info();
$checkout_whatsapp = get_brand_whatsapp();
$checkout_whatsapp_digits = preg_replace('/\D+/', '', $checkout_whatsapp['href']) ?? '';
$checkout_allergy_whatsapp_url = 'https://wa.me/' . $checkout_whatsapp_digits . '?text=' . rawurlencode('Hello Eddilicious Foods! I have a question about allergies before I order.');

$page_title = 'Checkout — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/checkout.css',
    'assets/css/order-lead-notice.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/checkout-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/auth-config.php';
include __DIR__ . '/includes/stripe-config.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="checkout-page">
    <div class="checkout-page__container">
        <header class="checkout-header">
            <a href="cart.php" class="checkout-header__back">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Back to basket
            </a>
            <h1 class="checkout-header__title">Checkout</h1>
            <p class="checkout-header__lead">Fill in your details and pay securely with Stripe.</p>

            <?php include __DIR__ . '/includes/partials/order-lead-notice.php'; ?>

            <?php if (isset($_GET['cancelled'])): ?>
                <p class="checkout-header__notice" role="status">Payment was cancelled. Your basket is still here when you are ready.</p>
            <?php endif; ?>

            <ol class="checkout-steps" aria-label="Checkout steps">
                <li class="checkout-steps__item is-active" data-checkout-step="details">
                    <span class="checkout-steps__number">1</span>
                    <span class="checkout-steps__label">Your details</span>
                </li>
                <li class="checkout-steps__item" data-checkout-step="delivery">
                    <span class="checkout-steps__number">2</span>
                    <span class="checkout-steps__label">Delivery</span>
                </li>
                <li class="checkout-steps__item" data-checkout-step="pay">
                    <span class="checkout-steps__number">3</span>
                    <span class="checkout-steps__label">Pay</span>
                </li>
            </ol>
        </header>

        <div class="checkout-layout" data-checkout-layout>
            <div class="checkout-main">
                <div class="checkout-empty" data-checkout-empty hidden>
                    <p>Your basket is empty.</p>
                    <a href="menu.php?category=soups" class="checkout-empty__link">Go to our menu</a>
                </div>

                <form id="checkout-form" class="checkout-form" data-checkout-form novalidate>
                    <!-- Step 1: Your details -->
                    <div class="checkout-step-panel is-active" data-checkout-step-panel="1">
                        <section class="checkout-card" aria-labelledby="checkout-details-heading">
                            <h2 id="checkout-details-heading" class="checkout-card__title">Your details</h2>
                            <p class="checkout-card__hint">Tell us who you are so we can reach you about your order.</p>
                            <div class="checkout-grid checkout-grid--2">
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-first-name">First name</label>
                                    <input type="text" id="checkout-first-name" name="first_name" class="checkout-input" autocomplete="given-name" required>
                                </div>
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-last-name">Last name</label>
                                    <input type="text" id="checkout-last-name" name="last_name" class="checkout-input" autocomplete="family-name" required>
                                </div>
                            </div>
                            <div class="checkout-field">
                                <label class="checkout-label" for="checkout-email">Email</label>
                                <input type="email" id="checkout-email" name="email" class="checkout-input" autocomplete="email" required>
                            </div>
                            <div class="checkout-field">
                                <label class="checkout-label" for="checkout-phone">Phone</label>
                                <input type="tel" id="checkout-phone" name="phone" class="checkout-input" autocomplete="tel" required>
                            </div>
                        </section>

                        <div class="checkout-step-nav">
                            <button type="button" class="checkout-step-nav__next" data-checkout-next>
                                Continue
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Delivery -->
                    <div class="checkout-step-panel" data-checkout-step-panel="2" hidden>
                        <section class="checkout-card" aria-labelledby="checkout-fulfillment-heading">
                            <h2 id="checkout-fulfillment-heading" class="checkout-card__title">How you get your food</h2>
                            <div class="checkout-fulfillment" role="radiogroup" aria-label="How you get your food">
                                <label class="checkout-fulfillment__option">
                                    <input type="radio" name="fulfillment" value="delivery" data-checkout-fulfillment checked>
                                    <span class="checkout-fulfillment__card">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                                        </svg>
                                        <span class="checkout-fulfillment__label">Delivery</span>
                                        <span class="checkout-fulfillment__meta"><?= htmlspecialchars(get_order_lead_delivery_meta()) ?></span>
                                    </span>
                                </label>
                                <label class="checkout-fulfillment__option">
                                    <input type="radio" name="fulfillment" value="collection" data-checkout-fulfillment>
                                    <span class="checkout-fulfillment__card">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                                        </svg>
                                        <span class="checkout-fulfillment__label">Collection</span>
                                        <span class="checkout-fulfillment__meta"><?= htmlspecialchars(get_order_lead_collection_meta()) ?></span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <section class="checkout-card" data-checkout-address aria-labelledby="checkout-address-heading">
                            <h2 id="checkout-address-heading" class="checkout-card__title">Where we deliver to</h2>
                            <div class="checkout-field">
                                <label class="checkout-label" for="checkout-address-line1">Address line 1</label>
                                <input type="text" id="checkout-address-line1" name="address_line1" class="checkout-input" autocomplete="address-line1" data-checkout-address-field>
                            </div>
                            <div class="checkout-field">
                                <label class="checkout-label" for="checkout-address-line2">Address line 2 <span class="checkout-label__optional">(optional)</span></label>
                                <input type="text" id="checkout-address-line2" name="address_line2" class="checkout-input" autocomplete="address-line2">
                            </div>
                            <div class="checkout-grid checkout-grid--2">
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-city">Town or city</label>
                                    <input type="text" id="checkout-city" name="city" class="checkout-input" autocomplete="address-level2" data-checkout-address-field>
                                </div>
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-postcode">Postcode</label>
                                    <input type="text" id="checkout-postcode" name="postcode" class="checkout-input" autocomplete="postal-code" data-checkout-address-field>
                                </div>
                            </div>
                        </section>

                        <div class="checkout-step-nav">
                            <button type="button" class="checkout-step-nav__back" data-checkout-back>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>
                                Back
                            </button>
                            <button type="button" class="checkout-step-nav__next" data-checkout-next>
                                Continue
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Pay -->
                    <div class="checkout-step-panel" data-checkout-step-panel="3" hidden>
                        <section class="checkout-card" aria-labelledby="checkout-notes-heading">
                            <h2 id="checkout-notes-heading" class="checkout-card__title">Notes for us</h2>
                            <div class="checkout-field">
                                <label class="checkout-label" for="checkout-notes">Anything we should know?</label>
                                <textarea id="checkout-notes" name="notes" class="checkout-textarea" rows="3" placeholder="Allergies, gate code, best time to call…"></textarea>
                            </div>
                        </section>

                        <section class="checkout-card checkout-card--agreement" aria-labelledby="checkout-agreement-heading">
                            <h2 id="checkout-agreement-heading" class="checkout-card__title">Before you pay</h2>
                            <p class="checkout-card__hint">You must read and accept our allergy policy before we can take your order.</p>

                            <div class="checkout-agreement" data-checkout-agreement>
                                <label class="checkout-agreement__label">
                                    <input
                                        type="checkbox"
                                        class="checkout-agreement__checkbox"
                                        name="allergy_agreement"
                                        value="1"
                                        data-checkout-agreement-checkbox
                                    >
                                    <span class="checkout-agreement__box" aria-hidden="true"></span>
                                    <span class="checkout-agreement__text">
                                        By buying from us, you agree to the
                                        <a href="terms.php#food-handling-allergies" target="_blank" rel="noopener noreferrer">terms and conditions</a>
                                        and our
                                        <button type="button" class="checkout-agreement__link" data-checkout-agreement-open>
                                            food allergy policy
                                        </button>.
                                        You have read this policy and understand the risks around allergens.
                                    </span>
                                </label>
                                <p class="checkout-agreement__error" data-checkout-agreement-error hidden role="alert"></p>
                            </div>
                        </section>

                        <section class="checkout-card" aria-labelledby="checkout-payment-heading">
                            <h2 id="checkout-payment-heading" class="checkout-card__title">Pay securely</h2>
                            <p class="checkout-card__hint">Secure payments powered by Stripe</p>

                            <div class="checkout-stripe">
                                <img
                                    src="assets/images/images/stripe-image.webp"
                                    alt="Secure payments powered by Stripe"
                                    class="checkout-stripe__image"
                                    width="640"
                                    height="160"
                                    loading="lazy"
                                >
                            </div>

                            <p class="checkout-stripe__unconfigured" data-checkout-stripe-warning hidden>
                                Card payments are not set up yet. Add your Stripe keys to the <code>.env</code> file.
                            </p>
                        </section>

                        <div class="checkout-step-nav">
                            <button type="button" class="checkout-step-nav__back" data-checkout-back>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>
                                Back
                            </button>
                            <button type="submit" class="checkout-step-nav__submit" data-checkout-submit disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>
                                </svg>
                                Pay with Stripe
                            </button>
                        </div>
                    </div>

                    <p class="checkout-form__error" data-checkout-form-error hidden></p>
                </form>

                <section class="checkout-success" data-checkout-success hidden aria-live="polite">
                    <div class="checkout-success__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                    </div>
                    <h2 class="checkout-success__title">We got your order</h2>
                    <p class="checkout-success__text" data-checkout-success-text><?= htmlspecialchars(get_order_lead_notice_short()) ?></p>
                    <a href="menu.php?category=soups" class="checkout-success__link">Order more food</a>
                </section>
            </div>

            <aside class="checkout-summary" aria-label="Order summary">
                <div class="checkout-summary__panel">
                    <h2 class="checkout-summary__title">Your order</h2>

                    <ul class="checkout-order" data-checkout-items role="list"></ul>

                    <dl class="checkout-summary__rows">
                        <div class="checkout-summary__row">
                            <dt>Sub total</dt>
                            <dd data-checkout-subtotal>£0.00</dd>
                        </div>
                        <div class="checkout-summary__row">
                            <dt>Discounts</dt>
                            <dd data-checkout-discount>-£0.00</dd>
                        </div>
                        <div class="checkout-summary__row">
                            <dt>Delivery fee</dt>
                            <dd data-checkout-delivery>Free</dd>
                        </div>
                    </dl>

                    <div class="checkout-summary__total">
                        <span>Total to pay</span>
                        <strong data-checkout-total>£0.00</strong>
                    </div>

                    <div class="checkout-summary__field">
                        <label class="checkout-summary__label" for="checkout-coupon">Coupon code</label>
                        <div class="checkout-summary__input-row">
                            <input
                                type="text"
                                id="checkout-coupon"
                                class="checkout-summary__input"
                                placeholder="Type your code here"
                                data-checkout-coupon-input
                                autocomplete="off"
                            >
                            <button type="button" class="checkout-summary__apply" data-checkout-apply-coupon aria-label="Apply coupon code">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                        <p class="checkout-summary__hint" data-checkout-coupon-message hidden></p>
                        <button type="button" class="checkout-summary__remove-coupon" data-checkout-remove-coupon hidden>
                            Remove coupon
                        </button>
                    </div>

                    <button type="button" class="checkout-pay checkout-pay--desktop" data-checkout-sidebar-action hidden>
                        <span class="checkout-pay__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </span>
                        Continue
                    </button>
                </div>
            </aside>
        </div>
    </div>

    <div class="checkout-mobile-bar" data-checkout-mobile-bar>
        <div class="checkout-mobile-bar__total">
            <span class="checkout-mobile-bar__label">Total to pay</span>
            <strong data-checkout-mobile-total>£0.00</strong>
        </div>
        <button type="button" class="checkout-mobile-bar__btn" data-checkout-mobile-action>
            Continue
        </button>
    </div>
</main>

<div class="checkout-agreement-modal" data-checkout-agreement-modal hidden>
    <div class="checkout-agreement-modal__backdrop" data-checkout-agreement-close tabindex="-1" aria-hidden="true"></div>
    <div
        class="checkout-agreement-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="checkout-agreement-modal-title"
        aria-describedby="checkout-agreement-modal-lead"
    >
        <header class="checkout-agreement-modal__head">
            <div>
                <h2 id="checkout-agreement-modal-title" class="checkout-agreement-modal__title"><?= htmlspecialchars($allergy_policy_title) ?></h2>
                <p id="checkout-agreement-modal-lead" class="checkout-agreement-modal__lead">Read this before you pay. Close this window when you are done.</p>
            </div>
            <button type="button" class="checkout-agreement-modal__close" data-checkout-agreement-close aria-label="Close policy">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </header>
        <div class="checkout-agreement-modal__body">
        <div class="checkout-agreement-modal__frame-wrap">
            <iframe
                class="checkout-agreement-modal__frame"
                title="<?= htmlspecialchars($allergy_policy_title) ?>"
                src="<?= htmlspecialchars($allergy_policy_pdf) ?>#toolbar=1"
                data-checkout-agreement-frame
            ></iframe>
        </div>

        <section class="checkout-agreement-modal__contact" aria-labelledby="checkout-agreement-contact-title">
            <div class="checkout-agreement-modal__contact-copy">
                <h3 id="checkout-agreement-contact-title" class="checkout-agreement-modal__contact-title">Contact us if you have any allergies</h3>
                <p class="checkout-agreement-modal__contact-lead">Talk to us before you pay if you need to ask about ingredients or cross-contamination.</p>
            </div>

            <ul class="checkout-agreement-modal__contact-list">
                <li class="checkout-agreement-modal__contact-item">
                    <span class="checkout-agreement-modal__contact-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    <span class="checkout-agreement-modal__contact-body">
                        <span class="checkout-agreement-modal__contact-label">Email</span>
                        <a href="mailto:<?= htmlspecialchars($checkout_contact['email']) ?>" class="checkout-agreement-modal__contact-link">
                            <?= htmlspecialchars($checkout_contact['email']) ?>
                        </a>
                    </span>
                </li>
                <?php foreach ($checkout_contact['phones'] as $phone): ?>
                    <li class="checkout-agreement-modal__contact-item">
                        <span class="checkout-agreement-modal__contact-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <span class="checkout-agreement-modal__contact-body">
                            <span class="checkout-agreement-modal__contact-label">Phone</span>
                            <a href="tel:<?= htmlspecialchars($phone['href']) ?>" class="checkout-agreement-modal__contact-link">
                                <?= htmlspecialchars($phone['display']) ?>
                            </a>
                        </span>
                    </li>
                <?php endforeach; ?>
                <li class="checkout-agreement-modal__contact-item">
                    <span class="checkout-agreement-modal__contact-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                    </span>
                    <span class="checkout-agreement-modal__contact-body">
                        <span class="checkout-agreement-modal__contact-label">WhatsApp</span>
                        <a href="<?= htmlspecialchars($checkout_allergy_whatsapp_url) ?>" class="checkout-agreement-modal__contact-link" target="_blank" rel="noopener noreferrer">
                            Message us on WhatsApp
                        </a>
                    </span>
                </li>
            </ul>
        </section>
        </div>

        <footer class="checkout-agreement-modal__foot">
            <a href="<?= htmlspecialchars($allergy_policy_pdf) ?>" class="checkout-agreement-modal__download" target="_blank" rel="noopener noreferrer" download="food-allergy-or-intolerance.pdf">
                Open in a new tab
            </a>
            <button type="button" class="checkout-agreement-modal__confirm" data-checkout-agreement-close>
                I have read this policy
            </button>
        </footer>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
