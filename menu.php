<?php
/**
 * Edilicious Foods — Menu shop (category listings)
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/menu-catalog.php';

$category_slug = $_GET['category'] ?? 'soups';
$category = get_menu_category($category_slug);

if ($category === null) {
    header('Location: menu.php?category=soups', true, 302);
    exit;
}

$products = get_products_for_category($category_slug);
$all_categories = get_menu_categories();

$page_title = $category['title'] . ' — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/categories.css',
    'assets/css/shop.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/custom-select.js',
    'assets/js/products.js',
    'assets/js/shop.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="shop-page">
    <div class="shop-page__container">
        <header class="shop-page__header">
            <h1 class="shop-page__title"><?= htmlspecialchars($category['title']) ?></h1>
            <p class="shop-page__subtitle"><?= htmlspecialchars($category['subtitle']) ?></p>
        </header>

        <nav class="shop-categories" aria-label="Food categories">
            <ul class="shop-categories__list">
                <?php foreach ($all_categories as $cat): ?>
                    <?php $is_active = $cat['slug'] === $category_slug; ?>
                    <li>
                        <a
                            href="menu.php?category=<?= htmlspecialchars($cat['slug']) ?>"
                            class="shop-categories__link<?= $is_active ? ' is-active' : '' ?>"
                            <?= $is_active ? 'aria-current="page"' : '' ?>
                        >
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="shop-toolbar" aria-label="Sort and filter products">
            <div class="shop-toolbar__field">
                <label class="shop-toolbar__label" for="shop-sort">Sort by</label>
                <div class="shop-toolbar__control">
                    <select id="shop-sort" class="shop-toolbar__select" data-shop-sort data-custom-select>
                        <option value="name-asc">Name (A to Z)</option>
                        <option value="name-desc">Name (Z to A)</option>
                        <option value="price-asc">Price (low to high)</option>
                        <option value="price-desc">Price (high to low)</option>
                    </select>
                </div>
            </div>

            <div class="shop-toolbar__field">
                <label class="shop-toolbar__label" for="shop-price-filter">Filter by price</label>
                <div class="shop-toolbar__control">
                    <select id="shop-price-filter" class="shop-toolbar__select" data-shop-price-filter data-custom-select>
                        <option value="all">All prices</option>
                        <option value="under-50">Under £50</option>
                        <option value="50-100">£50 to £100</option>
                        <option value="100-150">£100 to £150</option>
                        <option value="over-150">Over £150</option>
                    </select>
                </div>
            </div>

            <p class="shop-toolbar__count" data-shop-count aria-live="polite"></p>
        </div>

        <div class="product-section__grid shop-page__grid" data-shop-grid role="list">
            <?php foreach ($products as $product): ?>
                <?php if (($product['type'] ?? '') === 'platter'): ?>
                    <?php include __DIR__ . '/includes/partials/platter-card.php'; ?>
                <?php elseif (($product['type'] ?? '') === 'drink'): ?>
                    <?php include __DIR__ . '/includes/partials/drink-card.php'; ?>
                <?php else: ?>
                    <?php include __DIR__ . '/includes/partials/product-card.php'; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <p class="shop-empty" data-shop-empty hidden>We don't have any food in this price range. Try another filter.</p>

        <div class="shop-more-wrap">
            <button type="button" class="shop-more-btn" data-shop-view-more hidden>
                Load more
            </button>
        </div>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
