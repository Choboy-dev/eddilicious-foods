<?php
/**
 * Platter product card — fixed price, optional sale badge
 * Expects: $product ['id', 'name', 'image', 'price', 'compare_at'?, 'discount_label'?]
 */
$compare_at = $product['compare_at'] ?? null;
$discount_label = $product['discount_label'] ?? null;
$on_sale = $compare_at !== null && $compare_at > $product['price'];
?>
<article
    class="product-card product-card--platter"
    role="listitem"
    data-shop-item
    data-product-id="<?= htmlspecialchars($product['id']) ?>"
    data-price="<?= htmlspecialchars((string) $product['price']) ?>"
    data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
    data-price-min="<?= htmlspecialchars((string) $product['price']) ?>"
    data-price-max="<?= htmlspecialchars((string) $product['price']) ?>"
    <?php if ($on_sale): ?>
        data-compare-at="<?= htmlspecialchars((string) $compare_at) ?>"
    <?php endif; ?>
>
    <div class="product-card__media">
        <?php if ($discount_label): ?>
            <span class="product-card__sale-badge"><?= htmlspecialchars($discount_label) ?></span>
        <?php endif; ?>
        <a href="<?= htmlspecialchars(product_page_url($product)) ?>" class="product-card__image-link" tabindex="-1" aria-hidden="true">
            <?php include __DIR__ . '/product-card-media.php'; ?>
        </a>
        <div class="product-card__actions" aria-label="Product actions">
            <a href="<?= htmlspecialchars(product_page_url($product)) ?>" class="product-card__action" aria-label="View <?= htmlspecialchars($product['name']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                </svg>
            </a>
            <button type="button" class="product-card__action" data-action="wishlist" aria-label="Add <?= htmlspecialchars($product['name']) ?> to wishlist">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="product-card__body">
        <h3 class="product-card__name">
            <a href="<?= htmlspecialchars(product_page_url($product)) ?>" class="product-card__name-link"><?= htmlspecialchars($product['name']) ?></a>
        </h3>

        <p class="product-card__price product-card__price--fixed is-visible">
            <?php if ($on_sale): ?>
                <span class="product-card__price-compare"><?= format_price(catalog_price_pounds($compare_at)) ?></span>
            <?php endif; ?>
            <span class="product-card__price-value"><?= format_price(catalog_price_pounds($product['price'])) ?></span>
        </p>

        <button type="button" class="product-card__cart" data-add-to-cart>
            Add to Cart
        </button>
    </div>
</article>
