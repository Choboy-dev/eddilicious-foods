<?php

/**
 * Resolve traffic source / medium from referrer and UTM params.
 */
final class TrafficSource
{
  /**
   * @param array<string, mixed> $payload
   * @return array{source: string, medium: string, campaign: ?string}
   */
  public static function resolve(array $payload, ?string $referrer): array
  {
    $utmSource = self::clean((string) ($payload['utm_source'] ?? ''));
    $utmMedium = self::clean((string) ($payload['utm_medium'] ?? ''));
    $utmCampaign = self::clean((string) ($payload['utm_campaign'] ?? ''));

    if ($utmSource !== '') {
      return [
        'source' => $utmSource,
        'medium' => $utmMedium !== '' ? $utmMedium : 'campaign',
        'campaign' => $utmCampaign !== '' ? $utmCampaign : null,
      ];
    }

    $referrer = trim((string) $referrer);
    if ($referrer === '') {
      return [
        'source' => 'direct',
        'medium' => '(none)',
        'campaign' => null,
      ];
    }

    $host = parse_url($referrer, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
      return [
        'source' => 'referral',
        'medium' => 'referral',
        'campaign' => null,
      ];
    }

    $host = strtolower($host);
    $host = preg_replace('/^www\./', '', $host) ?? $host;

    $searchEngines = [
      'google.' => 'google',
      'bing.' => 'bing',
      'yahoo.' => 'yahoo',
      'duckduckgo.' => 'duckduckgo',
      'ecosia.' => 'ecosia',
    ];

    foreach ($searchEngines as $needle => $name) {
      if (str_contains($host, $needle)) {
        return [
          'source' => $name,
          'medium' => 'organic',
          'campaign' => null,
        ];
      }
    }

    $socialHosts = [
      'facebook.' => 'facebook',
      'fb.' => 'facebook',
      'instagram.' => 'instagram',
      'twitter.' => 'twitter',
      'x.com' => 'twitter',
      't.co' => 'twitter',
      'linkedin.' => 'linkedin',
      'tiktok.' => 'tiktok',
      'youtube.' => 'youtube',
      'pinterest.' => 'pinterest',
      'whatsapp.' => 'whatsapp',
    ];

    foreach ($socialHosts as $needle => $name) {
      if (str_contains($host, $needle)) {
        return [
          'source' => $name,
          'medium' => 'social',
          'campaign' => null,
        ];
      }
    }

    return [
      'source' => $host,
      'medium' => 'referral',
      'campaign' => null,
    ];
  }

  private static function clean(string $value): string
  {
    $value = trim($value);
    if ($value === '') {
      return '';
    }

    return substr($value, 0, 120);
  }
}
