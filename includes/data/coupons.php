<?php
/**
 * Edilicious Foods — Coupon definitions
 */

require_once dirname(__DIR__) . '/admin/CouponAdminRepository.php';

/**
 * @return array<string, array{code: string, type: string, value: float, message: string, once_per_customer?: bool, min_order_pence?: int}>
 */
function get_coupon_definitions(): array
{
    return CouponAdminRepository::activeDefinitions();
}

/**
 * Coupon data exposed to the browser (no secrets).
 *
 * @return array<string, array{code: string, type: string, value: float, message: string, oncePerCustomer: bool}>
 */
function get_coupons_for_client(): array
{
    $client = [];

    foreach (get_coupon_definitions() as $definition) {
        $code = strtoupper($definition['code']);
        $client[$code] = [
            'code' => $code,
            'type' => $definition['type'],
            'value' => (float) $definition['value'],
            'message' => $definition['message'],
            'oncePerCustomer' => !empty($definition['once_per_customer']),
        ];
    }

    return $client;
}

/**
 * Look up a coupon by code (server-side).
 */
function find_coupon(string $code): ?array
{
    $normalized = strtoupper(trim($code));
    $definitions = get_coupon_definitions();

    return $definitions[$normalized] ?? null;
}

/**
 * @return array<string, mixed>|null
 */
function get_active_promo_coupon(): ?array
{
    return CouponAdminRepository::getActivePromo();
}
