<?php
/**
 * Drink product card — 1 or 2 litre sizes, video preview until photos are added.
 * Expects: $product
 */
$price_min = min($product['prices']);
$price_max = max($product['prices']);
$size_options = [
    '1' => ['label' => '1 Litre', 'price' => $product['prices']['1'] ?? $price_min],
    '2' => ['label' => '2 Litres', 'price' => $product['prices']['2'] ?? $price_max],
];
?>
<article
    class="product-card product-card--drink"
    role="listitem"
    data-shop-item
    data-product-id="<?= htmlspecialchars($product['id']) ?>"
    data-prices="<?= htmlspecialchars(json_encode($product['prices'])) ?>"
    data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
    data-price-min="<?= htmlspecialchars((string) $price_min) ?>"
    data-price-max="<?= htmlspecialchars((string) $price_max) ?>"
>
    <div class="product-card__media">
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

        <div class="product-card__field">
            <div class="qty-dropdown" data-qty-dropdown>
                <select
                    id="qty-<?= htmlspecialchars($product['id']) ?>"
                    class="qty-dropdown__native"
                    data-qty-select
                    aria-hidden="true"
                    tabindex="-1"
                >
                    <option value="" selected disabled>Pick a size</option>
                    <?php foreach ($size_options as $value => $option): ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($option['label']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button
                    type="button"
                    class="qty-dropdown__trigger"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                    aria-controls="qty-menu-<?= htmlspecialchars($product['id']) ?>"
                    id="qty-trigger-<?= htmlspecialchars($product['id']) ?>"
                >
                    <span class="qty-dropdown__value qty-dropdown__value--placeholder" data-qty-label>
                        Pick a size
                    </span>
                    <span class="qty-dropdown__chevron" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </span>
                </button>

                <ul
                    class="qty-dropdown__menu"
                    id="qty-menu-<?= htmlspecialchars($product['id']) ?>"
                    role="listbox"
                    aria-label="Pick a size for <?= htmlspecialchars($product['name']) ?>"
                    hidden
                >
                    <?php foreach ($size_options as $value => $option): ?>
                        <li class="qty-dropdown__option" role="option" data-value="<?= htmlspecialchars($value) ?>" tabindex="-1" aria-selected="false">
                            <span class="qty-dropdown__option-main"><?= htmlspecialchars($option['label']) ?></span>
                            <span class="qty-dropdown__option-sub"><?= format_price((float) $option['price']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <p class="product-card__price" data-price-display aria-live="polite"></p>

        <button type="button" class="product-card__cart" data-add-to-cart disabled>
            Add to Cart
        </button>
    </div>
</article>
