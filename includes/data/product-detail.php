<?php
/**
 * Product detail content — gallery and long description from catalog / admin uploads.
 */

const PRODUCT_IMAGE_FALLBACK = 'assets/images/products/egusi-soup.jpeg';

/**
 * @return list<array{type: string, src: string, poster?: string, alt: string}>
 */
function product_gallery_from_media(array $product): array
{
    $name = $product['name'] ?? 'This dish';
    $gallery = [];
    $firstImage = product_real_image($product);

    foreach ($product['media'] ?? [] as $item) {
        if (!is_array($item) || empty($item['path'])) {
            continue;
        }

        $path = (string) $item['path'];
        $type = (string) ($item['type'] ?? 'image');

        if ($type === 'video') {
            $entry = [
                'type' => 'video',
                'src' => $path,
                'alt' => $name . ' — video',
            ];

            if ($firstImage !== '') {
                $entry['poster'] = $firstImage;
            }

            $gallery[] = $entry;
            continue;
        }

        if ($firstImage === '') {
            $firstImage = $path;
        }

        $gallery[] = [
            'type' => 'image',
            'src' => $path,
            'alt' => $name,
        ];

        if (count($gallery) >= 5) {
            break;
        }
    }

    return $gallery;
}

/**
 * Gallery from owner uploads, or the product's main image only.
 *
 * @return list<array{type: string, src: string, poster?: string, alt: string}>
 */
function get_product_gallery(array $product): array
{
    if (!empty($product['media']) && is_array($product['media'])) {
        $gallery = product_gallery_from_media($product);
        if ($gallery) {
            return $gallery;
        }
    }

    $name = $product['name'] ?? 'This dish';
    $main = product_real_image($product);
    if ($main === '') {
        $main = PRODUCT_IMAGE_FALLBACK;
    }

    return [
        [
            'type' => 'image',
            'src' => $main,
            'alt' => $name,
        ],
    ];
}

/**
 * Empty description shape for products with no custom copy.
 *
 * @return array{html: string}
 */
function empty_product_description(): array
{
    return ['html' => ''];
}

/**
 * Merge gallery and description into a catalog product.
 */
function enrich_product_detail(array $product): array
{
    $product['gallery'] = get_product_gallery($product);

    require_once __DIR__ . '/../admin/DescriptionHtmlSanitizer.php';

    if (empty($product['description_long']) || !is_array($product['description_long'])) {
        $product['description_long'] = empty_product_description();
    } else {
        $product['description_long'] = DescriptionHtmlSanitizer::normalize($product['description_long']);
    }

    require_once __DIR__ . '/../reviews/ProductReviewRepository.php';

    $productId = (string) ($product['id'] ?? '');
    $summary = ProductReviewRepository::summaryForProduct($productId);
    $product['reviews'] = ProductReviewRepository::listForProduct($productId);
    $product['rating_average'] = $summary['rating_average'];
    $product['review_count'] = $summary['review_count'];
    $product['rating_breakdown'] = $summary['breakdown'];

    return $product;
}
