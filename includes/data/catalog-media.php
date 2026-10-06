<?php
/**
 * Product media helpers — images, videos, optimized delivery paths.
 */

const CATALOG_PRODUCT_PLACEHOLDER = 'assets/images/products/drink-placeholder.svg';

function catalog_site_root(): string
{
    return dirname(__DIR__, 2);
}

function catalog_asset(string $file): string
{
    return 'assets/images/products/' . ltrim($file, '/');
}

/**
 * Prefer a pre-compressed copy in assets/images/products/optimized/ when present.
 */
function catalog_video_src(string $file): string
{
    $base = pathinfo($file, PATHINFO_FILENAME);
    $optimized = catalog_asset('optimized/' . $base . '.mp4');

    if (is_file(catalog_site_root() . '/' . $optimized)) {
        return $optimized;
    }

    return catalog_asset($file);
}

/**
 * @return list<array{type: string, path: string}>
 */
function catalog_media(?string $imageFile, ?string $videoFile = null): array
{
    $media = [];

    if ($imageFile !== null && $imageFile !== '') {
        $media[] = ['type' => 'image', 'path' => catalog_asset($imageFile)];
    }

    if ($videoFile !== null && $videoFile !== '') {
        $media[] = ['type' => 'video', 'path' => catalog_video_src($videoFile)];
    }

    return $media;
}

function catalog_video_mime(string $path): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return match ($ext) {
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
        default => 'video/mp4',
    };
}

function catalog_is_placeholder_image(string $path): bool
{
    return $path === CATALOG_PRODUCT_PLACEHOLDER || str_contains($path, 'drink-placeholder.svg');
}

function product_real_image(array $product): string
{
    $image = (string) ($product['image'] ?? '');

    if ($image === '' || catalog_is_placeholder_image($image)) {
        return '';
    }

    return $image;
}
function product_uses_video_preview(array $product): bool
{
    if (($product['preview'] ?? '') === 'video') {
        return true;
    }

    return product_real_image($product) === '' && !empty($product['video']);
}

function product_card_image(array $product): string
{
    $image = product_real_image($product);

    if ($image !== '') {
        return $image;
    }

    return CATALOG_PRODUCT_PLACEHOLDER;
}

function product_size_label(string $sizeKey): string
{
    return ((string) $sizeKey === '1') ? '1 Litre' : $sizeKey . ' Litres';
}

/**
 * @param array<string, mixed> $spec
 * @return array<string, mixed>
 */
function catalog_product(array $spec): array
{
    $imageFile = $spec['image'] ?? null;
    $videoFile = $spec['video'] ?? null;
    $media = catalog_media($imageFile, $videoFile);

    $product = $spec;
    unset($product['image'], $product['video']);

    if ($imageFile) {
        $product['image'] = catalog_asset($imageFile);
    }

    if ($videoFile) {
        $product['video'] = catalog_video_src($videoFile);
        if (empty($product['image'])) {
            $product['preview'] = 'video';
        }
    }

    if ($media !== []) {
        $product['media'] = $media;
    }

    return $product;
}
