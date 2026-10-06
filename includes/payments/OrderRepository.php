<?php

class OrderRepository
{
    public static function migrate(): void
    {
        auth_database()->exec(
            'CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_ref TEXT NOT NULL UNIQUE,
                stripe_session_id TEXT UNIQUE,
                stripe_payment_intent TEXT,
                status TEXT NOT NULL DEFAULT "pending",
                email TEXT NOT NULL,
                customer_name TEXT,
                phone TEXT,
                fulfillment TEXT NOT NULL,
                subtotal_pence INTEGER NOT NULL,
                discount_pence INTEGER NOT NULL DEFAULT 0,
                delivery_pence INTEGER NOT NULL DEFAULT 0,
                total_pence INTEGER NOT NULL,
                coupon_code TEXT,
                cart_json TEXT NOT NULL,
                customer_json TEXT NOT NULL,
                notes TEXT,
                created_at TEXT NOT NULL,
                paid_at TEXT
            )'
        );

        auth_database()->exec('CREATE INDEX IF NOT EXISTS idx_orders_email ON orders(email)');
        auth_database()->exec('CREATE INDEX IF NOT EXISTS idx_orders_stripe_session ON orders(stripe_session_id)');
    }

    public static function createPending(array $data): array
    {
        $orderRef = self::generateOrderRef();
        $now = gmdate('c');

        $stmt = auth_database()->prepare(
            'INSERT INTO orders (
                order_ref, status, email, customer_name, phone, fulfillment,
                subtotal_pence, discount_pence, delivery_pence, total_pence,
                coupon_code, cart_json, customer_json, notes, created_at
             ) VALUES (
                :order_ref, "pending", :email, :customer_name, :phone, :fulfillment,
                :subtotal_pence, :discount_pence, :delivery_pence, :total_pence,
                :coupon_code, :cart_json, :customer_json, :notes, :created_at
             )'
        );

        $stmt->execute([
            'order_ref' => $orderRef,
            'email' => auth_sanitize_email((string) $data['email']),
            'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'fulfillment' => (string) $data['fulfillment'],
            'subtotal_pence' => (int) $data['subtotal_pence'],
            'discount_pence' => (int) $data['discount_pence'],
            'delivery_pence' => (int) $data['delivery_pence'],
            'total_pence' => (int) $data['total_pence'],
            'coupon_code' => $data['coupon_code'] ?? null,
            'cart_json' => json_encode($data['cart'] ?? [], JSON_UNESCAPED_UNICODE),
            'customer_json' => json_encode($data['customer'] ?? [], JSON_UNESCAPED_UNICODE),
            'notes' => trim((string) ($data['notes'] ?? '')),
            'created_at' => $now,
        ]);

        return self::findByRef($orderRef) ?? [];
    }

    public static function attachStripeSession(string $orderRef, string $sessionId): void
    {
        $stmt = auth_database()->prepare(
            'UPDATE orders SET stripe_session_id = :stripe_session_id WHERE order_ref = :order_ref'
        );
        $stmt->execute([
            'stripe_session_id' => $sessionId,
            'order_ref' => $orderRef,
        ]);
    }

    public static function markPaidBySessionId(string $sessionId, ?string $paymentIntent = null): ?array
    {
        $order = self::findByStripeSession($sessionId);
        if (!$order) {
            return null;
        }

        if (($order['status'] ?? '') === 'paid') {
            return $order;
        }

        $stmt = auth_database()->prepare(
            'UPDATE orders
             SET status = "paid",
                 stripe_payment_intent = COALESCE(:payment_intent, stripe_payment_intent),
                 paid_at = :paid_at
             WHERE stripe_session_id = :stripe_session_id
               AND status = "pending"'
        );
        $stmt->execute([
            'payment_intent' => $paymentIntent,
            'paid_at' => gmdate('c'),
            'stripe_session_id' => $sessionId,
        ]);

        if ($stmt->rowCount() === 0) {
            return self::findByStripeSession($sessionId);
        }

        $paidOrder = self::findByStripeSession($sessionId);
        if ($paidOrder !== null) {
            self::notifyOwnerPaidOrder($paidOrder);
        }

        return $paidOrder;
    }

    /**
     * @param array<string, mixed> $order
     */
    private static function notifyOwnerPaidOrder(array $order): void
    {
        require_once __DIR__ . '/TwilioWhatsAppNotifier.php';

        $result = TwilioWhatsAppNotifier::sendNewOrderAlert($order);
        if (!$result['ok']) {
            error_log('WhatsApp order alert failed for ' . ($order['order_ref'] ?? 'unknown') . ': ' . ($result['error'] ?? 'unknown error'));
        }
    }

    public static function findByRef(string $orderRef): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM orders WHERE order_ref = :order_ref LIMIT 1');
        $stmt->execute(['order_ref' => $orderRef]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findByStripeSession(string $sessionId): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM orders WHERE stripe_session_id = :id LIMIT 1');
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function couponUsedByEmail(string $email, string $couponCode): bool
    {
        $stmt = auth_database()->prepare(
            'SELECT id FROM orders
             WHERE email = :email
               AND coupon_code = :coupon_code
               AND status = "paid"
             LIMIT 1'
        );
        $stmt->execute([
            'email' => auth_sanitize_email($email),
            'coupon_code' => strtoupper(trim($couponCode)),
        ]);

        return (bool) $stmt->fetch();
    }

    public static function markFailedBySessionId(string $sessionId, ?string $reason = null): ?array
    {
        $order = self::findByStripeSession($sessionId);
        if (!$order || ($order['status'] ?? '') === 'paid') {
            return $order;
        }

        $stmt = auth_database()->prepare(
            'UPDATE orders SET status = "failed", notes = COALESCE(:notes, notes)
             WHERE stripe_session_id = :stripe_session_id AND status = "pending"'
        );
        $stmt->execute([
            'notes' => $reason,
            'stripe_session_id' => $sessionId,
        ]);

        return self::findByStripeSession($sessionId);
    }

    public static function markCancelledByRef(string $orderRef): void
    {
        $stmt = auth_database()->prepare(
            'UPDATE orders SET status = "cancelled"
             WHERE order_ref = :order_ref AND status = "pending"'
        );
        $stmt->execute(['order_ref' => $orderRef]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listForAdmin(?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT * FROM orders';
        $params = [];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $sql .= ' WHERE status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY datetime(created_at) DESC LIMIT :limit OFFSET :offset';

        $stmt = auth_database()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public static function countByStatus(?string $status = null): int
    {
        if ($status === null || $status === '' || $status === 'all') {
            $stmt = auth_database()->query('SELECT COUNT(*) AS total FROM orders');
        } else {
            $stmt = auth_database()->prepare('SELECT COUNT(*) AS total FROM orders WHERE status = :status');
            $stmt->execute(['status' => $status]);
        }

        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return array{period: string, label: string, start: string, end: string, chart_granularity: string}
     */
    public static function resolveDashboardPeriod(string $period, ?string $from = null, ?string $to = null): array
    {
        $period = strtolower(trim($period));
        $today = self::dashboardToday();

        if ($period === 'custom') {
            $start = self::parseDashboardDate($from);
            $end = self::parseDashboardDate($to);

            if ($start === null || $end === null) {
                throw new InvalidArgumentException('Pick a valid start and end date.');
            }

            if ($start > $end) {
                throw new InvalidArgumentException('The start date must be before the end date.');
            }

            $daySpan = $start->diff($end)->days + 1;
            if ($daySpan > 366) {
                throw new InvalidArgumentException('Custom ranges can be up to 366 days.');
            }

            return [
                'period' => 'custom',
                'label' => self::formatDashboardRangeLabel($start, $end),
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'chart_granularity' => $daySpan > 45 ? 'week' : 'day',
            ];
        }

        $map = [
            'today' => [
                'label' => 'Today',
                'start' => $today,
                'end' => $today,
                'chart_granularity' => 'day',
            ],
            '7d' => [
                'label' => 'Last 7 days',
                'start' => $today->modify('-6 days'),
                'end' => $today,
                'chart_granularity' => 'day',
            ],
            '30d' => [
                'label' => 'Last 30 days',
                'start' => $today->modify('-29 days'),
                'end' => $today,
                'chart_granularity' => 'day',
            ],
            'all' => [
                'label' => 'All time',
                'start' => $today->modify('-11 months')->modify('first day of this month'),
                'end' => $today,
                'chart_granularity' => 'month',
            ],
        ];

        if (!isset($map[$period])) {
            $period = '7d';
        }

        $resolved = $map[$period];

        return [
            'period' => $period,
            'label' => $resolved['label'],
            'start' => $resolved['start']->format('Y-m-d'),
            'end' => $resolved['end']->format('Y-m-d'),
            'chart_granularity' => $resolved['chart_granularity'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dashboardStatsForPeriod(string $period = '7d', ?string $from = null, ?string $to = null): array
    {
        $bounds = self::resolveDashboardPeriod($period, $from, $to);
        $pdo = auth_database();

        $revenueStart = $bounds['period'] === 'all' ? null : $bounds['start'];
        $revenueEnd = $bounds['period'] === 'all' ? null : $bounds['end'];

        $revenuePence = self::sumPaidRevenueBetween($revenueStart, $revenueEnd);

        $orderStart = $bounds['period'] === 'all' ? null : $bounds['start'];
        $orderEnd = $bounds['period'] === 'all' ? null : $bounds['end'];

        return [
            'period' => $bounds['period'],
            'period_label' => $bounds['label'],
            'period_start' => $bounds['start'],
            'period_end' => $bounds['end'],
            'revenue_pence' => $revenuePence,
            'orders_paid' => self::countByStatusBetween('paid', $orderStart, $orderEnd),
            'orders_pending' => self::countByStatusBetween('pending', $orderStart, $orderEnd),
            'orders_failed' => self::countByStatusBetween('failed', $orderStart, $orderEnd),
            'orders_cancelled' => self::countByStatusBetween('cancelled', $orderStart, $orderEnd),
            'revenue_chart' => self::revenueChartBetween(
                $bounds['start'],
                $bounds['end'],
                $bounds['chart_granularity']
            ),
            'chart_granularity' => $bounds['chart_granularity'],
            'stripe_configured' => auth_stripe_configured(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dashboardStats(): array
    {
        return self::dashboardStatsForPeriod('7d');
    }

    private static function dashboardToday(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone('Europe/London'));
    }

    private static function parseDashboardDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', trim($value), self::dashboardToday()->getTimezone());

        if ($date === false || $date->format('Y-m-d') !== trim($value)) {
            return null;
        }

        return $date;
    }

    private static function formatDashboardRangeLabel(DateTimeImmutable $start, DateTimeImmutable $end): string
    {
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $start->format('j M Y');
        }

        if ($start->format('Y') === $end->format('Y')) {
            return $start->format('j M') . ' – ' . $end->format('j M Y');
        }

        return $start->format('j M Y') . ' – ' . $end->format('j M Y');
    }

    private static function sumPaidRevenueBetween(?string $start, ?string $end): int
    {
        $sql = "SELECT COALESCE(SUM(total_pence), 0) AS total FROM orders
                WHERE status = 'paid' AND paid_at IS NOT NULL";
        $params = [];

        if ($start !== null && $end !== null) {
            $sql .= ' AND date(paid_at) >= date(:start) AND date(paid_at) <= date(:end)';
            $params['start'] = $start;
            $params['end'] = $end;
        }

        $stmt = auth_database()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    private static function countByStatusBetween(string $status, ?string $start, ?string $end): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM orders WHERE status = :status';
        $params = ['status' => $status];

        if ($start !== null && $end !== null) {
            $sql .= ' AND date(created_at) >= date(:start) AND date(created_at) <= date(:end)';
            $params['start'] = $start;
            $params['end'] = $end;
        }

        $stmt = auth_database()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return list<array{key: string, label: string, total_pence: int}>
     */
    private static function revenueChartBetween(string $start, string $end, string $granularity): array
    {
        $pdo = auth_database();
        $buckets = self::chartBuckets($start, $end, $granularity);
        $totals = [];

        if ($granularity === 'month') {
            $stmt = $pdo->prepare(
                "SELECT strftime('%Y-%m', paid_at) AS bucket, COALESCE(SUM(total_pence), 0) AS total
                 FROM orders
                 WHERE status = 'paid' AND paid_at IS NOT NULL
                   AND date(paid_at) >= date(:start) AND date(paid_at) <= date(:end)
                 GROUP BY bucket
                 ORDER BY bucket ASC"
            );
            $stmt->execute(['start' => $start, 'end' => $end]);
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $totals[(string) ($row['bucket'] ?? '')] = (int) ($row['total'] ?? 0);
            }
        } elseif ($granularity === 'week') {
            $stmt = $pdo->prepare(
                "SELECT date(paid_at) AS day, COALESCE(SUM(total_pence), 0) AS total
                 FROM orders
                 WHERE status = 'paid' AND paid_at IS NOT NULL
                   AND date(paid_at) >= date(:start) AND date(paid_at) <= date(:end)
                 GROUP BY day
                 ORDER BY day ASC"
            );
            $stmt->execute(['start' => $start, 'end' => $end]);
            $daily = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $daily[(string) ($row['day'] ?? '')] = (int) ($row['total'] ?? 0);
            }

            foreach ($buckets as $bucket) {
                $weekTotal = 0;
                $cursor = new DateTimeImmutable($bucket['key'], self::dashboardToday()->getTimezone());
                $weekEnd = $cursor->modify('+6 days');
                while ($cursor <= $weekEnd) {
                    $key = $cursor->format('Y-m-d');
                    $weekTotal += $daily[$key] ?? 0;
                    $cursor = $cursor->modify('+1 day');
                }
                $totals[$bucket['key']] = $weekTotal;
            }
        } else {
            $stmt = $pdo->prepare(
                "SELECT date(paid_at) AS day, COALESCE(SUM(total_pence), 0) AS total
                 FROM orders
                 WHERE status = 'paid' AND paid_at IS NOT NULL
                   AND date(paid_at) >= date(:start) AND date(paid_at) <= date(:end)
                 GROUP BY day
                 ORDER BY day ASC"
            );
            $stmt->execute(['start' => $start, 'end' => $end]);
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $totals[(string) ($row['day'] ?? '')] = (int) ($row['total'] ?? 0);
            }
        }

        return array_map(function (array $bucket) use ($totals) {
            return [
                'key' => $bucket['key'],
                'label' => $bucket['label'],
                'total_pence' => $totals[$bucket['key']] ?? 0,
            ];
        }, $buckets);
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private static function chartBuckets(string $start, string $end, string $granularity): array
    {
        $tz = self::dashboardToday()->getTimezone();
        $cursor = new DateTimeImmutable($start, $tz);
        $endDate = new DateTimeImmutable($end, $tz);
        $buckets = [];

        if ($granularity === 'month') {
            $cursor = $cursor->modify('first day of this month');
            while ($cursor <= $endDate) {
                $key = $cursor->format('Y-m');
                $buckets[] = [
                    'key' => $key,
                    'label' => $cursor->format('M'),
                ];
                $cursor = $cursor->modify('+1 month');
            }

            return $buckets;
        }

        if ($granularity === 'week') {
            while ($cursor <= $endDate) {
                $key = $cursor->format('Y-m-d');
                $buckets[] = [
                    'key' => $key,
                    'label' => $cursor->format('j M'),
                ];
                $cursor = $cursor->modify('+7 days');
            }

            return $buckets;
        }

        while ($cursor <= $endDate) {
            $key = $cursor->format('Y-m-d');
            $isSingleDay = $start === $end;
            $buckets[] = [
                'key' => $key,
                'label' => $isSingleDay ? 'Today' : $cursor->format('D'),
            ];
            $cursor = $cursor->modify('+1 day');
        }

        return $buckets;
    }

    private static function generateOrderRef(): string
    {
        return 'EDF-' . strtoupper(bin2hex(random_bytes(4))) . '-' . gmdate('ymd');
    }
}
