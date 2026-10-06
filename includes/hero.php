<?php
/**
 * Edilicious Foods — Homepage Hero
 * Adapted from reference: split layout, floating cards, social proof
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/menu-catalog.php';

$hero_float_products = [
    'left' => find_product_by_id('egusi-soup'),
    'right' => find_product_by_id('jollof-rice'),
];
?>

<section class="hero" aria-labelledby="hero-heading">
    <div class="hero__container">
        <!-- Left: copy + CTAs -->
        <div class="hero__content">
            <h1 id="hero-heading" class="hero__title">
                We cook and deliver delicious food, <span class="hero__title-tail"><span class="hero__title-script">anywhere in the UK</span></span>
            </h1>

            <p class="hero__subtitle">
                We cook Nigerian meals fresh in our kitchen. Order what you like from our menu. We also cook for weddings, birthdays, and professional events.
            </p>

            <div class="hero__cta">
                <a href="menu.php" class="hero__btn hero__btn--primary">Order Now</a>
            </div>
        </div>

        <!-- Right: visual + floating cards -->
        <div class="hero__visual">
            <span class="hero__decor hero__decor--square-red" aria-hidden="true"></span>
            <span class="hero__decor hero__decor--square-yellow" aria-hidden="true"></span>
            <span class="hero__decor hero__decor--dot-red" aria-hidden="true"></span>
            <span class="hero__decor hero__decor--dot-yellow" aria-hidden="true"></span>

            <div class="hero__plate-wrap">
                <img
                    src="assets/images/hero/plate-of-egusi.webp"
                    alt="A plate of egusi soup we made"
                    class="hero__plate"
                    width="560"
                    height="560"
                    loading="eager"
                >
            </div>

            <!-- Clock badge -->
            <div class="hero__float hero__float--clock" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>

            <!-- Flame + dashed arrow -->
            <div class="hero__float hero__float--flame" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                </svg>
                <svg class="hero__arrow" xmlns="http://www.w3.org/2000/svg" width="80" height="60" viewBox="0 0 80 60" fill="none" aria-hidden="true">
                    <path d="M5 45 Q40 10 75 15" stroke="#6b9e78" stroke-width="2" stroke-dasharray="6 4" fill="none"/>
                    <path d="M68 10 L75 15 L70 22" stroke="#6b9e78" stroke-width="2" fill="none"/>
                </svg>
            </div>

            <?php foreach ($hero_float_products as $position => $product): ?>
                <?php if ($product): ?>
                    <?php include __DIR__ . '/partials/hero-product-float.php'; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
