<?php

class GoogleVerifier
{
    public static function verifyIdToken(string $idToken): array
    {
        $clientId = auth_env('GOOGLE_CLIENT_ID');
        if (!$clientId) {
            return ['ok' => false, 'error' => 'Google sign-in is not set up yet. Add GOOGLE_CLIENT_ID to your .env file.'];
        }

        if ($idToken === '') {
            return ['ok' => false, 'error' => 'Missing Google token.'];
        }

        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['ok' => false, 'error' => 'We could not verify Google sign-in. Try again.'];
        }

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['email'])) {
            return ['ok' => false, 'error' => 'Google sign-in failed. Try again.'];
        }

        if (($data['aud'] ?? '') !== $clientId) {
            return ['ok' => false, 'error' => 'Google sign-in is not valid for this site.'];
        }

        if (($data['email_verified'] ?? 'false') !== 'true' && ($data['email_verified'] ?? false) !== true) {
            return ['ok' => false, 'error' => 'Your Google email is not verified.'];
        }

        return [
            'ok' => true,
            'google_id' => (string) ($data['sub'] ?? ''),
            'email' => auth_sanitize_email((string) $data['email']),
            'name' => isset($data['name']) ? trim((string) $data['name']) : null,
            'avatar_url' => isset($data['picture']) ? trim((string) $data['picture']) : null,
        ];
    }
}
