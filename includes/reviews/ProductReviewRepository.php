<?php
/**
 * Verified buyer reviews — one review per user per product.
 */

final class ProductReviewRepository
{
    public static function migrate(): void
    {
        $pdo = auth_database();

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS product_reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id TEXT NOT NULL,
                user_id INTEGER NOT NULL,
                order_id INTEGER NOT NULL,
                rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
                review_text TEXT NOT NULL,
                display_name TEXT NOT NULL,
                created_at TEXT NOT NULL,
                UNIQUE (user_id, product_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )'
        );

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_product_reviews_product ON product_reviews(product_id, created_at DESC)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_product_reviews_user ON product_reviews(user_id)');
    }

    /**
     * @return array{rating_average: float, review_count: int, breakdown: array<int, int>}
     */
    public static function summaryForProduct(string $productId): array
    {
        self::migrate();

        $productId = trim($productId);
        if ($productId === '') {
            return self::emptySummary();
        }

        $pdo = auth_database();
        $stmt = $pdo->prepare(
            'SELECT rating, COUNT(*) AS total
             FROM product_reviews
             WHERE product_id = :product_id
             GROUP BY rating'
        );
        $stmt->execute([':product_id' => $productId]);

        $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $count = 0;
        $sum = 0;

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $rating = (int) ($row['rating'] ?? 0);
            $total = (int) ($row['total'] ?? 0);
            if ($rating >= 1 && $rating <= 5) {
                $breakdown[$rating] = $total;
                $count += $total;
                $sum += $rating * $total;
            }
        }

        return [
            'rating_average' => $count > 0 ? round($sum / $count, 1) : 0.0,
            'review_count' => $count,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listForProduct(string $productId, int $limit = 50): array
    {
        self::migrate();

        $productId = trim($productId);
        if ($productId === '') {
            return [];
        }

        $limit = max(1, min(100, $limit));
        $pdo = auth_database();
        $stmt = $pdo->prepare(
            'SELECT id, product_id, user_id, order_id, rating, review_text, display_name, created_at
             FROM product_reviews
             WHERE product_id = :product_id
             ORDER BY datetime(created_at) DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':product_id', $productId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll() ?: [];

        return array_map([self::class, 'formatReviewRow'], $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findForUserAndProduct(int $userId, string $productId): ?array
    {
        self::migrate();

        $productId = trim($productId);
        if ($userId <= 0 || $productId === '') {
            return null;
        }

        $pdo = auth_database();
        $stmt = $pdo->prepare(
            'SELECT id, product_id, user_id, order_id, rating, review_text, display_name, created_at
             FROM product_reviews
             WHERE user_id = :user_id AND product_id = :product_id
             LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);
        $row = $stmt->fetch();

        return $row === false ? null : self::formatReviewRow($row);
    }

    /**
     * @return array{can_review: bool, reason: string, order_id?: int}
     */
    public static function eligibilityForUser(array $user, string $productId): array
    {
        self::migrate();

        $productId = trim($productId);
        $userId = (int) ($user['id'] ?? 0);
        $email = auth_sanitize_email((string) ($user['email'] ?? ''));

        if ($userId <= 0 || $email === '' || $productId === '') {
            return ['can_review' => false, 'reason' => 'sign_in'];
        }

        if (self::findForUserAndProduct($userId, $productId) !== null) {
            return ['can_review' => false, 'reason' => 'already_reviewed'];
        }

        $orderId = self::findEligibleOrderId($email, $productId);
        if ($orderId === null) {
            return ['can_review' => false, 'reason' => 'not_purchased'];
        }

        return [
            'can_review' => true,
            'reason' => 'eligible',
            'order_id' => $orderId,
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array{ok: bool, error?: string, review?: array<string, mixed>}
     */
    public static function submit(array $user, string $productId, int $rating, string $text): array
    {
        self::migrate();

        $productId = trim($productId);
        $userId = (int) ($user['id'] ?? 0);
        $text = trim($text);

        if ($userId <= 0) {
            return ['ok' => false, 'error' => 'Sign in to leave a review.'];
        }

        if ($productId === '') {
            return ['ok' => false, 'error' => 'This meal was not found.'];
        }

        if ($rating < 1 || $rating > 5) {
            return ['ok' => false, 'error' => 'Pick a star rating from 1 to 5.'];
        }

        if ($text === '' || mb_strlen($text) < 12) {
            return ['ok' => false, 'error' => 'Write at least 12 characters about your meal.'];
        }

        if (mb_strlen($text) > 1200) {
            return ['ok' => false, 'error' => 'Keep your review under 1200 characters.'];
        }

        $eligibility = self::eligibilityForUser($user, $productId);
        if (!$eligibility['can_review']) {
            return match ($eligibility['reason']) {
                'already_reviewed' => ['ok' => false, 'error' => 'You already reviewed this meal.'],
                'not_purchased' => ['ok' => false, 'error' => 'Only customers who bought this meal can review it.'],
                default => ['ok' => false, 'error' => 'Sign in to leave a review.'],
            };
        }

        $orderId = (int) ($eligibility['order_id'] ?? 0);
        if ($orderId <= 0) {
            return ['ok' => false, 'error' => 'We could not find your order for this meal.'];
        }

        $displayName = self::displayNameForUser($user);
        $now = gmdate('c');

        try {
            $stmt = auth_database()->prepare(
                'INSERT INTO product_reviews (
                    product_id, user_id, order_id, rating, review_text, display_name, created_at
                 ) VALUES (
                    :product_id, :user_id, :order_id, :rating, :review_text, :display_name, :created_at
                 )'
            );
            $stmt->execute([
                ':product_id' => substr($productId, 0, 120),
                ':user_id' => $userId,
                ':order_id' => $orderId,
                ':rating' => $rating,
                ':review_text' => $text,
                ':display_name' => substr($displayName, 0, 80),
                ':created_at' => $now,
            ]);
        } catch (PDOException $exception) {
            if (str_contains($exception->getMessage(), 'UNIQUE')) {
                return ['ok' => false, 'error' => 'You already reviewed this meal.'];
            }

            throw $exception;
        }

        $review = self::findForUserAndProduct($userId, $productId);

        return [
            'ok' => true,
            'review' => $review,
            'summary' => self::summaryForProduct($productId),
        ];
    }

    private static function findEligibleOrderId(string $email, string $productId): ?int
    {
        $email = auth_sanitize_email($email);
        $productId = trim($productId);

        if ($email === '' || $productId === '') {
            return null;
        }

        $stmt = auth_database()->prepare(
            "SELECT id, cart_json
             FROM orders
             WHERE email = :email AND status = 'paid'
             ORDER BY datetime(COALESCE(paid_at, created_at)) DESC"
        );
        $stmt->execute([':email' => $email]);

        foreach ($stmt->fetchAll() ?: [] as $row) {
            if (self::orderContainsProduct($row, $productId)) {
                return (int) ($row['id'] ?? 0);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $order
     */
    private static function orderContainsProduct(array $order, string $productId): bool
    {
        $cart = json_decode((string) ($order['cart_json'] ?? '[]'), true);
        if (!is_array($cart)) {
            return false;
        }

        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ((string) ($item['id'] ?? '') === $productId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $user
     */
    private static function displayNameForUser(array $user): string
    {
        $name = trim((string) ($user['name'] ?? ''));
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name) ?: [];
            $first = trim((string) ($parts[0] ?? $name));

            return $first !== '' ? $first : 'Customer';
        }

        $email = (string) ($user['email'] ?? '');
        $local = explode('@', $email)[0] ?? 'Customer';
        $local = trim(str_replace(['.', '_', '-'], ' ', $local));
        $bits = preg_split('/\s+/', $local) ?: [];
        $first = trim((string) ($bits[0] ?? ''));

        if ($first === '') {
            return 'Customer';
        }

        return ucfirst(strtolower($first));
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function formatReviewRow(array $row): array
    {
        $createdAt = (string) ($row['created_at'] ?? '');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'product_id' => (string) ($row['product_id'] ?? ''),
            'rating' => (int) ($row['rating'] ?? 0),
            'text' => (string) ($row['review_text'] ?? ''),
            'name' => (string) ($row['display_name'] ?? 'Customer'),
            'date' => self::formatReviewDate($createdAt),
            'verified' => true,
        ];
    }

    private static function formatReviewDate(string $iso): string
    {
        if ($iso === '') {
            return '';
        }

        try {
            $date = new DateTimeImmutable($iso);
            $date = $date->setTimezone(new DateTimeZone('Europe/London'));

            return $date->format('j M Y');
        } catch (Exception $exception) {
            return '';
        }
    }

    /**
     * @return array{rating_average: float, review_count: int, breakdown: array<int, int>}
     */
    private static function emptySummary(): array
    {
        return [
            'rating_average' => 0.0,
            'review_count' => 0,
            'breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ];
    }
}
