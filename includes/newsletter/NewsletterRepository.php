<?php

class NewsletterRepository
{
    public static function migrate(): void
    {
        auth_database()->exec(
            'CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE COLLATE NOCASE,
                created_at TEXT NOT NULL
            )'
        );
    }

    /**
     * @return array{ok: true, is_new: bool}
     */
    public static function subscribe(string $email): array
    {
        $email = auth_sanitize_email($email);
        $now = gmdate('c');

        $existing = auth_database()->prepare('SELECT id FROM newsletter_subscribers WHERE email = :email LIMIT 1');
        $existing->execute(['email' => $email]);

        if ($existing->fetch()) {
            return ['ok' => true, 'is_new' => false];
        }

        $stmt = auth_database()->prepare(
            'INSERT INTO newsletter_subscribers (email, created_at) VALUES (:email, :created_at)'
        );
        $stmt->execute([
            'email' => $email,
            'created_at' => $now,
        ]);

        return ['ok' => true, 'is_new' => true];
    }
}
