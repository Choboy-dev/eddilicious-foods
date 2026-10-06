<?php
/**
 * Product card media — image or looping video preview.
 * Expects: $product
 */
$videoSrc = $product['video'] ?? '';
$imageSrc = product_card_image($product);
$useVideo = product_uses_video_preview($product) && $videoSrc !== '';
$poster = product_real_image($product);
?>
<?php if ($useVideo): ?>
    <video
        class="product-card__video"
        muted
        loop
        playsinline
        preload="metadata"
        data-product-card-video
        data-preview-time="0.35"
        <?php if ($poster !== ''): ?>poster="<?= htmlspecialchars($poster) ?>"<?php endif; ?>
        aria-label="<?= htmlspecialchars($product['name'] ?? 'Product video') ?>"
    >
        <source src="<?= htmlspecialchars($videoSrc) ?>" type="<?= htmlspecialchars(catalog_video_mime($videoSrc)) ?>">
    </video>
<?php else: ?>
    <img
        src="<?= htmlspecialchars($imageSrc) ?>"
        alt="<?= htmlspecialchars($product['name'] ?? '') ?>"
        class="product-card__image"
        width="280"
        height="220"
        loading="lazy"
    >
<?php endif; ?>
