<?php

class OtpRepository
{
    private const OTP_LENGTH = 6;
    private const OTP_TTL_SECONDS = 600;
    private const MAX_ATTEMPTS = 5;
    private const RATE_LIMIT_WINDOW_SECONDS = 900;
    private const RATE_LIMIT_MAX_SENDS = 3;

    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    public static function canSend(string $email): bool
    {
        $since = gmdate('c', time() - self::RATE_LIMIT_WINDOW_SECONDS);
        $stmt = auth_database()->prepare(
            'SELECT COUNT(*) AS total FROM otp_codes WHERE email = :email AND created_at >= :since'
        );
        $stmt->execute([
            'email' => auth_sanitize_email($email),
            'since' => $since,
        ]);
        $row = $stmt->fetch();

        return ((int) ($row['total'] ?? 0)) < self::RATE_LIMIT_MAX_SENDS;
    }

    public static function store(string $email, string $code, string $purpose): void
    {
        $email = auth_sanitize_email($email);
        $expires = gmdate('c', time() + self::OTP_TTL_SECONDS);

        $delete = auth_database()->prepare('DELETE FROM otp_codes WHERE email = :email');
        $delete->execute(['email' => $email]);

        $stmt = auth_database()->prepare(
            'INSERT INTO otp_codes (email, code_hash, purpose, attempts, expires_at, created_at)
             VALUES (:email, :code_hash, :purpose, 0, :expires_at, :created_at)'
        );
        $stmt->execute([
            'email' => $email,
            'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            'purpose' => $purpose,
            'expires_at' => $expires,
            'created_at' => gmdate('c'),
        ]);
    }

    public static function verify(string $email, string $code, string $purpose): array
    {
        $email = auth_sanitize_email($email);
        $code = preg_replace('/\D+/', '', $code) ?? '';

        if (strlen($code) !== self::OTP_LENGTH) {
            return ['ok' => false, 'error' => 'Enter the full 6-digit code.'];
        }

        $stmt = auth_database()->prepare(
            'SELECT * FROM otp_codes WHERE email = :email ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['ok' => false, 'error' => 'No code found. Request a new one.'];
        }

        if ($row['purpose'] !== $purpose) {
            return ['ok' => false, 'error' => 'This code is not valid for this step.'];
        }

        if (strtotime($row['expires_at']) < time()) {
            return ['ok' => false, 'error' => 'This code has expired. Request a new one.'];
        }

        if ((int) $row['attempts'] >= self::MAX_ATTEMPTS) {
            return ['ok' => false, 'error' => 'Too many tries. Request a new code.'];
        }

        if (!password_verify($code, $row['code_hash'])) {
            $update = auth_database()->prepare('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = :id');
            $update->execute(['id' => $row['id']]);

            return ['ok' => false, 'error' => 'That code is wrong. Try again.'];
        }

        $delete = auth_database()->prepare('DELETE FROM otp_codes WHERE email = :email');
        $delete->execute(['email' => $email]);

        return ['ok' => true];
    }

    public static function ttlMinutes(): int
    {
        return (int) (self::OTP_TTL_SECONDS / 60);
    }
}
