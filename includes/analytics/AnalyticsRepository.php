<?php

require_once __DIR__ . '/UserAgentParser.php';
require_once __DIR__ . '/TrafficSource.php';
require_once __DIR__ . '/GeoLookup.php';
require_once __DIR__ . '/GoogleAnalyticsClient.php';

final class AnalyticsRepository
{
  public static function migrate(): void
  {
    $pdo = auth_database();

    $pdo->exec(
      'CREATE TABLE IF NOT EXISTS analytics_pageviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT NOT NULL,
        visitor_id TEXT NOT NULL,
        path TEXT NOT NULL,
        page_title TEXT,
        referrer TEXT,
        source TEXT NOT NULL DEFAULT "direct",
        medium TEXT NOT NULL DEFAULT "(none)",
        campaign TEXT,
        device_type TEXT NOT NULL DEFAULT "desktop",
        browser TEXT,
        os TEXT,
        country TEXT,
        region TEXT,
        city TEXT,
        ip_hash TEXT,
        screen_width INTEGER,
        language TEXT,
        created_at TEXT NOT NULL
      )'
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_analytics_pageviews_created ON analytics_pageviews(created_at)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_analytics_pageviews_session ON analytics_pageviews(session_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_analytics_pageviews_path ON analytics_pageviews(path)');

    $pdo->exec(
      'CREATE TABLE IF NOT EXISTS analytics_geo_cache (
        ip_hash TEXT PRIMARY KEY,
        country TEXT,
        region TEXT,
        city TEXT,
        cached_at TEXT NOT NULL
      )'
    );
  }

  public static function isEnabled(): bool
  {
    $value = strtolower(trim((string) auth_env('ANALYTICS_ENABLED', 'true')));

    return !in_array($value, ['0', 'false', 'no', 'off'], true);
  }

  /**
   * @param array<string, mixed> $payload
   * @return array{ok: bool, error?: string}
   */
  public static function recordPageview(array $payload): array
  {
    if (!self::isEnabled()) {
      return ['ok' => true];
    }

    $sessionId = self::cleanId((string) ($payload['session_id'] ?? ''));
    $visitorId = self::cleanId((string) ($payload['visitor_id'] ?? ''));
    $path = self::cleanPath((string) ($payload['path'] ?? ''));

    if ($sessionId === '' || $visitorId === '' || $path === '') {
      return ['ok' => false, 'error' => 'Missing tracking fields.'];
    }

    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $parsed = UserAgentParser::parse($userAgent);
    if ($parsed['is_bot']) {
      return ['ok' => true];
    }

    if (self::isDuplicateHit($sessionId, $path)) {
      return ['ok' => true];
    }

    $referrer = self::cleanText((string) ($payload['referrer'] ?? ''), 500);
    $traffic = TrafficSource::resolve($payload, $referrer);
    $ip = GeoLookup::clientIp();
    $geo = GeoLookup::resolve($ip);

    $pdo = auth_database();
    $stmt = $pdo->prepare(
      'INSERT INTO analytics_pageviews (
        session_id, visitor_id, path, page_title, referrer, source, medium, campaign,
        device_type, browser, os, country, region, city, ip_hash, screen_width, language, created_at
      ) VALUES (
        :session_id, :visitor_id, :path, :page_title, :referrer, :source, :medium, :campaign,
        :device_type, :browser, :os, :country, :region, :city, :ip_hash, :screen_width, :language, :created_at
      )'
    );

    $stmt->execute([
      'session_id' => $sessionId,
      'visitor_id' => $visitorId,
      'path' => $path,
      'page_title' => self::cleanText((string) ($payload['page_title'] ?? ''), 200),
      'referrer' => $referrer !== '' ? $referrer : null,
      'source' => $traffic['source'],
      'medium' => $traffic['medium'],
      'campaign' => $traffic['campaign'],
      'device_type' => $parsed['device_type'],
      'browser' => $parsed['browser'],
      'os' => $parsed['os'],
      'country' => $geo['country'],
      'region' => $geo['region'],
      'city' => $geo['city'],
      'ip_hash' => GeoLookup::hashIp($ip),
      'screen_width' => self::intOrNull($payload['screen_width'] ?? null),
      'language' => self::cleanText((string) ($payload['language'] ?? ''), 20),
      'created_at' => gmdate('c'),
    ]);

