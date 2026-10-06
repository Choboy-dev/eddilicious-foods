<?php
/**
 * Drinks available when booking catering (not sold on the shop menu).
 */

require_once __DIR__ . '/catalog-media.php';

/**
 * @return list<array{id: string, name: string, image: string, sizes: array<string, string>}>
 */
function get_catering_drinks(): array
{
    static $drinks = null;

    if ($drinks !== null) {
        return $drinks;
    }

    $items = [
        ['id' => 'chapman', 'name' => 'Chapman', 'video' => 'chapman.mp4'],
        ['id' => 'kiwi-mojito', 'name' => 'Kiwi Mojito', 'video' => 'kiwi-mojito.mp4'],
        ['id' => 'pina-colada', 'name' => 'Pina Colada', 'video' => 'pina-colada.mp4'],
        ['id' => 'screwdriver', 'name' => 'Screwdriver', 'video' => 'screwdriver.mp4'],
        ['id' => 'white-wine', 'name' => 'White Wine', 'video' => 'white-wine.mp4'],
    ];

    $drinks = [];

    foreach ($items as $item) {
        $video = catalog_video_src($item['video']);
        $drinks[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'image' => catalog_asset(pathinfo($item['video'], PATHINFO_FILENAME) . '.jpeg'),
            'video' => $video,
            'sizes' => [
                '1' => '1 litre',
                '2' => '2 litres',
            ],
        ];
    }

    return $drinks;
}

/**
 * @return array<string, string>
 */
function get_catering_drink_size_labels(): array
{
    return [
        '1' => '1 litre',
        '2' => '2 litres',
    ];
}
