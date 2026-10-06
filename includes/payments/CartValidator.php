<?php

require_once dirname(__DIR__) . '/data/menu-catalog.php';
require_once dirname(__DIR__) . '/data/coupons.php';

class CartValidator
{
    /**
     * @param list<array<string, mixed>> $items
     * @return array{ok: bool, error?: string, lines?: list<array<string, mixed>>, subtotal_pence?: int, discount_pence?: int, delivery_pence?: int, total_pence?: int, coupon_code?: ?string}
     */
    public static function validate(array $items, ?string $couponCode, string $fulfillment): array
    {
        if ($items === []) {
            return ['ok' => false, 'error' => 'Your basket is empty.'];
        }

        if (!in_array($fulfillment, ['delivery', 'collection'], true)) {
            return ['ok' => false, 'error' => 'Choose delivery or collection.'];
        }

        $lines = [];
        $subtotalPence = 0;

        foreach ($items as $item) {
            $line = self::validateLine($item);
            if (!$line['ok']) {
                return $line;
            }

            $lines[] = $line['line'];
            $subtotalPence += $line['line']['line_total_pence'];
        }

        $discountPence = self::calculateDiscountPence($subtotalPence, $couponCode);
        $deliveryPence = 0;
        $totalPence = max(0, $subtotalPence - $discountPence + $deliveryPence);

        if ($totalPence < 50) {
            return ['ok' => false, 'error' => 'Order total is too low to pay online.'];
        }

        return [
            'ok' => true,
            'lines' => $lines,
            'subtotal_pence' => $subtotalPence,
            'discount_pence' => $discountPence,
            'delivery_pence' => $deliveryPence,
            'total_pence' => $totalPence,
            'coupon_code' => $discountPence > 0 ? strtoupper(trim((string) $couponCode)) : null,
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @return array{ok: bool, error?: string, line?: array<string, mixed>}
     */
    private static function validateLine(array $item): array
    {
        $productId = (string) ($item['id'] ?? '');
        $product = find_product_by_id($productId);

        if (!$product) {
            return ['ok' => false, 'error' => 'Something in your basket is no longer on our menu.'];
        }

        $units = max(1, (int) ($item['units'] ?? 1));
        $clientPrice = round((float) ($item['price'] ?? 0), 2);
        $quantityLabel = trim((string) ($item['quantity'] ?? ''));

        $unitPrice = self::resolveUnitPrice($product, $quantityLabel);
        if ($unitPrice === null) {
            return ['ok' => false, 'error' => 'We could not price an item in your basket.'];
        }

        if (abs($clientPrice - $unitPrice) > 0.01) {
            return ['ok' => false, 'error' => 'Prices changed. Refresh your basket and try again.'];
        }

        $lineTotalPence = (int) round($unitPrice * $units * 100);
        $name = (string) ($product['name'] ?? $item['name'] ?? 'Item');
        $description = $units > 1
            ? $units . ' × ' . $quantityLabel
            : $quantityLabel;

        return [
            'ok' => true,
            'line' => [
                'product_id' => $productId,
                'name' => $name,
                'description' => $description,
                'quantity_label' => $quantityLabel,
                'units' => $units,
                'unit_price' => $unitPrice,
                'line_total_pence' => $lineTotalPence,
                'stripe_quantity' => $units,
                'stripe_unit_amount_pence' => (int) round($unitPrice * 100),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $product
     */
    private static function resolveUnitPrice(array $product, string $quantityLabel): ?float
    {
        if (($product['type'] ?? '') === 'platter') {
            return round((float) ($product['price'] ?? 0), 2);
        }

        $sizeKey = null;
        if (preg_match('/(\d+)/', $quantityLabel, $matches)) {
            $sizeKey = $matches[1];
        }

        $prices = $product['prices'] ?? [];
        if ($sizeKey !== null && isset($prices[$sizeKey])) {
            return round((float) $prices[$sizeKey], 2);
        }

        return null;
    }

    private static function calculateDiscountPence(int $subtotalPence, ?string $couponCode): int
    {
        $code = strtoupper(trim((string) $couponCode));
        if ($code === '' || $subtotalPence <= 0) {
            return 0;
        }

        $coupon = find_coupon($code);
        if (!$coupon) {
            return 0;
        }

        if (($coupon['type'] ?? '') === 'percent') {
            $percent = (float) ($coupon['value'] ?? 0);

            return (int) round($subtotalPence * ($percent / 100));
        }

        return 0;
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @return list<array<string, mixed>>
     */
    public static function buildStripeLineItems(array $lines, int $discountPence, int $subtotalPence): array
    {
        $ratio = ($discountPence > 0 && $subtotalPence > 0)
            ? $discountPence / $subtotalPence
            : 0.0;

        $stripeLines = [];

        foreach ($lines as $index => $line) {
            $unitPence = $line['stripe_unit_amount_pence'];
            if ($ratio > 0) {
                $unitPence = (int) max(1, round($unitPence * (1 - $ratio)));
            }

            $name = (string) $line['name'];
            if ($line['description'] !== $line['quantity_label']) {
                $name .= ' (' . $line['description'] . ')';
            }

            $stripeLines[] = [
                'price_data' => [
                    'currency' => 'gbp',
                    'unit_amount' => $unitPence,
                    'product_data' => [
                        'name' => $name,
                        'metadata' => [
                            'product_id' => (string) $line['product_id'],
                        ],
                    ],
                ],
                'quantity' => (int) $line['stripe_quantity'],
            ];
        }

        return $stripeLines;
    }
}
