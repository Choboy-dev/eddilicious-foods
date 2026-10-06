<?php
/**
 * Product media gallery — 1 hero viewer + 4 thumbnails (5 media slots total).
 */
$gallery = $product['gallery'] ?? [];
if (!$gallery) {
    return;
}

$thumbItems = array_slice($gallery, 1, 4);
$total = count($gallery);
?>

<div
    class="product-gallery"
    data-product-gallery
    data-gallery-items="<?= htmlspecialchars(json_encode($gallery), ENT_QUOTES, 'UTF-8') ?>"
>
    <div class="product-gallery__stage" data-gallery-stage>
        <?php if (!empty($product['discount_label']) && ($product['type'] ?? '') === 'platter'): ?>
            <span class="product-gallery__badge"><?= htmlspecialchars($product['discount_label']) ?> off</span>
        <?php endif; ?>

        <div class="product-gallery__hero" data-gallery-hero>
            <?php
            $hero = $gallery[0];
            $heroIsVideo = ($hero['type'] ?? 'image') === 'video';
            ?>
            <?php if ($heroIsVideo): ?>
                <?php
                $heroPoster = (string) ($hero['poster'] ?? product_real_image($product));
                ?>
                <video
                    class="product-gallery__video"
                    data-gallery-video
                    controls
                    muted
                    loop
                    playsinline
                    autoplay
                    preload="auto"
                    <?php if ($heroPoster !== ''): ?>poster="<?= htmlspecialchars($heroPoster) ?>"<?php endif; ?>
                >
                    <source src="<?= htmlspecialchars($hero['src']) ?>" type="<?= htmlspecialchars(catalog_video_mime($hero['src'])) ?>">
                </video>
            <?php else: ?>
                <img
                    src="<?= htmlspecialchars($hero['src']) ?>"
                    alt="<?= htmlspecialchars($hero['alt'] ?? $product['name']) ?>"
                    class="product-gallery__image"
                    data-gallery-image
                    width="640"
                    height="480"
                    loading="eager"
                >
            <?php endif; ?>
        </div>

        <?php if ($total > 1): ?>
            <div class="product-gallery__nav">
                <button type="button" class="product-gallery__nav-btn" data-gallery-prev aria-label="Previous photo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <span class="product-gallery__counter" data-gallery-counter>1 / <?= $total ?></span>
                <button type="button" class="product-gallery__nav-btn" data-gallery-next aria-label="Next photo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($thumbItems): ?>
        <div class="product-gallery__thumbs" role="list" aria-label="More photos and videos">
            <?php foreach ($gallery as $index => $item): ?>
                <?php if ($index === 0) {
                    continue;
                } ?>
                <?php
                $isVideo = ($item['type'] ?? 'image') === 'video';
                $thumbSrc = $isVideo
                    ? ((string) ($item['poster'] ?? product_real_image($product)))
                    : ((string) ($item['src'] ?? ''));
                if ($isVideo && ($thumbSrc === '' || catalog_is_placeholder_image($thumbSrc))) {
                    $thumbSrc = (string) ($item['src'] ?? '');
                }
                ?>
                <button
                    type="button"
                    class="product-gallery__thumb"
                    role="listitem"
                    data-gallery-thumb
                    data-gallery-index="<?= (int) $index ?>"
                    aria-label="<?= htmlspecialchars($item['alt'] ?? 'View media ' . ($index + 1)) ?>"
                    aria-pressed="false"
                >
                    <img
                        src="<?= htmlspecialchars($thumbSrc) ?>"
                        alt=""
                        class="product-gallery__thumb-image"
                        width="88"
                        height="88"
                        loading="lazy"
                    >
                    <?php if ($isVideo): ?>
                        <span class="product-gallery__thumb-play" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
