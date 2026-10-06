<?php
/**
 * Hero floating product card — expects $product and $position ('left'|'right').
 *
 * @var array<string, mixed> $product
 * @var string $position
 */
$url = product_page_url($product);
$price_min = product_price_min($product);
$price_label = (($product['type'] ?? '') === 'platter')
    ? format_price($price_min)
    : 'from ' . format_price($price_min);
?>
<a
    href="<?= htmlspecialchars($url) ?>"
    class="hero__float hero__float--dish hero__float--dish--<?= htmlspecialchars($position) ?>"
>
    <img
        src="<?= htmlspecialchars($product['image'] ?? '') ?>"
        alt="<?= htmlspecialchars($product['name'] ?? '') ?>"
        class="hero__dish-thumb"
        width="48"
        height="48"
        loading="lazy"
    >
    <div class="hero__dish-info">
        <span class="hero__dish-name"><?= htmlspecialchars($product['name'] ?? '') ?></span>
        <span class="hero__dish-stars" aria-label="5 out of 5 stars">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <?php endfor; ?>
        </span>
    </div>
    <span class="hero__dish-price"><?= htmlspecialchars($price_label) ?></span>
</a>
