<?php
/**
 * Edilicious Foods — Live promo banner (from admin coupon)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/coupons.php';

$promo = get_active_promo_coupon();
if ($promo === null) {
    return;
}

$coupon_code = (string) ($promo['code'] ?? '');
$promo_hint = (string) ($promo['promo_hint'] ?? '');
?>

<section class="promo-banner" aria-label="Money off your first order">
    <div class="promo-banner__container">
        <p class="promo-banner__lead">
            Save money on your
            <strong class="promo-banner__highlight">first order with us.</strong>
        </p>

        <button
            type="button"
            class="promo-banner__code"
            data-copy-code="<?= htmlspecialchars($coupon_code) ?>"
            aria-label="Copy code <?= htmlspecialchars($coupon_code) ?>"
        >
            <span class="promo-banner__code-text"><?= htmlspecialchars($coupon_code) ?></span>
            <span class="promo-banner__code-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                </svg>
            </span>
        </button>

        <p class="promo-banner__hint">
            <?= htmlspecialchars($promo_hint) ?>
        </p>
    </div>
</section>
