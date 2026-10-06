<?php
/**
 * Edilicious Foods — Checkout success (Stripe return URL)
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/payments/StripeService.php';

$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$result = ['ok' => false, 'error' => 'We could not confirm your payment.'];

if ($sessionId !== '') {
    $result = StripeService::verifyCheckoutSession($sessionId);
}

$order = $result['order'] ?? null;
$customerName = '';
$orderRef = '';
$fulfillmentLabel = 'We will be in touch about your order.';

if (is_array($order)) {
    $customerName = trim((string) ($order['customer_name'] ?? ''));
    $orderRef = (string) ($order['order_ref'] ?? '');
    $fulfillmentLabel = get_order_success_message((string) ($order['fulfillment'] ?? 'delivery'));
}

$page_title = 'Order confirmed — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/order-lead-notice.css',
    'assets/css/checkout.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/checkout-success.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="checkout-page checkout-page--success">
    <div class="checkout-page__container">
        <?php if ($result['ok'] && is_array($order)): ?>
            <section class="checkout-success checkout-success--page" aria-live="polite">
                <div class="checkout-success__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </div>
                <h1 class="checkout-success__title">We got your order</h1>
                <p class="checkout-success__text">
                    <?php if ($customerName !== ''): ?>
                        Thanks, <?= htmlspecialchars($customerName) ?>.
                    <?php else: ?>
                        Thanks for your order.
                    <?php endif; ?>
                    <?= htmlspecialchars($fulfillmentLabel) ?>
                </p>
                <?php if ($orderRef !== ''): ?>
                    <p class="checkout-success__ref">Order reference: <strong><?= htmlspecialchars($orderRef) ?></strong></p>
                <?php endif; ?>
                <div class="checkout-success__actions">
                    <a href="account.php" class="checkout-success__link">View your account</a>
                    <a href="menu.php?category=soups" class="checkout-success__link checkout-success__link--ghost">Order more food</a>
                </div>
            </section>
        <?php else: ?>
            <section class="checkout-success checkout-success--page checkout-success--error" aria-live="polite">
                <h1 class="checkout-success__title">Payment not confirmed</h1>
                <p class="checkout-success__text"><?= htmlspecialchars($result['error'] ?? 'Something went wrong.') ?></p>
                <a href="checkout.php" class="checkout-success__link">Back to checkout</a>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
