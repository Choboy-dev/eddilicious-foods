<?php
/**
 * Edilicious Foods — Search results
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/menu-catalog.php';

$query = trim($_GET['q'] ?? '');
$results = $query !== '' ? search_products($query) : [];
$result_count = count($results);
$popular_searches = get_popular_searches();

$page_title = $query !== ''
    ? 'Search: ' . $query . ' — Eddilicious Foods'
    : 'Search — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/categories.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/search.js',
    'assets/js/navbar.js',
    'assets/js/products.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="search-page">
    <div class="search-page__container">
        <header class="search-page__header">
            <?php if ($query !== ''): ?>
                <h1 class="search-page__title">
                    Results for <span class="search-page__query">"<?= htmlspecialchars($query) ?>"</span>
                </h1>
                <p class="search-page__meta">
                    <?= $result_count === 1 ? 'We found 1 dish.' : 'We found ' . $result_count . ' dishes.' ?>
                </p>
            <?php else: ?>
                <h1 class="search-page__title">Search our menu</h1>
                <p class="search-page__meta">Type what you want and we will show you matching food.</p>
            <?php endif; ?>
        </header>

        <form class="search-page__form" action="search.php" method="get" role="search">
            <label class="visually-hidden" for="search-page-input">Search our menu</label>
            <div class="search-page__input-wrap">
                <input
                    type="search"
                    id="search-page-input"
                    name="q"
                    class="search-page__input"
                    value="<?= htmlspecialchars($query) ?>"
                    placeholder="Search soups, rice, platters…"
                    autocomplete="off"
                >
            </div>
            <button type="submit" class="search-page__submit" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
            </button>
        </form>

        <?php if ($query !== '' && $result_count > 0): ?>
            <div class="product-section__grid search-page__grid" role="list">
                <?php foreach ($results as $product): ?>
                    <?php if (($product['type'] ?? '') === 'platter'): ?>
                        <?php include __DIR__ . '/includes/partials/platter-card.php'; ?>
                    <?php else: ?>
                        <?php include __DIR__ . '/includes/partials/product-card.php'; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php elseif ($query !== ''): ?>
            <div class="search-page__empty">
                <p>We could not find anything for "<?= htmlspecialchars($query) ?>". Try another word.</p>
                <div class="search-page__suggestions">
                    <?php foreach ($popular_searches as $term): ?>
                        <a href="search.php?q=<?= urlencode($term) ?>" class="search-page__chip">
                            <?= htmlspecialchars($term) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="search-page__empty">
                <p>Start typing above, or try one of these:</p>
                <div class="search-page__suggestions">
                    <?php foreach ($popular_searches as $term): ?>
                        <a href="search.php?q=<?= urlencode($term) ?>" class="search-page__chip">
                            <?= htmlspecialchars($term) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
