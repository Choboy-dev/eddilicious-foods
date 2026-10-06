<?php

/**
 * Google OAuth 2.0 — authorization code flow (server-side, uses client secret).
 */
class GoogleOAuth
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const STATE_TTL = 600;

    public static function isConfigured(): bool
    {
        return (bool) auth_env('GOOGLE_CLIENT_ID')
            && (bool) auth_env('GOOGLE_CLIENT_SECRET');
    }

    public static function redirectUri(): string
    {
        $configured = auth_env('GOOGLE_REDIRECT_URI');
        if ($configured) {
            return rtrim($configured, '/');
        }

        return auth_app_url() . '/auth/google-callback.php';
    }

    public static function buildAuthorizationUrl(string $purpose, string $redirect): string
    {
        $state = bin2hex(random_bytes(32));

        $_SESSION['google_oauth'] = [
            'state' => $state,
            'purpose' => $purpose,
            'redirect' => auth_safe_redirect($redirect),
            'created_at' => time(),
        ];

        $params = [
            'client_id' => auth_env('GOOGLE_CLIENT_ID'),
            'redirect_uri' => self::redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * @return array{ok: bool, purpose?: string, redirect?: string, error_code?: string, user?: array}
     */
    public static function handleCallback(?string $code, ?string $state, ?string $error): array
    {
        $oauthState = self::consumeState($state);
        $purpose = is_array($oauthState) ? ($oauthState['purpose'] ?? 'signin') : 'signin';
        $redirect = is_array($oauthState) ? ($oauthState['redirect'] ?? 'account.php') : 'account.php';

        if ($error !== null && $error !== '') {
            $errorCode = $error === 'access_denied' ? 'google_cancelled' : 'google_failed';

            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => $errorCode,
            ];
        }

        if (!is_array($oauthState)) {
            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => 'session_expired',
            ];
        }

        if ($code === null || $code === '') {
            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => 'google_failed',
            ];
        }

        $tokens = self::exchangeCode($code);
        if (!$tokens['ok']) {
            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => 'google_failed',
            ];
        }

        $idToken = (string) ($tokens['id_token'] ?? '');
        if ($idToken === '') {
            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => 'google_failed',
            ];
        }

        $verified = GoogleVerifier::verifyIdToken($idToken);
        if (!$verified['ok']) {
            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => 'google_failed',
            ];
        }

        $existing = UserRepository::findByEmail($verified['email']);

        if ($purpose === 'signup' && $existing && empty($existing['google_id'])) {
            return [
                'ok' => false,
                'purpose' => $purpose,
                'redirect' => $redirect,
                'error_code' => 'account_exists',
            ];
        }

        $user = UserRepository::upsertFromGoogle(
            $verified['google_id'],
            $verified['email'],
            $verified['name'],
            $verified['avatar_url']
        );

        AuthSession::login($user);

        return [
            'ok' => true,
            'purpose' => $purpose,
            'redirect' => $redirect,
            'user' => $user,
        ];
    }

    public static function errorMessage(string $code): string
    {
        return match ($code) {
            'google_cancelled' => 'Google sign-in was cancelled.',
            'session_expired' => 'Your session expired. Try Google sign-in again.',
            'account_exists' => 'This email already has an account. Sign in with your email code instead.',
            'google_not_configured' => 'Google sign-in is not set up yet. Ask the site owner to add Google credentials.',
            default => 'Google sign-in failed. Try again.',
        };
    }

    /**
     * @return array{ok: bool, access_token?: string, id_token?: string}
     */
    private static function exchangeCode(string $code): array
    {
        $payload = http_build_query([
            'code' => $code,
            'client_id' => auth_env('GOOGLE_CLIENT_ID'),
            'client_secret' => auth_env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => self::redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        $response = self::httpPost(self::TOKEN_URL, $payload);
        if ($response === null) {
            return ['ok' => false];
        }

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['id_token'])) {
            return ['ok' => false];
        }

        return [
            'ok' => true,
            'access_token' => (string) ($data['access_token'] ?? ''),
            'id_token' => (string) $data['id_token'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function consumeState(?string $state): ?array
    {
        $stored = $_SESSION['google_oauth'] ?? null;
        unset($_SESSION['google_oauth']);

        if (!is_array($stored) || empty($stored['state'])) {
            return null;
        }

        if (!hash_equals($stored['state'], (string) $state)) {
            return null;
        }

        if (time() - (int) ($stored['created_at'] ?? 0) > self::STATE_TTL) {
            return null;
        }

        return $stored;
    }

    private static function httpPost(string $url, string $body): ?string
    {
        if (function_exists('curl_init')) {
            $handle = curl_init($url);
            if ($handle === false) {
                return null;
            }

            curl_setopt_array($handle, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $response = curl_exec($handle);
            curl_close($handle);

            return is_string($response) ? $response : null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        return is_string($response) ? $response : null;
    }
}
