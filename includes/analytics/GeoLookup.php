<?php

/**
 * Resolve visitor country and city from request headers or IP.
 */
final class GeoLookup
{
  /**
   * @return array{country: ?string, region: ?string, city: ?string}
   */
  public static function resolve(?string $ip = null): array
  {
    $country = self::headerCountry();
    if ($country !== null) {
      return [
        'country' => $country,
        'region' => self::clean(self::serverHeader('CF-IPRegion')),
        'city' => self::clean(self::serverHeader('CF-IPCITY')),
      ];
    }

    $ip = $ip ?? self::clientIp();
    if ($ip === null || self::isPrivateIp($ip)) {
      return [
        'country' => 'Local',
        'region' => null,
        'city' => null,
      ];
    }

    $cached = self::lookupCached($ip);
    if ($cached !== null) {
      return $cached;
    }

    $lookup = self::lookupRemote($ip);
    self::storeCache($ip, $lookup);

    return $lookup;
  }

  public static function clientIp(): ?string
  {
    $candidates = [
      self::serverHeader('HTTP_CF_CONNECTING_IP'),
      self::serverHeader('HTTP_X_FORWARDED_FOR'),
      self::serverHeader('REMOTE_ADDR'),
    ];

    foreach ($candidates as $candidate) {
      if ($candidate === null || $candidate === '') {
        continue;
      }

      $parts = array_map('trim', explode(',', $candidate));
      foreach ($parts as $part) {
        if (filter_var($part, FILTER_VALIDATE_IP)) {
          return $part;
        }
      }
    }

    return null;
  }

  public static function hashIp(?string $ip): ?string
  {
    if ($ip === null || $ip === '') {
      return null;
    }

    $salt = (string) auth_env('APP_URL', 'eddilicious');

    return hash('sha256', $salt . '|' . $ip);
  }

  private static function headerCountry(): ?string
  {
    $country = self::clean(self::serverHeader('HTTP_CF_IPCOUNTRY'));
    if ($country === null || strlen($country) !== 2) {
      return null;
    }

    return strtoupper($country);
  }

  private static function serverHeader(string $key): ?string
  {
    $value = $_SERVER[$key] ?? null;

    return is_string($value) && trim($value) !== '' ? trim($value) : null;
  }

  private static function isPrivateIp(string $ip): bool
  {
    return filter_var(
      $ip,
      FILTER_VALIDATE_IP,
      FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
  }

  /**
   * @return array{country: ?string, region: ?string, city: ?string}
   */
  private static function lookupCached(string $ip): ?array
  {
    $pdo = auth_database();
    $hash = self::hashIp($ip);
    if ($hash === null) {
      return null;
    }

    $stmt = $pdo->prepare(
      'SELECT country, region, city FROM analytics_geo_cache WHERE ip_hash = :ip_hash LIMIT 1'
    );
    $stmt->execute(['ip_hash' => $hash]);
    $row = $stmt->fetch();

    if (!$row) {
      return null;
    }

    return [
      'country' => $row['country'] ?? null,
      'region' => $row['region'] ?? null,
      'city' => $row['city'] ?? null,
    ];
  }

  /**
   * @param array{country: ?string, region: ?string, city: ?string} $lookup
   */
  private static function storeCache(string $ip, array $lookup): void
  {
    $hash = self::hashIp($ip);
    if ($hash === null) {
      return;
    }

    $pdo = auth_database();
    $stmt = $pdo->prepare(
      'INSERT INTO analytics_geo_cache (ip_hash, country, region, city, cached_at)
       VALUES (:ip_hash, :country, :region, :city, :cached_at)
       ON CONFLICT(ip_hash) DO UPDATE SET
         country = excluded.country,
         region = excluded.region,
         city = excluded.city,
         cached_at = excluded.cached_at'
    );
    $stmt->execute([
      'ip_hash' => $hash,
      'country' => $lookup['country'],
      'region' => $lookup['region'],
      'city' => $lookup['city'],
      'cached_at' => gmdate('c'),
    ]);
  }

  /**
   * @return array{country: ?string, region: ?string, city: ?string}
   */
  private static function lookupRemote(string $ip): array
  {
    $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,regionName,city';

    $context = stream_context_create([
      'http' => [
        'timeout' => 2,
        'ignore_errors' => true,
      ],
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
      return [
        'country' => 'Unknown',
        'region' => null,
        'city' => null,
      ];
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
      return [
        'country' => 'Unknown',
        'region' => null,
        'city' => null,
      ];
    }

    return [
      'country' => self::clean((string) ($data['country'] ?? 'Unknown')) ?: 'Unknown',
      'region' => self::clean((string) ($data['regionName'] ?? '')),
      'city' => self::clean((string) ($data['city'] ?? '')),
    ];
  }

  private static function clean(?string $value): ?string
  {
    if ($value === null) {
      return null;
    }

    $value = trim($value);
    if ($value === '') {
      return null;
    }

    return substr($value, 0, 120);
  }
}
