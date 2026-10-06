<?php

class CatalogAdminRepository
{
    public static function migrate(): void
    {
        auth_database()->exec(
            'CREATE TABLE IF NOT EXISTS catalog_custom_products (
                id TEXT PRIMARY KEY,
                category TEXT NOT NULL,
                type TEXT NOT NULL,
                name TEXT NOT NULL,
                image TEXT NOT NULL,
                prices_json TEXT,
                price REAL,
                compare_at REAL,
                discount_label TEXT,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );

        auth_database()->exec(
            'CREATE TABLE IF NOT EXISTS catalog_product_overrides (
                product_id TEXT PRIMARY KEY,
                overrides_json TEXT NOT NULL,
                is_hidden INTEGER NOT NULL DEFAULT 0,
                updated_at TEXT NOT NULL
            )'
        );

        self::ensureMediaColumn();
        self::ensureDescriptionColumn();

        require_once __DIR__ . '/MealMediaUpload.php';
        MealMediaUpload::ensureUploadDirectoryWritable();
    }

    private static function ensureMediaColumn(): void
    {
        $columns = auth_database()->query('PRAGMA table_info(catalog_custom_products)')->fetchAll() ?: [];
        $hasMedia = false;

        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'media_json') {
                $hasMedia = true;
                break;
            }
        }

        if (!$hasMedia) {
            auth_database()->exec('ALTER TABLE catalog_custom_products ADD COLUMN media_json TEXT');
        }
    }

    private static function ensureDescriptionColumn(): void
    {
        $columns = auth_database()->query('PRAGMA table_info(catalog_custom_products)')->fetchAll() ?: [];
        $hasDescription = false;

        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'description_json') {
                $hasDescription = true;
                break;
            }
        }

        if (!$hasDescription) {
            auth_database()->exec('ALTER TABLE catalog_custom_products ADD COLUMN description_json TEXT');
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{html: string}|null
     */
    private static function descriptionFromPayload(array $payload): ?array
    {
        require_once __DIR__ . '/DescriptionHtmlSanitizer.php';

        $html = trim((string) ($payload['description_html'] ?? ''));
        if ($html === '') {
            $legacy = [
                'intro' => trim((string) ($payload['description_intro'] ?? '')),
                'paragraphs' => [],
                'bullets' => [],
            ];

            $details = trim((string) ($payload['description_details'] ?? ''));
            if ($details !== '') {
                $legacy['paragraphs'] = array_values(array_filter(
                    array_map('trim', preg_split('/\R\s*\R/', $details) ?: []),
                    static fn(string $line): bool => $line !== ''
                ));
            }

            $bulletsRaw = trim((string) ($payload['description_bullets'] ?? ''));
            if ($bulletsRaw !== '') {
                $legacy['bullets'] = array_values(array_filter(
                    array_map('trim', preg_split('/\R/', $bulletsRaw) ?: []),
                    static fn(string $line): bool => $line !== ''
                ));
            }

            if ($legacy['intro'] === '' && $legacy['paragraphs'] === [] && $legacy['bullets'] === []) {
                return null;
            }

            $html = DescriptionHtmlSanitizer::fromLegacy($legacy);
        } else {
            $html = DescriptionHtmlSanitizer::sanitize($html);
        }

        if (!DescriptionHtmlSanitizer::hasContent($html)) {
            return null;
        }

        return ['html' => $html];
    }

    /**
     * @param array<string, mixed> $product
     * @return array{html: string}|null
     */
    private static function descriptionFromProduct(array $product): ?array
    {
        require_once __DIR__ . '/DescriptionHtmlSanitizer.php';

        if (!empty($product['description_long']) && is_array($product['description_long'])) {
            $normalized = DescriptionHtmlSanitizer::normalize($product['description_long']);
            if (DescriptionHtmlSanitizer::hasContent($normalized['html'])) {
                return $normalized;
            }
        }

        $decoded = json_decode((string) ($product['description_json'] ?? ''), true);
        if (is_array($decoded)) {
            $normalized = DescriptionHtmlSanitizer::normalize($decoded);
            if (DescriptionHtmlSanitizer::hasContent($normalized['html'])) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $staticCatalog
     * @return list<array<string, mixed>>
     */
    public static function mergeWithStatic(array $staticCatalog): array
    {
        $overrides = self::overridesByProductId();
        $merged = [];

        foreach ($staticCatalog as $product) {
            $id = (string) ($product['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $override = $overrides[$id] ?? null;
            if ($override && !empty($override['is_hidden'])) {
                continue;
            }

            if ($override && !empty($override['data'])) {
                $product = array_merge($product, $override['data']);
            }

            $merged[] = $product;
        }

        foreach (self::customProducts() as $custom) {
            $merged[] = $custom;
        }

        return $merged;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listForAdmin(): array
    {
        require_once dirname(__DIR__) . '/data/menu-catalog.php';

        $items = [];
        foreach (get_menu_catalog_static() as $product) {
            $id = (string) ($product['id'] ?? '');
            $override = self::getOverride($id);

            if ($override && !empty($override['is_hidden'])) {
                continue;
            }

            $items[] = self::formatAdminRow($product, 'built-in', $override);
        }

        foreach (self::customProductsForAdmin() as $product) {
            $items[] = self::formatAdminRow($product, 'custom', null);
        }

        usort($items, static fn(array $a, array $b): int => strcmp((string) $a['name'], (string) $b['name']));

        return $items;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function saveCustom(array $payload): array
    {
        require_once __DIR__ . '/MealMediaUpload.php';

        $existingId = trim((string) ($payload['existing_id'] ?? ($payload['id'] ?? '')));
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Meal name is required.');
        }

        $id = $existingId !== '' ? $existingId : self::slugify($name);
        if ($id === '') {
            throw new InvalidArgumentException('We could not make a shop link from that name.');
        }

        $id = self::ensureUniqueCustomId($id, $existingId !== '' ? $existingId : null);

        $media = MealMediaUpload::normalizeMediaList(is_array($payload['media'] ?? null) ? $payload['media'] : []);
        if ($media === []) {
            throw new InvalidArgumentException('Add at least one photo or video.');
        }

        $image = MealMediaUpload::primaryImagePath($media);
        $mediaJson = json_encode($media, JSON_UNESCAPED_UNICODE);

        $now = gmdate('c');
        $type = (string) ($payload['type'] ?? 'litre');
        $category = (string) ($payload['category'] ?? 'soups');

        $pricesJson = null;
        $price = null;
        $compareAt = null;
        $discountLabel = null;

        if ($type === 'platter') {
            $price = (float) ($payload['price'] ?? 0);
            if ($price <= 0) {
                throw new InvalidArgumentException('Tray price must be greater than zero.');
            }
            if (!empty($payload['compare_at'])) {
                $compareAt = (float) $payload['compare_at'];
            }
            if (!empty($payload['discount_label'])) {
                $discountLabel = trim((string) $payload['discount_label']);
            }
        } else {
            $prices = [
                '2' => (float) ($payload['price_2'] ?? 0),
                '4' => (float) ($payload['price_4'] ?? 0),
            ];
            if ($prices['2'] <= 0 || $prices['4'] <= 0) {
                throw new InvalidArgumentException('2 litre and 4 litre prices are required.');
            }
            $pricesJson = json_encode($prices, JSON_UNESCAPED_UNICODE);
        }

        $existingRow = self::findCustomRow($id);
        $createdAt = $existingRow['created_at'] ?? $now;
        $description = self::descriptionFromPayload($payload);
        $descriptionJson = $description !== null
            ? json_encode($description, JSON_UNESCAPED_UNICODE)
            : null;

        $stmt = auth_database()->prepare(
            'INSERT INTO catalog_custom_products (
                id, category, type, name, image, media_json, description_json, prices_json, price, compare_at, discount_label,
                is_active, created_at, updated_at
             ) VALUES (
                :id, :category, :type, :name, :image, :media_json, :description_json, :prices_json, :price, :compare_at, :discount_label,
                :is_active, :created_at, :updated_at
             )
             ON CONFLICT(id) DO UPDATE SET
                category = excluded.category,
                type = excluded.type,
                name = excluded.name,
                image = excluded.image,
                media_json = excluded.media_json,
                description_json = excluded.description_json,
                prices_json = excluded.prices_json,
                price = excluded.price,
                compare_at = excluded.compare_at,
                discount_label = excluded.discount_label,
                is_active = excluded.is_active,
                updated_at = excluded.updated_at'
        );

        $stmt->execute([
            'id' => $id,
            'category' => $category,
            'type' => $type,
            'name' => $name,
            'image' => $image,
            'media_json' => $mediaJson,
            'description_json' => $descriptionJson,
            'prices_json' => $pricesJson,
            'price' => $price,
            'compare_at' => $compareAt,
            'discount_label' => $discountLabel,
            'is_active' => !empty($payload['is_active']) ? 1 : 0,
            'created_at' => $createdAt,
            'updated_at' => $now,
        ]);

        return self::rowToProduct(self::findCustomRow($id) ?? []);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function saveBuiltInOverride(string $productId, array $payload): void
    {
        require_once __DIR__ . '/MealMediaUpload.php';

        $productId = trim($productId);
        if ($productId === '') {
            throw new InvalidArgumentException('Product id is required.');
        }

        $data = [];
        foreach (['name', 'category', 'type', 'discount_label'] as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== '') {
                $data[$field] = $payload[$field];
            }
        }

        $media = MealMediaUpload::normalizeMediaList(is_array($payload['media'] ?? null) ? $payload['media'] : []);
        if ($media !== []) {
            $data['media'] = $media;
            $data['image'] = MealMediaUpload::primaryImagePath($media, (string) ($payload['image'] ?? ''));
        } elseif (array_key_exists('image', $payload) && $payload['image'] !== '') {
            $data['image'] = $payload['image'];
        }

        if (isset($payload['price_2'], $payload['price_4'])) {
            $data['prices'] = [
                '2' => (float) $payload['price_2'],
                '4' => (float) $payload['price_4'],
            ];
        }

        if (isset($payload['price'])) {
            $data['price'] = (float) $payload['price'];
        }

        if (isset($payload['compare_at']) && $payload['compare_at'] !== '') {
            $data['compare_at'] = (float) $payload['compare_at'];
        }

        $description = self::descriptionFromPayload($payload);
        if ($description !== null) {
            $data['description_long'] = $description;
        }

        $isHidden = !empty($payload['is_hidden']) ? 1 : 0;
        $now = gmdate('c');

        $stmt = auth_database()->prepare(
            'INSERT INTO catalog_product_overrides (product_id, overrides_json, is_hidden, updated_at)
             VALUES (:product_id, :overrides_json, :is_hidden, :updated_at)
             ON CONFLICT(product_id) DO UPDATE SET
                overrides_json = excluded.overrides_json,
                is_hidden = excluded.is_hidden,
                updated_at = excluded.updated_at'
        );

        $stmt->execute([
            'product_id' => $productId,
            'overrides_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'is_hidden' => $isHidden,
            'updated_at' => $now,
        ]);
    }

    public static function deleteCustom(string $id): void
    {
        require_once __DIR__ . '/MealMediaUpload.php';

        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Meal id is required.');
        }

        $row = self::findCustomRow($id);
        if ($row === null) {
            throw new InvalidArgumentException('That meal was not found.');
        }

        $media = json_decode((string) ($row['media_json'] ?? '[]'), true);
        if (is_array($media)) {
            MealMediaUpload::deleteStoredMedia($media);
        }

        $stmt = auth_database()->prepare('DELETE FROM catalog_custom_products WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function hideBuiltIn(string $productId): void
    {
        require_once __DIR__ . '/MealMediaUpload.php';

        $productId = trim($productId);
        if ($productId === '') {
            throw new InvalidArgumentException('Meal id is required.');
        }

        self::assertBuiltInExists($productId);

        $override = self::getOverride($productId);
        $data = $override['data'] ?? [];

        if (!empty($data['media']) && is_array($data['media'])) {
            MealMediaUpload::deleteStoredMedia($data['media']);
            unset($data['media'], $data['image']);
        }

        $now = gmdate('c');

        $stmt = auth_database()->prepare(
            'INSERT INTO catalog_product_overrides (product_id, overrides_json, is_hidden, updated_at)
             VALUES (:product_id, :overrides_json, 1, :updated_at)
             ON CONFLICT(product_id) DO UPDATE SET
                overrides_json = excluded.overrides_json,
                is_hidden = 1,
                updated_at = excluded.updated_at'
        );

        $stmt->execute([
            'product_id' => $productId,
            'overrides_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'updated_at' => $now,
        ]);
    }

    public static function deleteProduct(string $id, string $source): void
    {
        $source = trim($source);
        if ($source === 'custom') {
            self::deleteCustom($id);

            return;
        }

        if ($source === 'built-in') {
            self::hideBuiltIn($id);

            return;
        }

        throw new InvalidArgumentException('Unknown meal type.');
    }

    /**
     * @param list<array{id?: string, source?: string}> $items
     * @return array{deleted: int, failed: int}
     */
    public static function deleteProducts(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('Pick at least one meal to delete.');
        }

        $deleted = 0;
        $failed = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                $failed++;
                continue;
            }

            try {
                self::deleteProduct((string) ($item['id'] ?? ''), (string) ($item['source'] ?? 'custom'));
                $deleted++;
            } catch (InvalidArgumentException) {
                $failed++;
            }
        }

        if ($deleted === 0) {
            throw new InvalidArgumentException('We could not delete those meals. Try again.');
        }

        return ['deleted' => $deleted, 'failed' => $failed];
    }

    private static function assertBuiltInExists(string $productId): void
    {
        require_once dirname(__DIR__) . '/data/menu-catalog.php';

        foreach (get_menu_catalog_static() as $product) {
            if ((string) ($product['id'] ?? '') === $productId) {
                return;
            }
        }

        throw new InvalidArgumentException('That meal was not found.');
    }

    /**
     * @return array<string, array{is_hidden: bool, data: array<string, mixed>}>
     */
    private static function overridesByProductId(): array
    {
        $stmt = auth_database()->query('SELECT product_id, overrides_json, is_hidden FROM catalog_product_overrides');
        $rows = $stmt->fetchAll() ?: [];
        $map = [];

        foreach ($rows as $row) {
            $data = json_decode((string) ($row['overrides_json'] ?? '{}'), true);
            $map[(string) $row['product_id']] = [
                'is_hidden' => (bool) ($row['is_hidden'] ?? false),
                'data' => is_array($data) ? $data : [],
            ];
        }

        return $map;
    }

    /**
     * @return array{is_hidden: bool, data: array<string, mixed>}|null
     */
    private static function getOverride(string $productId): ?array
    {
        $stmt = auth_database()->prepare(
            'SELECT overrides_json, is_hidden FROM catalog_product_overrides WHERE product_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $data = json_decode((string) ($row['overrides_json'] ?? '{}'), true);

        return [
            'is_hidden' => (bool) ($row['is_hidden'] ?? false),
            'data' => is_array($data) ? $data : [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function customProductsForAdmin(): array
    {
        $stmt = auth_database()->query(
            'SELECT * FROM catalog_custom_products ORDER BY name ASC'
        );
        $rows = $stmt->fetchAll() ?: [];
        $products = [];

        foreach ($rows as $row) {
            $product = self::rowToProduct($row);
            if ($product !== null) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function customProducts(): array
    {
        $stmt = auth_database()->query(
            'SELECT * FROM catalog_custom_products WHERE is_active = 1 ORDER BY name ASC'
        );
        $rows = $stmt->fetchAll() ?: [];
        $products = [];

        foreach ($rows as $row) {
            $product = self::rowToProduct($row);
            if ($product !== null) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function rowToProduct(array $row): ?array
    {
        require_once __DIR__ . '/MealMediaUpload.php';

        if (empty($row['id'])) {
            return null;
        }

        $product = [
            'id' => (string) $row['id'],
            'category' => (string) $row['category'],
            'type' => (string) $row['type'],
            'name' => (string) $row['name'],
            'image' => (string) $row['image'],
            'is_active' => !empty($row['is_active']),
        ];

        $media = json_decode((string) ($row['media_json'] ?? '[]'), true);
        if (is_array($media) && $media !== []) {
            $product['media'] = MealMediaUpload::normalizeMediaList($media);
        }

        if (($product['type'] ?? '') === 'platter') {
            $product['price'] = (float) ($row['price'] ?? 0);
            if (!empty($row['compare_at'])) {
                $product['compare_at'] = (float) $row['compare_at'];
            }
            if (!empty($row['discount_label'])) {
                $product['discount_label'] = (string) $row['discount_label'];
            }
        } else {
            $prices = json_decode((string) ($row['prices_json'] ?? '{}'), true);
            $product['prices'] = is_array($prices) ? $prices : [];
        }

        $description = self::descriptionFromProduct($row);
        if ($description !== null) {
            $product['description_long'] = $description;
        }

        return $product;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findCustomById(string $id): ?array
    {
        $row = self::findCustomRow($id);

        return $row ? self::rowToProduct($row) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findCustomRow(string $id): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM catalog_custom_products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $product
     * @param array{is_hidden: bool, data: array<string, mixed>}|null $override
     * @return array<string, mixed>
     */
    private static function formatAdminRow(array $product, string $source, ?array $override): array
    {
        if ($override && !empty($override['data'])) {
            $product = array_merge($product, $override['data']);
        }

        $row = [
            'id' => (string) ($product['id'] ?? ''),
            'source' => $source,
            'category' => (string) ($product['category'] ?? ''),
            'type' => (string) ($product['type'] ?? 'litre'),
            'name' => (string) ($product['name'] ?? ''),
            'image' => (string) ($product['image'] ?? ''),
            'is_hidden' => (bool) ($override['is_hidden'] ?? false),
            'is_active' => $source === 'custom'
                ? !empty($product['is_active'])
                : !($override['is_hidden'] ?? false),
        ];

        if (($product['type'] ?? '') === 'platter') {
            $row['price'] = (float) ($product['price'] ?? 0);
            $row['compare_at'] = isset($product['compare_at']) ? (float) $product['compare_at'] : null;
            $row['discount_label'] = $product['discount_label'] ?? null;
        } else {
            $prices = $product['prices'] ?? [];
            $row['price_2'] = (float) ($prices['2'] ?? 0);
            $row['price_4'] = (float) ($prices['4'] ?? 0);
        }

        if (!empty($product['media']) && is_array($product['media'])) {
            $row['media'] = $product['media'];
        } elseif (!empty($product['image'])) {
            $row['media'] = [
                ['path' => (string) $product['image'], 'type' => 'image'],
            ];
        } else {
            $row['media'] = [];
        }

        require_once __DIR__ . '/DescriptionHtmlSanitizer.php';

        $description = self::descriptionFromProduct($product);
        $row['description_long'] = DescriptionHtmlSanitizer::normalize(
            $description ?? ['html' => '']
        );

        return $row;
    }

    private static function ensureUniqueCustomId(string $id, ?string $ignoreId = null): string
    {
        $candidate = $id;
        $suffix = 2;

        while (self::idTaken($candidate, $ignoreId)) {
            $candidate = $id . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private static function idTaken(string $id, ?string $ignoreId = null): bool
    {
        if ($ignoreId !== null && $id === $ignoreId) {
            return false;
        }

        if (self::customIdExists($id)) {
            return true;
        }

        require_once dirname(__DIR__) . '/data/menu-catalog.php';
        foreach (get_menu_catalog_static() as $product) {
            if (($product['id'] ?? '') === $id) {
                return true;
            }
        }

        return false;
    }

    private static function customIdExists(string $id): bool
    {
        $stmt = auth_database()->prepare('SELECT id FROM catalog_custom_products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return (bool) $row;
    }

    public static function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }
}
