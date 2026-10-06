<?php

class UserRepository
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => auth_sanitize_email($email)]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findByGoogleId(string $googleId): ?array
    {
        $stmt = auth_database()->prepare('SELECT * FROM users WHERE google_id = :google_id LIMIT 1');
        $stmt->execute(['google_id' => $googleId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function createFromEmail(string $email): array
    {
        $now = gmdate('c');
        $email = auth_sanitize_email($email);

        $stmt = auth_database()->prepare(
            'INSERT INTO users (email, auth_provider, created_at, last_login_at)
             VALUES (:email, "email", :created_at, :last_login_at)'
        );
        $stmt->execute([
            'email' => $email,
            'created_at' => $now,
            'last_login_at' => $now,
        ]);

        return self::findById((int) auth_database()->lastInsertId());
    }

    public static function upsertFromGoogle(string $googleId, string $email, ?string $name, ?string $avatarUrl): array
    {
        $email = auth_sanitize_email($email);
        $now = gmdate('c');

        $existing = self::findByGoogleId($googleId);
        if ($existing) {
            self::touchLogin((int) $existing['id'], $name, $avatarUrl);
            return self::findById((int) $existing['id']);
        }

        $byEmail = self::findByEmail($email);
        if ($byEmail) {
            $stmt = auth_database()->prepare(
                'UPDATE users
                 SET google_id = :google_id,
                     auth_provider = "google",
                     name = COALESCE(:name, name),
                     avatar_url = COALESCE(:avatar_url, avatar_url),
                     last_login_at = :last_login_at
                 WHERE id = :id'
            );
            $stmt->execute([
                'google_id' => $googleId,
                'name' => $name,
                'avatar_url' => $avatarUrl,
                'last_login_at' => $now,
                'id' => $byEmail['id'],
            ]);

            return self::findById((int) $byEmail['id']);
        }

        $stmt = auth_database()->prepare(
            'INSERT INTO users (email, name, avatar_url, auth_provider, google_id, created_at, last_login_at)
             VALUES (:email, :name, :avatar_url, "google", :google_id, :created_at, :last_login_at)'
        );
        $stmt->execute([
            'email' => $email,
            'name' => $name,
            'avatar_url' => $avatarUrl,
            'google_id' => $googleId,
            'created_at' => $now,
            'last_login_at' => $now,
        ]);

        return self::findById((int) auth_database()->lastInsertId());
    }

    public static function touchLogin(int $id, ?string $name = null, ?string $avatarUrl = null): void
    {
        $stmt = auth_database()->prepare(
            'UPDATE users
             SET last_login_at = :last_login_at,
                 name = COALESCE(:name, name),
                 avatar_url = COALESCE(:avatar_url, avatar_url)
             WHERE id = :id'
        );
        $stmt->execute([
            'last_login_at' => gmdate('c'),
            'name' => $name,
            'avatar_url' => $avatarUrl,
            'id' => $id,
        ]);
    }
}