    return ['ok' => true];
  }

  /**
   * @return array<string, mixed>
   */
  public static function dashboardStatsForPeriod(string $period = '7d', ?string $from = null, ?string $to = null): array
  {
    require_once dirname(__DIR__) . '/payments/OrderRepository.php';
    $bounds = OrderRepository::resolveDashboardPeriod($period, $from, $to);
    $pdo = auth_database();

    $where = self::periodWhereSql('created_at', $bounds);
    $params = self::periodParams($bounds);

    $pageviews = self::scalarCount($pdo, 'SELECT COUNT(*) AS total FROM analytics_pageviews WHERE ' . $where, $params);
    $sessions = self::scalarCount(
      $pdo,
      'SELECT COUNT(DISTINCT session_id) AS total FROM analytics_pageviews WHERE ' . $where,
      $params
    );
    $visitors = self::scalarCount(
      $pdo,
      'SELECT COUNT(DISTINCT visitor_id) AS total FROM analytics_pageviews WHERE ' . $where,
      $params
    );

    $bounceRate = self::bounceRate($pdo, $where, $params);
    $avgPages = $sessions > 0 ? round($pageviews / $sessions, 1) : 0.0;

    $viewsChart = self::viewsChart($pdo, $bounds);
    $devices = self::dimensionBreakdown($pdo, 'device_type', $where, $params);
    $sources = self::sourceBreakdown($pdo, $where, $params);
    $countries = self::dimensionBreakdown($pdo, 'country', $where, $params, 12);
    $cities = self::cityBreakdown($pdo, $where, $params, 12);
    $browsers = self::dimensionBreakdown($pdo, 'browser', $where, $params, 8);
    $operatingSystems = self::dimensionBreakdown($pdo, 'os', $where, $params, 8);
    $pages = self::topPages($pdo, $where, $params, 10);
    $referrers = self::topReferrers($pdo, $where, $params, 8);

    $gender = GoogleAnalyticsClient::genderBreakdown($bounds['start'], $bounds['end']);
    $ageGroups = GoogleAnalyticsClient::ageBreakdown($bounds['start'], $bounds['end']);

    return [
      'period' => $bounds['period'],
      'period_label' => $bounds['label'],
      'period_start' => $bounds['start'],
      'period_end' => $bounds['end'],
      'pageviews' => $pageviews,
      'sessions' => $sessions,
      'visitors' => $visitors,
      'bounce_rate' => $bounceRate,
      'avg_pages_per_session' => $avgPages,
      'views_chart' => $viewsChart,
      'chart_granularity' => $bounds['chart_granularity'],
      'devices' => $devices,
      'sources' => $sources,
      'countries' => $countries,
      'cities' => $cities,
      'browsers' => $browsers,
      'operating_systems' => $operatingSystems,
      'pages' => $pages,
      'referrers' => $referrers,
      'gender' => $gender,
      'age_groups' => $ageGroups,
      'ga4_connected' => GoogleAnalyticsClient::isConfigured(),
      'ga4_measurement_id' => GoogleAnalyticsClient::measurementId(),
      'tracking_enabled' => self::isEnabled(),
    ];
  }

  /**
   * @param array{period: string, start: string, end: string, chart_granularity: string} $bounds
   */
  private static function periodWhereSql(string $column, array $bounds): string
  {
    if ($bounds['period'] === 'all') {
      return '1 = 1';
    }

    return "date({$column}) >= :period_start AND date({$column}) <= :period_end";
  }

  /**
   * @param array{period: string, start: string, end: string} $bounds
   * @return array<string, string>
   */
  private static function periodParams(array $bounds): array
  {
    if ($bounds['period'] === 'all') {
      return [];
    }

    return [
      'period_start' => $bounds['start'],
      'period_end' => $bounds['end'],
    ];
  }

  /**
   * @param array<string, string> $params
   */
  private static function scalarCount(PDO $pdo, string $sql, array $params): int
  {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    return (int) ($row['total'] ?? $row['COUNT(*)'] ?? 0);
  }

  /**
   * @param array<string, string> $params
   */
  private static function bounceRate(PDO $pdo, string $where, array $params): string
  {
    $sql = 'SELECT session_id, COUNT(*) AS views
            FROM analytics_pageviews
            WHERE ' . $where . '
            GROUP BY session_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    if (!$rows) {
      return '0%';
    }

    $bounces = 0;
    foreach ($rows as $row) {
      if ((int) ($row['views'] ?? 0) <= 1) {
        $bounces++;
      }
    }

    $rate = round(($bounces / count($rows)) * 100);

    return $rate . '%';
  }

  /**
   * @param array{period: string, start: string, end: string, chart_granularity: string} $bounds
   * @return list<array{label: string, views: int}>
   */
  private static function viewsChart(PDO $pdo, array $bounds): array
  {
    if ($bounds['period'] === 'all' || $bounds['chart_granularity'] === 'month') {
      $sql = 'SELECT strftime("%Y-%m", created_at) AS bucket, COUNT(*) AS views
              FROM analytics_pageviews
              GROUP BY bucket
              ORDER BY bucket ASC
              LIMIT 12';
      $stmt = $pdo->query($sql);
    } elseif ($bounds['chart_granularity'] === 'week') {
      $sql = 'SELECT strftime("%Y-W%W", created_at) AS bucket, COUNT(*) AS views
              FROM analytics_pageviews
              WHERE date(created_at) >= :period_start AND date(created_at) <= :period_end
              GROUP BY bucket
              ORDER BY bucket ASC';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        'period_start' => $bounds['start'],
        'period_end' => $bounds['end'],
      ]);
    } else {
      $sql = 'SELECT date(created_at) AS bucket, COUNT(*) AS views
              FROM analytics_pageviews
              WHERE date(created_at) >= :period_start AND date(created_at) <= :period_end
              GROUP BY bucket
              ORDER BY bucket ASC';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        'period_start' => $bounds['start'],
        'period_end' => $bounds['end'],
      ]);
    }

    $rows = $stmt->fetchAll();
    $chart = [];

    foreach ($rows as $row) {
      $bucket = (string) ($row['bucket'] ?? '');
      $chart[] = [
        'label' => self::formatChartLabel($bucket, $bounds['chart_granularity']),
        'views' => (int) ($row['views'] ?? 0),
      ];
    }

    return $chart;
  }

  private static function formatChartLabel(string $bucket, string $granularity): string
  {
    if ($granularity === 'month' && preg_match('/^(\d{4})-(\d{2})$/', $bucket, $matches)) {
      $date = DateTimeImmutable::createFromFormat('Y-m-d', $matches[1] . '-' . $matches[2] . '-01');

      return $date ? $date->format('M Y') : $bucket;
    }

    if ($granularity === 'week') {
      return 'Wk ' . substr($bucket, -2);
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $bucket);

    return $date ? $date->format('d M') : $bucket;
  }

  /**
   * @param array<string, string> $params
   * @return list<array{label: string, count: int, pct: float}>
   */
  private static function dimensionBreakdown(
    PDO $pdo,
    string $column,
    string $where,
    array $params,
    int $limit = 6
  ): array {
    $allowed = ['device_type', 'country', 'browser', 'os'];
    if (!in_array($column, $allowed, true)) {
      return [];
    }

    $sql = "SELECT COALESCE(NULLIF({$column}, ''), 'Unknown') AS label, COUNT(*) AS count
            FROM analytics_pageviews
            WHERE {$where}
            GROUP BY label
            ORDER BY count DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return self::withPercentages($rows);
  }

  /**
   * @param array<string, string> $params
   * @return list<array{label: string, count: int, pct: float}>
   */
  private static function sourceBreakdown(PDO $pdo, string $where, array $params): array
  {
    $sql = "SELECT source || ' / ' || medium AS label, COUNT(*) AS count
            FROM analytics_pageviews
            WHERE {$where}
            GROUP BY source, medium
            ORDER BY count DESC
            LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return self::withPercentages($rows);
  }

  /**
   * @param array<string, string> $params
   * @return list<array{label: string, count: int, pct: float}>
   */
  private static function cityBreakdown(PDO $pdo, string $where, array $params, int $limit): array
  {
    $sql = "SELECT COALESCE(city, 'Unknown') || ', ' || COALESCE(country, 'Unknown') AS label, COUNT(*) AS count
            FROM analytics_pageviews
            WHERE {$where}
            GROUP BY city, country
            ORDER BY count DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return self::withPercentages($rows);
  }

  /**
   * @param array<string, string> $params
   * @return list<array{label: string, count: int, pct: float}>
   */
  private static function topPages(PDO $pdo, string $where, array $params, int $limit): array
  {
    $sql = "SELECT path AS label, COUNT(*) AS count
            FROM analytics_pageviews
            WHERE {$where}
            GROUP BY path
            ORDER BY count DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return self::withPercentages($rows);
  }

  /**
   * @param array<string, string> $params
   * @return list<array{label: string, count: int, pct: float}>
   */
  private static function topReferrers(PDO $pdo, string $where, array $params, int $limit): array
  {
    $sql = "SELECT COALESCE(NULLIF(referrer, ''), 'Direct or hidden') AS label, COUNT(*) AS count
            FROM analytics_pageviews
            WHERE {$where}
            GROUP BY referrer
            ORDER BY count DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return self::withPercentages($rows);
  }

  /**
   * @param list<array<string, mixed>> $rows
   * @return list<array{label: string, count: int, pct: float}>
   */
  private static function withPercentages(array $rows): array
  {
    $total = 0;
    foreach ($rows as $row) {
      $total += (int) ($row['count'] ?? 0);
    }

    if ($total <= 0) {
      return [];
    }

    $items = [];
    foreach ($rows as $row) {
      $count = (int) ($row['count'] ?? 0);
      $items[] = [
        'label' => self::prettifyDeviceLabel((string) ($row['label'] ?? 'Unknown')),
        'count' => $count,
        'pct' => round(($count / $total) * 100, 1),
      ];
    }

    return $items;
  }

  private static function prettifyDeviceLabel(string $label): string
  {
    $map = [
      'desktop' => 'Desktop',
      'mobile' => 'Mobile',
      'tablet' => 'Tablet',
      'unknown' => 'Unknown',
    ];

    $lower = strtolower($label);

    return $map[$lower] ?? $label;
  }

  private static function isDuplicateHit(string $sessionId, string $path): bool
  {
    $pdo = auth_database();
    $stmt = $pdo->prepare(
      'SELECT id FROM analytics_pageviews
       WHERE session_id = :session_id AND path = :path
         AND datetime(created_at) >= datetime("now", "-3 seconds")
       LIMIT 1'
    );
    $stmt->execute([
      'session_id' => $sessionId,
      'path' => $path,
    ]);

    return (bool) $stmt->fetch();
  }

  private static function cleanId(string $value): string
  {
    $value = trim($value);
    if ($value === '' || strlen($value) > 64) {
      return '';
    }

    return preg_match('/^[a-zA-Z0-9_-]+$/', $value) ? $value : '';
  }

  private static function cleanPath(string $path): string
  {
    $path = trim($path);
    if ($path === '') {
      return '';
    }

    if (!str_starts_with($path, '/')) {
      $path = '/' . $path;
    }

    return substr($path, 0, 300);
  }

  private static function cleanText(string $value, int $max): ?string
  {
    $value = trim($value);
    if ($value === '') {
      return null;
    }

    return substr($value, 0, $max);
  }

  private static function intOrNull(mixed $value): ?int
  {
    if ($value === null || $value === '') {
      return null;
    }

    $int = (int) $value;

    return $int > 0 ? $int : null;
  }
}
