<?php
/**
 * Edilicious Foods — Product detail
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/menu-catalog.php';

$product_id = trim($_GET['id'] ?? '');
$product = $product_id !== '' ? find_product_by_id($product_id) : null;

if ($product === null) {
    http_response_code(404);
    $page_title = 'Product not found — Eddilicious Foods';
    $page_styles = [
        'assets/css/navbar.css',
        'assets/css/product.css',
        'assets/css/footer.css',
    ];
    $page_scripts = [
        'assets/js/cart-store.js',
        'assets/js/wishlist-store.js',
        'assets/js/coupon-store.js',
        'assets/js/navbar.js',
        'assets/js/footer.js',
    ];

    include __DIR__ . '/includes/head.php';
    include __DIR__ . '/includes/navbar.php';
    ?>

    <main class="product-page product-page--empty">
        <div class="product-page__container">
            <div class="product-empty">
                <h1 class="product-empty__title">We could not find that dish</h1>
                <p class="product-empty__text">It may have moved. Browse our menu to find what you want.</p>
                <a href="menu.php" class="product-btn product-btn--primary">Browse menu</a>
            </div>
        </div>
    </main>

    <?php
    include __DIR__ . '/includes/footer.php';
    include __DIR__ . '/includes/scripts.php';
    return;
}

$category = get_menu_category($product['category'] ?? '');
$category_name = $category['name'] ?? 'Menu';
$category_url = 'menu.php?category=' . urlencode($product['category'] ?? 'soups');
$is_platter = ($product['type'] ?? '') === 'platter';
$description_long = $product['description_long'] ?? empty_product_description();
$description_html = (string) ($description_long['html'] ?? '');
$reviews = $product['reviews'] ?? [];
$rating_average = $product['rating_average'] ?? 0;
$review_count = $product['review_count'] ?? count($reviews);
$rating_breakdown = $product['rating_breakdown'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$related = get_related_products($product, 4);

$page_title = $product['name'] . ' — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/categories.css',
    'assets/css/product.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/product-page.js',
    'assets/js/product-reviews.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main
    class="product-page"
    data-product-page
    data-product-id="<?= htmlspecialchars($product['id']) ?>"
    data-product-type="<?= htmlspecialchars($product['type'] ?? 'litre') ?>"
    <?php if ($is_platter): ?>
        data-product-price="<?= htmlspecialchars((string) $product['price']) ?>"
        <?php if (!empty($product['compare_at'])): ?>
            data-product-compare="<?= htmlspecialchars((string) $product['compare_at']) ?>"
        <?php endif; ?>
    <?php else: ?>
        data-product-prices="<?= htmlspecialchars(json_encode($product['prices'] ?? [])) ?>"
    <?php endif; ?>
>
    <div class="product-page__container">
        <nav class="product-breadcrumb" aria-label="Breadcrumb">
            <a href="menu.php" class="product-breadcrumb__link">Menu</a>
            <span class="product-breadcrumb__sep" aria-hidden="true">/</span>
            <a href="<?= htmlspecialchars($category_url) ?>" class="product-breadcrumb__link"><?= htmlspecialchars($category_name) ?></a>
            <span class="product-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="product-breadcrumb__current" aria-current="page"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="product-detail">
            <div class="product-detail__media-col">
                <?php include __DIR__ . '/includes/partials/product-gallery.php'; ?>
            </div>

            <div class="product-detail__buy" id="product-detail-buy">
                <a href="<?= htmlspecialchars($category_url) ?>" class="product-detail__category"><?= htmlspecialchars($category_name) ?></a>
                <h1 class="product-detail__title"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-detail__rating-slot" data-product-rating-slot>
                <?php if ($review_count > 0): ?>
                    <a href="#product-panel-reviews" class="product-detail__rating" data-open-reviews-tab aria-label="<?= htmlspecialchars((string) $rating_average) ?> out of 5 from <?= (int) $review_count ?> reviews">
                        <span class="product-detail__stars" aria-hidden="true">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= $s <= round($rating_average) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>
                            <?php endfor; ?>
                        </span>
                        <span class="product-detail__rating-text"><?= htmlspecialchars((string) $rating_average) ?> · <?= (int) $review_count ?> reviews</span>
                    </a>
                <?php endif; ?>
                </div>

                <div class="product-detail__price" data-product-price-display aria-live="polite">
                    <?php if ($is_platter): ?>
                        <?php if (!empty($product['compare_at']) && $product['compare_at'] > $product['price']): ?>
                            <span class="product-detail__price-compare"><?= format_price((float) $product['compare_at']) ?></span>
                        <?php endif; ?>
                        <span class="product-detail__price-value"><?= format_price((float) $product['price']) ?></span>
                    <?php else: ?>
                        <span class="product-detail__price-placeholder">Pick a size to see the price</span>
                    <?php endif; ?>
                </div>

                <?php if (!$is_platter): ?>
                    <fieldset class="product-sizes">
                        <legend class="product-sizes__label">Pick a size</legend>
                        <div class="product-sizes__options" role="radiogroup" aria-label="Pick a size">
                            <?php foreach ($product['prices'] ?? [] as $litres => $amount): ?>
                                <button
                                    type="button"
                                    class="product-sizes__option"
                                    role="radio"
                                    aria-checked="false"
                                    data-size-option
                                    data-value="<?= htmlspecialchars((string) $litres) ?>"
                                    data-label="<?= htmlspecialchars(product_size_label((string) $litres)) ?>"
                                    data-price="<?= htmlspecialchars((string) $amount) ?>"
                                >
                                    <span class="product-sizes__option-main"><?= htmlspecialchars(product_size_label((string) $litres)) ?></span>
                                    <span class="product-sizes__option-price"><?= format_price((float) $amount) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php else: ?>
                    <p class="product-detail__size-note">One tray. Good for sharing at an event.</p>
                <?php endif; ?>

                <div class="product-qty" data-product-qty>
                    <span class="product-qty__label" id="product-qty-label">Quantity</span>
                    <div class="product-qty__control" role="group" aria-labelledby="product-qty-label">
                        <button type="button" class="product-qty__btn" data-qty-decrease aria-label="Decrease quantity">−</button>
                        <input
                            type="text"
                            class="product-qty__input"
                            value="1"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            aria-live="polite"
                            data-qty-input
                            readonly
                        >
                        <button type="button" class="product-qty__btn" data-qty-increase aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <div class="product-detail__actions">
                    <button type="button" class="product-btn product-btn--primary" data-product-add-cart<?= $is_platter ? '' : ' disabled' ?>>
                        Add to cart
                    </button>
                    <button
                        type="button"
                        class="product-btn product-btn--wishlist"
                        data-product-wishlist
                        aria-pressed="false"
                        aria-label="Save <?= htmlspecialchars($product['name']) ?> to wishlist"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                        </svg>
                        <span>Save</span>
                    </button>
                </div>

                <ul class="product-trust" aria-label="Why order with us">
                    <li class="product-trust__item">
                        <span class="product-trust__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v2"/><path d="M6.8 7.8 5.6 9"/><path d="m5.6 15 1.2 1.2"/><path d="M12 19v2"/><path d="m17.4 16.2 1.2-1.2"/><path d="m18.6 9-1.2-1.2"/><circle cx="12" cy="12" r="4"/></svg>
                        </span>
                        <span class="product-trust__label">Cooked fresh for your order</span>
                    </li>
                    <li class="product-trust__item">
                        <span class="product-trust__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                        </span>
                        <span class="product-trust__label">UK delivery with 24 hours notice</span>
                    </li>
                    <li class="product-trust__item">
                        <span class="product-trust__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </span>
                        <span class="product-trust__label">Clear prices in GBP</span>
                    </li>
                </ul>
            </div>
        </div>

        <section class="product-tabs" data-product-tabs>
            <div class="product-tabs__nav" role="tablist" aria-label="Product details">
                <button
                    type="button"
                    class="product-tabs__tab is-active"
                    role="tab"
                    id="product-tab-description"
                    aria-selected="true"
                    aria-controls="product-panel-description"
                    data-product-tab="description"
                >
                    Description
                </button>
                <button
                    type="button"
                    class="product-tabs__tab"
                    role="tab"
                    id="product-tab-reviews"
                    aria-selected="false"
                    aria-controls="product-panel-reviews"
                    data-product-tab="reviews"
                >
                    Reviews<?= $review_count > 0 ? ' (' . (int) $review_count . ')' : '' ?>
                </button>
            </div>

            <div
                class="product-tabs__panel is-active"
                role="tabpanel"
                id="product-panel-description"
                aria-labelledby="product-tab-description"
                data-product-panel="description"
            >
                <?php if ($description_html !== ''): ?>
                    <div class="product-description-rich">
                        <?= $description_html ?>
                    </div>
                <?php else: ?>
                    <p class="product-tabs__empty">No description yet.</p>
                <?php endif; ?>
            </div>

            <?php include __DIR__ . '/includes/partials/product-reviews-panel.php'; ?>
        </section>

        <?php if ($related): ?>
            <section class="product-related" aria-labelledby="product-related-heading">
                <div class="product-related__head">
                    <h2 id="product-related-heading" class="product-related__title">More from <?= htmlspecialchars(strtolower($category_name)) ?></h2>
                    <a href="<?= htmlspecialchars($category_url) ?>" class="product-related__link">View all</a>
                </div>
                <div class="product-related__grid" role="list">
                    <?php foreach ($related as $related_product): ?>
                        <a href="<?= htmlspecialchars(product_page_url($related_product)) ?>" class="product-related__card" role="listitem">
                            <img
                                src="<?= htmlspecialchars($related_product['image']) ?>"
                                alt=""
                                class="product-related__image"
                                width="120"
                                height="96"
                                loading="lazy"
                            >
                            <span class="product-related__name"><?= htmlspecialchars($related_product['name']) ?></span>
                            <span class="product-related__price"><?= htmlspecialchars(product_price_range_label($related_product)) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <div class="product-mobile-bar" data-product-mobile-bar>
        <div class="product-mobile-bar__price" data-product-mobile-price aria-live="polite">
            <?php if ($is_platter): ?>
                <span class="product-mobile-bar__label">Price</span>
                <strong><?= format_price((float) $product['price']) ?></strong>
            <?php else: ?>
                <span class="product-mobile-bar__label">Pick a size</span>
                <strong data-product-mobile-price-value>—</strong>
            <?php endif; ?>
        </div>
        <button type="button" class="product-btn product-btn--primary product-mobile-bar__btn" data-product-mobile-add<?= $is_platter ? '' : ' disabled' ?>>
            Add to cart
        </button>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
