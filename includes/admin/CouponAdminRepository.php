<?php

class CouponAdminRepository
{
    public static function migrate(): void
    {
        auth_database()->exec(
            'CREATE TABLE IF NOT EXISTS admin_coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE COLLATE NOCASE,
                type TEXT NOT NULL,
                value REAL NOT NULL,
                message TEXT NOT NULL,
                promo_hint TEXT,
                once_per_customer INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                expires_at TEXT,
                min_order_pence INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );

        self::ensurePromoHintColumn();
        self::ensureDefaultCoupon();
    }

    private static function ensurePromoHintColumn(): void
    {
        $columns = auth_database()->query('PRAGMA table_info(admin_coupons)')->fetchAll() ?: [];
        $hasHint = false;

        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'promo_hint') {
                $hasHint = true;
                break;
            }
        }

        if (!$hasHint) {
            auth_database()->exec('ALTER TABLE admin_coupons ADD COLUMN promo_hint TEXT');
        }
    }

    private static function ensureDefaultCoupon(): void
    {
        $count = (int) auth_database()->query('SELECT COUNT(*) FROM admin_coupons')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $now = gmdate('c');
        $stmt = auth_database()->prepare(
            'INSERT INTO admin_coupons (
                code, type, value, message, promo_hint, once_per_customer, is_active,
                expires_at, min_order_pence, created_at, updated_at
             ) VALUES (
                :code, :type, :value, :message, :promo_hint, :once_per_customer, :is_active,
                :expires_at, :min_order_pence, :created_at, :updated_at
             )'
        );

        $stmt->execute([
            'code' => 'EDDFIRST15',
            'type' => 'percent',
            'value' => 15.0,
            'message' => 'We took 15% off your order.',
            'promo_hint' => '15% off when you order from us the first time. Type this code when you pay.',
            'once_per_customer' => 1,
            'is_active' => 1,
            'expires_at' => null,
            'min_order_pence' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @return array<string, array{code: string, type: string, value: float, message: string, once_per_customer?: bool, min_order_pence?: int}>
     */
    public static function activeDefinitions(): array
    {
        $coupon = self::getActivePromo();
        if ($coupon === null) {
            return [];
        }

        $code = strtoupper((string) $coupon['code']);

        return [
            $code => [
                'code' => $code,
                'type' => (string) $coupon['type'],
                'value' => (float) $coupon['value'],
                'message' => (string) $coupon['message'],
                'once_per_customer' => (bool) ($coupon['once_per_customer'] ?? false),
                'min_order_pence' => (int) ($coupon['min_order_pence'] ?? 0),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getActivePromo(): ?array
    {
        $stmt = auth_database()->query(
            'SELECT * FROM admin_coupons WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 1'
        );
        $row = $stmt->fetch();

        if (!$row || !self::isRowValid($row)) {
            return null;
        }

        return self::formatRow($row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listAll(): array
    {
        $stmt = auth_database()->query(
            'SELECT * FROM admin_coupons ORDER BY is_active DESC, updated_at DESC'
        );
        $rows = $stmt->fetchAll() ?: [];

        return array_map([self::class, 'formatRow'], $rows);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function save(array $payload): array
    {
        $code = strtoupper(trim((string) ($payload['code'] ?? '')));
        if ($code === '') {
            throw new InvalidArgumentException('Coupon code is required.');
        }

        $type = (string) ($payload['type'] ?? 'percent');
        if (!in_array($type, ['percent', 'fixed'], true)) {
            throw new InvalidArgumentException('Coupon type must be percent or fixed.');
        }

        $value = (float) ($payload['value'] ?? 0);
        if ($value <= 0) {
            throw new InvalidArgumentException('Coupon value must be greater than zero.');
        }

        if ($type === 'percent' && $value > 100) {
            throw new InvalidArgumentException('Percent discount cannot be over 100.');
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            throw new InvalidArgumentException('Success message is required.');
        }

        $promoHint = trim((string) ($payload['promo_hint'] ?? ''));
        if ($promoHint === '') {
            $promoHint = self::defaultPromoHint($type, $value);
        }

        $now = gmdate('c');
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        $expiresAt = trim((string) ($payload['expires_at'] ?? ''));
        $expiresAt = $expiresAt !== '' ? $expiresAt : null;
        $makeLive = !array_key_exists('is_active', $payload) || !empty($payload['is_active']);

        if ($id > 0) {
            $stmt = auth_database()->prepare(
                'UPDATE admin_coupons SET
                    code = :code,
                    type = :type,
                    value = :value,
                    message = :message,
                    promo_hint = :promo_hint,
                    once_per_customer = :once_per_customer,
                    is_active = :is_active,
                    expires_at = :expires_at,
                    min_order_pence = :min_order_pence,
                    updated_at = :updated_at
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $id,
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'message' => $message,
                'promo_hint' => $promoHint,
                'once_per_customer' => !empty($payload['once_per_customer']) ? 1 : 0,
                'is_active' => $makeLive ? 1 : 0,
                'expires_at' => $expiresAt,
                'min_order_pence' => (int) ($payload['min_order_pence'] ?? 0),
                'updated_at' => $now,
            ]);
        } else {
            $stmt = auth_database()->prepare(
                'INSERT INTO admin_coupons (
                    code, type, value, message, promo_hint, once_per_customer, is_active,
                    expires_at, min_order_pence, created_at, updated_at
                 ) VALUES (
                    :code, :type, :value, :message, :promo_hint, :once_per_customer, :is_active,
                    :expires_at, :min_order_pence, :created_at, :updated_at
                 )'
            );
            $stmt->execute([
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'message' => $message,
                'promo_hint' => $promoHint,
                'once_per_customer' => !empty($payload['once_per_customer']) ? 1 : 0,
                'is_active' => $makeLive ? 1 : 0,
                'expires_at' => $expiresAt,
                'min_order_pence' => (int) ($payload['min_order_pence'] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $id = (int) auth_database()->lastInsertId();
        }

        if ($makeLive) {
            self::pauseAllExcept($id);
        }

        $row = self::findById($id);

        return $row ? self::formatRow($row) : [];
    }

    public static function delete(int $id): void
    {
        $stmt = auth_database()->prepare('DELETE FROM admin_coupons WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function defaultPromoHint(string $type, float $value): string
    {
        if ($type === 'fixed') {
            return '£' . number_format($value, 2) . ' off when you order from us. Type this code when you pay.';
        }

        $percent = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return $percent . '% off when you order from us the first time. Type this code when you pay.';
    }

    private static function pauseAllExcept(int $keepId): void
    {
        $stmt = auth_database()->prepare(
            'UPDATE admin_coupons SET is_active = 0, updated_at = :updated_at WHERE id != :id AND is_active = 1'
        );
        $stmt->execute([
            'id' => $keepId,
            'updated_at' => gmdate('c'),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function isRowValid(array $row): bool
    {
        if (empty($row['expires_at'])) {
            return true;
        }

        $expires = strtotime((string) $row['expires_at']);

        return $expires === false || $expires >= time();
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findById(int $id): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM admin_coupons WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function formatRow(array $row): array
    {
        $type = (string) ($row['type'] ?? 'percent');
        $value = (float) ($row['value'] ?? 0);
        $promoHint = trim((string) ($row['promo_hint'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'code' => strtoupper((string) ($row['code'] ?? '')),
            'type' => $type,
            'value' => $value,
            'message' => (string) ($row['message'] ?? ''),
            'promo_hint' => $promoHint !== '' ? $promoHint : self::defaultPromoHint($type, $value),
            'once_per_customer' => (bool) ($row['once_per_customer'] ?? false),
            'is_active' => (bool) ($row['is_active'] ?? false),
            'expires_at' => $row['expires_at'] ?? null,
            'min_order_pence' => (int) ($row['min_order_pence'] ?? 0),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }
}
