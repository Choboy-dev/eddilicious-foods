<?php
/**
 * Expose product image lookup to JavaScript on cart/checkout pages.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/menu-catalog.php';
?>
<script>
window.EDDILICIOUS_PRODUCT_IMAGES = <?= json_encode(get_product_images_for_client(), JSON_UNESCAPED_UNICODE) ?>;
window.EDDILICIOUS_PRODUCT_CATALOG = <?= json_encode(get_products_catalog_for_client(), JSON_UNESCAPED_UNICODE) ?>;
window.EDDILICIOUS_POPULAR_SEARCHES = <?= json_encode(get_popular_searches(), JSON_UNESCAPED_UNICODE) ?>;
</script>
