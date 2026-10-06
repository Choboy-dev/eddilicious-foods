<?php

/**
 * Optional Google Analytics 4 demographics via the Data API.
 */
final class GoogleAnalyticsClient
{
  public static function isConfigured(): bool
  {
    return trim((string) auth_env('GA4_PROPERTY_ID', '')) !== ''
      && is_readable(self::credentialsPath());
  }

  public static function measurementId(): ?string
  {
    $id = trim((string) auth_env('GA4_MEASUREMENT_ID', ''));

    return $id !== '' ? $id : null;
  }

  /**
   * @return list<array{label: string, sessions: int, pct: float}>
   */
  public static function genderBreakdown(string $startDate, string $endDate): array
  {
    if (!self::isConfigured()) {
      return [];
    }

    $rows = self::runReport(
      $startDate,
      $endDate,
      ['userGender'],
      ['sessions']
    );

    return self::formatBreakdown($rows, 'userGender', 'sessions');
  }

  /**
   * @return list<array{label: string, sessions: int, pct: float}>
   */
  public static function ageBreakdown(string $startDate, string $endDate): array
  {
    if (!self::isConfigured()) {
      return [];
    }

    $rows = self::runReport(
      $startDate,
      $endDate,
      ['userAgeBracket'],
      ['sessions']
    );

    return self::formatBreakdown($rows, 'userAgeBracket', 'sessions');
  }

  /**
   * @param list<string> $dimensions
   * @param list<string> $metrics
   * @return list<array<string, mixed>>
   */
  private static function runReport(
    string $startDate,
    string $endDate,
    array $dimensions,
    array $metrics
  ): array {
    $propertyId = trim((string) auth_env('GA4_PROPERTY_ID', ''));
    $token = self::accessToken();
    if ($token === null || $propertyId === '') {
      return [];
    }

    $body = [
      'dateRanges' => [
        [
          'startDate' => $startDate,
          'endDate' => $endDate,
        ],
      ],
      'dimensions' => array_map(static fn (string $name) => ['name' => $name], $dimensions),
      'metrics' => array_map(static fn (string $name) => ['name' => $name], $metrics),
      'limit' => 20,
    ];

    $url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . rawurlencode($propertyId) . ':runReport';
    $response = self::postJson($url, $body, $token);
    if ($response === null) {
      return [];
    }

    $rows = [];
    foreach ($response['rows'] ?? [] as $row) {
      $dimensionValues = $row['dimensionValues'] ?? [];
      $metricValues = $row['metricValues'] ?? [];
      $entry = [];

      foreach ($dimensions as $index => $name) {
        $entry[$name] = (string) ($dimensionValues[$index]['value'] ?? '');
      }

      foreach ($metrics as $index => $name) {
        $entry[$name] = (int) ($metricValues[$index]['value'] ?? 0);
      }

      $rows[] = $entry;
    }

    return $rows;
  }

  /**
   * @param list<array<string, mixed>> $rows
   * @return list<array{label: string, sessions: int, pct: float}>
   */
  private static function formatBreakdown(array $rows, string $dimension, string $metric): array
  {
    $total = 0;
    foreach ($rows as $row) {
      $total += (int) ($row[$metric] ?? 0);
    }

    if ($total <= 0) {
      return [];
    }

    $items = [];
    foreach ($rows as $row) {
      $count = (int) ($row[$metric] ?? 0);
      if ($count <= 0) {
        continue;
      }

      $label = self::prettifyLabel((string) ($row[$dimension] ?? 'Unknown'));
      if ($label === '(not set)' || $label === 'unknown') {
        continue;
      }

      $items[] = [
        'label' => $label,
        'sessions' => $count,
        'pct' => round(($count / $total) * 100, 1),
      ];
    }

    usort($items, static fn (array $a, array $b) => $b['sessions'] <=> $a['sessions']);

    return $items;
  }

  private static function prettifyLabel(string $label): string
  {
    $label = trim($label);
    if ($label === '') {
      return 'Unknown';
    }

    $map = [
      'male' => 'Male',
      'female' => 'Female',
      'unknown' => 'Unknown',
      '(not set)' => '(not set)',
    ];

    $lower = strtolower($label);

    return $map[$lower] ?? ucwords(str_replace('_', ' ', $label));
  }

  private static function credentialsPath(): string
  {
    $configured = trim((string) auth_env('GA4_CREDENTIALS_JSON', ''));
    if ($configured === '') {
      return dirname(__DIR__, 2) . '/storage/ga4-credentials.json';
    }

    if ($configured[0] === '/') {
      return $configured;
    }

    return dirname(__DIR__, 2) . '/' . ltrim($configured, '/');
  }

  private static function accessToken(): ?string
  {
    $path = self::credentialsPath();
    if (!is_readable($path)) {
      return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
      return null;
    }

    $credentials = json_decode($raw, true);
    if (!is_array($credentials)) {
      return null;
    }

    $clientEmail = (string) ($credentials['client_email'] ?? '');
    $privateKey = (string) ($credentials['private_key'] ?? '');
    if ($clientEmail === '' || $privateKey === '') {
      return null;
    }

    $now = time();
    $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claim = self::base64UrlEncode(json_encode([
      'iss' => $clientEmail,
      'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
      'aud' => 'https://oauth2.googleapis.com/token',
      'iat' => $now,
      'exp' => $now + 3600,
    ]));

    $unsigned = $header . '.' . $claim;
    $signature = '';
    $signed = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    if (!$signed) {
      return null;
    }

    $jwt = $unsigned . '.' . self::base64UrlEncode($signature);

    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
          'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
          'assertion' => $jwt,
        ]),
        'timeout' => 8,
        'ignore_errors' => true,
      ],
    ]);

    $rawResponse = @file_get_contents('https://oauth2.googleapis.com/token', false, $context);
    if ($rawResponse === false) {
      return null;
    }

    $data = json_decode($rawResponse, true);
    if (!is_array($data) || empty($data['access_token'])) {
      return null;
    }

    return (string) $data['access_token'];
  }

  /**
   * @param array<string, mixed> $body
   * @return array<string, mixed>|null
   */
  private static function postJson(string $url, array $body, string $token): ?array
  {
    $payload = json_encode($body);
    if ($payload === false) {
      return null;
    }

    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$token}\r\n",
        'content' => $payload,
        'timeout' => 8,
        'ignore_errors' => true,
      ],
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
      return null;
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : null;
  }

  private static function base64UrlEncode(string $value): string
  {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }
}
