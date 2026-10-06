<?php
/**
 * Edilicious Foods — Full menu product catalog
 */

require_once __DIR__ . '/catalog-media.php';

/**
 * @return array<string, array{slug: string, name: string, title: string, subtitle: string, type: string}>
 */
function get_menu_categories(): array
{
    return [
        'soups' => [
            'slug' => 'soups',
            'name' => 'Soups',
            'title' => 'Our Nigerian soups',
            'subtitle' => 'We sell these by the litre. Pick 2 litres or 4 litres. Order at least 24 hours ahead.',
            'type' => 'litre',
        ],
        'stews' => [
            'slug' => 'stews',
            'name' => 'Stews',
            'title' => 'Our stews',
            'subtitle' => 'We cook these fresh. Pick 2 litres or 4 litres. Order at least 24 hours ahead.',
            'type' => 'litre',
        ],
        'rice' => [
            'slug' => 'rice',
            'name' => 'Rice Dishes',
            'title' => 'Our rice dishes',
            'subtitle' => 'Fresh rice by the litre. Order at least 24 hours ahead.',
            'type' => 'litre',
        ],
        'platters' => [
            'slug' => 'platters',
            'name' => 'Platters',
            'title' => 'Our platters',
            'subtitle' => 'Big trays of food for you to share. Order at least 24 hours ahead.',
            'type' => 'platter',
        ],
        'peppered-proteins' => [
            'slug' => 'peppered-proteins',
            'name' => 'Peppered Proteins',
            'title' => 'Our peppered proteins',
            'subtitle' => 'Spicy meats and fish, sold by the litre. Order at least 24 hours ahead.',
            'type' => 'litre',
        ],
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function get_menu_catalog_static(): array
{
    static $catalog = null;

    if ($catalog !== null) {
        return $catalog;
    }

    $litre = static fn(array $spec): array => catalog_product($spec);

    $catalog = [
        // ---- Soups ----
        $litre(['id' => 'egusi-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Egusi Soup', 'image' => 'egusi-soup.jpeg', 'prices' => ['2' => 70.00, '4' => 135.00]]),
        $litre(['id' => 'bitterleaf-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Bitterleaf Soup', 'image' => 'bitterleaf-soup.jpeg', 'prices' => ['2' => 70.00, '4' => 140.00]]),
        $litre(['id' => 'edikang-ikong-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Edikang Ikong Soup', 'image' => 'edikang-ikong-soup.jpeg', 'prices' => ['2' => 70.00, '4' => 132.00]]),
        $litre(['id' => 'seafood-oha-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Seafood Oha Soup', 'image' => 'seafood-oha-soup.jpeg', 'prices' => ['2' => 75.00, '4' => 140.00]]),
        $litre(['id' => 'oha-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Oha Soup', 'video' => 'oha-soup.mp4', 'prices' => ['2' => 65.00, '4' => 130.00]]),
        $litre(['id' => 'white-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'White Soup', 'image' => 'white-soup.jpeg', 'video' => 'white-soup.mp4', 'prices' => ['2' => 72.00, '4' => 135.00]]),
        $litre(['id' => 'goat-meat-peppersoup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Goat Meat Pepper Soup', 'image' => 'goat-meat-peppersoup.jpeg', 'prices' => ['2' => 55.00, '4' => 105.00]]),
        $litre(['id' => 'assorted-goat-meat-peppersoup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Assorted Goat Meat Pepper Soup', 'image' => 'assorted-goat-meat-peppersoup.jpeg', 'prices' => ['2' => 60.00, '4' => 115.00]]),
        $litre(['id' => 'ekpang-nkwukwo', 'category' => 'soups', 'type' => 'litre', 'name' => 'Ekpang Nkwukwo', 'image' => 'ekpang-nkwukwo.jpeg', 'prices' => ['2' => 68.00, '4' => 128.00]]),
        $litre(['id' => 'fisherman-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Fisherman Soup', 'video' => 'fisherman-soup.mp4', 'prices' => ['2' => 68.00, '4' => 128.00]]),
        $litre(['id' => 'ogbono-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Ogbono Soup', 'video' => 'ogbono-soup.mp4', 'prices' => ['2' => 68.00, '4' => 128.00]]),
        $litre(['id' => 'vegetable-soup', 'category' => 'soups', 'type' => 'litre', 'name' => 'Vegetable Soup', 'video' => 'vegetable-soup.mp4', 'prices' => ['2' => 58.00, '4' => 115.00]]),

        // ---- Stews ----
        $litre(['id' => 'chicken-stew', 'category' => 'stews', 'type' => 'litre', 'name' => 'Chicken Stew', 'image' => 'chicken-stew.jpeg', 'prices' => ['2' => 50.00, '4' => 95.00]]),
        $litre(['id' => 'fish-stew', 'category' => 'stews', 'type' => 'litre', 'name' => 'Fish Stew', 'image' => 'fish-stew.jpeg', 'prices' => ['2' => 52.00, '4' => 100.00]]),

        // ---- Rice ----
        $litre(['id' => 'jollof-rice', 'category' => 'rice', 'type' => 'litre', 'name' => 'Jollof Rice', 'image' => 'jollof-rice.jpeg', 'prices' => ['2' => 35.00, '4' => 70.00]]),
        $litre(['id' => 'basmati-jollof-rice', 'category' => 'rice', 'type' => 'litre', 'name' => 'Basmati Jollof Rice', 'image' => 'basmati-jollof-rice.jpeg', 'prices' => ['2' => 45.00, '4' => 85.00]]),
        $litre(['id' => 'jollof-rice-with-chopped-goat-meat', 'category' => 'rice', 'type' => 'litre', 'name' => 'Jollof Rice with Chopped Goat Meat', 'image' => 'jollof-rice-with-chopped-goat-meat.jpeg', 'prices' => ['2' => 55.00, '4' => 100.00]]),
        $litre(['id' => 'fried-rice-with-liver', 'category' => 'rice', 'type' => 'litre', 'name' => 'Fried Rice with Liver', 'image' => 'fried-rice-with-liver.jpeg', 'prices' => ['2' => 40.00, '4' => 75.00]]),
        $litre(['id' => 'fried-rice-with-stewed-turkey', 'category' => 'rice', 'type' => 'litre', 'name' => 'Fried Rice with Stewed Turkey', 'image' => 'friend-rice-with-stewed-turkey.jpeg', 'prices' => ['2' => 54.00, '4' => 102.00]]),
        $litre(['id' => 'shrimp-fried-rice', 'category' => 'rice', 'type' => 'litre', 'name' => 'Shrimp Fried Rice', 'image' => 'shrimp-fried-rice.jpeg', 'prices' => ['2' => 58.00, '4' => 105.00]]),
        $litre(['id' => 'shrimp-coconut-rice', 'category' => 'rice', 'type' => 'litre', 'name' => 'Shrimp Coconut Rice', 'image' => 'shrimp-coconut-rice.jpeg', 'prices' => ['2' => 58.00, '4' => 105.00]]),

        // ---- Peppered proteins ----
        $litre(['id' => 'peppered-goat-meat', 'category' => 'peppered-proteins', 'type' => 'litre', 'name' => 'Peppered Goat Meat', 'image' => 'peppered-goat-meat.jpeg', 'prices' => ['2' => 72.00, '4' => 135.00]]),

        // ---- Platters ----
        catalog_product(['id' => 'beef-suya-platter', 'category' => 'platters', 'type' => 'platter', 'name' => 'Beef Suya Platter', 'image' => 'beef-suya-platter.jpeg', 'price' => 120.00]),
        catalog_product(['id' => 'ram-suya', 'category' => 'platters', 'type' => 'platter', 'name' => 'Ram Suya', 'image' => 'ram-suya.jpeg', 'price' => 120.00]),
        catalog_product(['id' => 'ram-suya-with-masa', 'category' => 'platters', 'type' => 'platter', 'name' => 'Ram Suya with Masa', 'image' => 'ram-suya-with-masa.jpeg', 'price' => 150.00]),
        catalog_product(['id' => 'peppered-chicken-platter', 'category' => 'platters', 'type' => 'platter', 'name' => 'Peppered Chicken Platter', 'image' => 'peppered-chicken-platter.jpeg', 'price' => 130.00]),
        catalog_product(['id' => 'stewed-chicken-platter', 'category' => 'platters', 'type' => 'platter', 'name' => 'Stewed Chicken Platter', 'image' => 'stewed-chicken-platter.jpeg', 'price' => 135.00]),
        catalog_product(['id' => 'chicken-in-sauce-platter', 'category' => 'platters', 'type' => 'platter', 'name' => 'Chicken in Sauce Platter', 'image' => 'chicken-in-sauce-platter.jpeg', 'price' => 140.00]),
        catalog_product(['id' => 'jollof-rice-plantain-peppered-goat-meat-platter', 'category' => 'platters', 'type' => 'platter', 'name' => 'Jollof Rice with Plantain and Peppered Goat Meat', 'image' => 'jollof-rice-plantains-peppered-goat-meat-platter.jpeg', 'price' => 180.00]),
        catalog_product(['id' => 'salad-platter', 'category' => 'platters', 'type' => 'platter', 'name' => 'Salad Platter', 'image' => 'salad-platter.jpeg', 'price' => 80.00]),
        catalog_product(['id' => 'moi-moi', 'category' => 'platters', 'type' => 'platter', 'name' => 'Moi Moi', 'image' => 'moi-moi.jpeg', 'price' => 65.00]),
        catalog_product(['id' => 'small-chops', 'category' => 'platters', 'type' => 'platter', 'name' => 'Small Chops', 'video' => 'small-chops.mp4', 'price' => 95.00]),
    ];

    return $catalog;
}

/**
 * @return list<array<string, mixed>>
 */
function get_menu_catalog(): array
{
    static $merged = null;

    if ($merged !== null) {
        return $merged;
    }

    $merged = get_menu_catalog_static();

    if (class_exists('CatalogAdminRepository')) {
        $merged = CatalogAdminRepository::mergeWithStatic($merged);
    }

    return $merged;
}

/**
 * @return array<string, array{slug: string, name: string, title: string, subtitle: string, type: string}>|null
 */
function get_menu_category(string $slug): ?array
{
    $categories = get_menu_categories();

    return $categories[$slug] ?? null;
}

/**
 * @return list<array<string, mixed>>
 */
function get_products_for_category(string $slug): array
{
    return array_values(array_filter(
        get_menu_catalog(),
        static fn(array $product): bool => $product['category'] === $slug
    ));
}

function product_price_min(array $product): float
{
    if (($product['type'] ?? '') === 'platter') {
        return (float) ($product['price'] ?? 0);
    }

    $prices = $product['prices'] ?? [];
    if (!is_array($prices) || $prices === []) {
        return 0.0;
    }

    $values = array_map('catalog_price_pounds', array_values($prices));

    return (float) min($values);
}

function product_price_max(array $product): float
{
    if (($product['type'] ?? '') === 'platter') {
        return (float) ($product['price'] ?? 0);
    }

    $prices = $product['prices'] ?? [];
    if (!is_array($prices) || $prices === []) {
        return 0.0;
    }

    $values = array_map('catalog_price_pounds', array_values($prices));

    return (float) max($values);
}

function product_price_range_label(array $product): string
{
    $min = product_price_min($product);
    $max = product_price_max($product);

    if ($min === $max) {
        return format_price($min);
    }

    return format_price($min) . ' – ' . format_price($max);
}

function product_sizes_label(array $product): string
{
    if (($product['type'] ?? '') === 'platter') {
        return 'size: 1 tray';
    }

    return 'size: 2 Litres, 4 Litres';
}

function product_badge_label(array $product): string
{
    if (($product['type'] ?? '') === 'platter') {
        return '1 Tray';
    }

    return '2 Litres';
}

/**
 * URL for a product detail page.
 */
function product_page_url(array $product): string
{
    return 'product.php?id=' . urlencode($product['id'] ?? '');
}

/**
 * Short product description for the detail page.
 */
function get_product_description(array $product): string
{
    $name = $product['name'] ?? 'This dish';
    $type = $product['type'] ?? 'litre';

    if ($type === 'platter') {
        return 'We cook ' . $name . ' fresh for your event or get-together. One tray feeds a group. ' . get_order_lead_product_suffix();
    }

    $descriptions = [
        'soups' => 'We cook ' . $name . ' with fresh ingredients. Pick 2 litres or 4 litres. ' . get_order_lead_product_suffix(),
        'stews' => 'We slow-cook ' . $name . ' so the flavour builds. Pick 2 litres or 4 litres. ' . get_order_lead_product_suffix(),
        'rice' => 'We make ' . $name . ' fresh for your order. Pick 2 litres or 4 litres. ' . get_order_lead_product_suffix(),
        'peppered-proteins' => 'We spice and cook ' . $name . ' to order. Pick 2 litres or 4 litres. ' . get_order_lead_product_suffix(),
    ];

    $category = $product['category'] ?? '';

    return $descriptions[$category] ?? 'We cook ' . $name . ' fresh for your order. ' . get_order_lead_product_suffix();
}

/**
 * Other products in the same category for the detail page.
 *
 * @return list<array<string, mixed>>
 */
function get_related_products(array $product, int $limit = 4): array
{
    $category = $product['category'] ?? '';
    $id = $product['id'] ?? '';

    $related = array_values(array_filter(
        get_products_for_category($category),
        static fn(array $item): bool => ($item['id'] ?? '') !== $id
    ));

    return array_slice($related, 0, $limit);
}

require_once __DIR__ . '/product-detail.php';

/**
 * Find a single product by id.
 */
function find_product_by_id(string $id): ?array
{
    foreach (get_menu_catalog() as $product) {
        if (($product['id'] ?? '') === $id) {
            return enrich_product_detail($product);
        }
    }

    return null;
}

/**
 * Product image paths for client-side cart fallbacks.
 *
 * @return array<string, string>
 */
function get_product_images_for_client(): array
{
    $images = [];

    foreach (get_menu_catalog() as $product) {
        if (!empty($product['id'])) {
            $images[$product['id']] = product_card_image($product);
        }
    }

    return $images;
}

/**
 * Product catalog for client-side wishlist and lookups.
 *
 * @return array<string, array<string, mixed>>
 */
function get_products_catalog_for_client(): array
{
    $client = [];

    foreach (get_menu_catalog() as $product) {
        $id = $product['id'] ?? '';
        if ($id === '') {
            continue;
        }

        $entry = [
            'id' => $id,
            'name' => $product['name'],
            'image' => product_card_image($product),
            'type' => $product['type'],
            'category' => $product['category'],
            'categoryLabel' => get_menu_category($product['category'] ?? '')['name'] ?? '',
            'menuUrl' => 'menu.php?category=' . urlencode($product['category'] ?? 'soups'),
            'productUrl' => product_page_url($product),
        ];

        if (($product['type'] ?? '') === 'platter') {
            $price = (float) ($product['price'] ?? 0);
            $entry['price'] = $price;
            $entry['priceMin'] = $price;
            $entry['priceMax'] = $price;
            if (!empty($product['compare_at'])) {
                $entry['compareAt'] = (float) $product['compare_at'];
            }
            if (!empty($product['discount_label'])) {
                $entry['discountLabel'] = $product['discount_label'];
            }
        } else {
            $entry['prices'] = $product['prices'] ?? [];
            $entry['priceMin'] = product_price_min($product);
            $entry['priceMax'] = product_price_max($product);
        }

        $client[$id] = $entry;
    }

    return $client;
}

/**
 * Popular search suggestions for the overlay.
 *
 * @return list<string>
 */
function get_popular_searches(): array
{
    return [
        'Jollof Rice',
        'Egusi Soup',
        'Beef Suya Platter',
    ];
}

/**
 * Search products by name or category.
 *
 * @return list<array<string, mixed>>
 */
function search_products(string $query): array
{
    $needle = strtolower(trim($query));
    if ($needle === '') {
        return [];
    }

    $results = [];

    foreach (get_menu_catalog() as $product) {
        $name = strtolower($product['name'] ?? '');
        $category = get_menu_category($product['category'] ?? '');
        $categoryName = strtolower($category['name'] ?? '');
        $categorySlug = strtolower($product['category'] ?? '');

        if (
            str_contains($name, $needle) ||
            str_contains($categoryName, $needle) ||
            str_contains($categorySlug, str_replace(' ', '-', $needle))
        ) {
            $results[] = $product;
        }
    }

    return $results;
}
