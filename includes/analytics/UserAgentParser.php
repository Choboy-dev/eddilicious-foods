<?php

/**
 * Lightweight user-agent parsing for device, browser, and OS.
 */
final class UserAgentParser
{
  /**
   * @return array{device_type: string, browser: string, os: string, is_bot: bool}
   */
  public static function parse(?string $userAgent): array
  {
    $ua = strtolower(trim((string) $userAgent));

    if ($ua === '') {
      return [
        'device_type' => 'unknown',
        'browser' => 'Unknown',
        'os' => 'Unknown',
        'is_bot' => false,
      ];
    }

    $isBot = (bool) preg_match(
      '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|whatsapp|preview|headless|phantom|wget|curl\/|python-requests|go-http-client|semrush|ahrefs|bingpreview|yandex|duckduckbot|bytespider|petalbot|gptbot|claudebot|anthropic/i',
      $ua
    );

    $deviceType = 'desktop';
    if (preg_match('/ipad|tablet|kindle|playbook|silk/i', $ua)) {
      $deviceType = 'tablet';
    } elseif (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini/i', $ua)) {
      $deviceType = 'mobile';
    }

    $browser = 'Other';
    if (str_contains($ua, 'edg/') || str_contains($ua, 'edge/')) {
      $browser = 'Edge';
    } elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
      $browser = 'Opera';
    } elseif (str_contains($ua, 'firefox/')) {
      $browser = 'Firefox';
    } elseif (str_contains($ua, 'chrome/') && !str_contains($ua, 'chromium')) {
      $browser = 'Chrome';
    } elseif (str_contains($ua, 'safari/') && !str_contains($ua, 'chrome/')) {
      $browser = 'Safari';
    } elseif (str_contains($ua, 'msie') || str_contains($ua, 'trident/')) {
      $browser = 'Internet Explorer';
    }

    $os = 'Other';
    if (str_contains($ua, 'windows')) {
      $os = 'Windows';
    } elseif (str_contains($ua, 'mac os x') || str_contains($ua, 'macintosh')) {
      $os = 'macOS';
    } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod')) {
      $os = 'iOS';
    } elseif (str_contains($ua, 'android')) {
      $os = 'Android';
    } elseif (str_contains($ua, 'linux')) {
      $os = 'Linux';
    } elseif (str_contains($ua, 'cros')) {
      $os = 'Chrome OS';
    }

    return [
      'device_type' => $deviceType,
      'browser' => $browser,
      'os' => $os,
      'is_bot' => $isBot,
    ];
  }
}
