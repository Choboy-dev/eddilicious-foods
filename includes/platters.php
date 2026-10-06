<?php
/**
 * Edilicious Foods — Platters (product cards)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/menu-catalog.php';

$platter_products = array_slice(get_products_for_category('platters'), 0, 5);
?>

<section class="product-section product-section--alt" aria-labelledby="platters-heading">
    <div class="product-section__container">
        <header class="product-section__header product-section__header--row">
            <div class="product-section__intro">
                <h2 id="platters-heading" class="product-section__title">
                    Platters
                </h2>
                <p class="product-section__subtitle">Big trays of food for you to share.</p>
            </div>
            <a href="menu.php?category=platters" class="product-section__view-all">
                View All
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </header>

        <div class="product-section__grid" role="list">
            <?php foreach ($platter_products as $product): ?>
                <?php include __DIR__ . '/partials/platter-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
