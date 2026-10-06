<?php
/**
 * SQLite database for users and OTP codes.
 */

function auth_database_path(): string
{
    return dirname(__DIR__, 2) . '/storage/edilicious.sqlite';
}

function auth_database(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dir = dirname(auth_database_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    } elseif (!is_writable($dir)) {
        @chmod($dir, 0777);
    }

    $dbPath = auth_database_path();
    if (is_file($dbPath) && !is_writable($dbPath)) {
        @chmod($dbPath, 0666);
    }

    $pdo = new PDO('sqlite:' . auth_database_path(), null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function auth_database_migrate(): void
{
    $pdo = auth_database();

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE COLLATE NOCASE,
            name TEXT,
            avatar_url TEXT,
            auth_provider TEXT NOT NULL DEFAULT "email",
            google_id TEXT UNIQUE,
            created_at TEXT NOT NULL,
            last_login_at TEXT
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS otp_codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL COLLATE NOCASE,
            code_hash TEXT NOT NULL,
            purpose TEXT NOT NULL,
            attempts INTEGER NOT NULL DEFAULT 0,
            expires_at TEXT NOT NULL,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_otp_email_created ON otp_codes(email, created_at)');

    require_once __DIR__ . '/../payments/OrderRepository.php';
    OrderRepository::migrate();

    require_once __DIR__ . '/../admin/CatalogAdminRepository.php';
    require_once __DIR__ . '/../admin/CouponAdminRepository.php';
    require_once __DIR__ . '/../newsletter/NewsletterRepository.php';
    require_once __DIR__ . '/../analytics/AnalyticsRepository.php';
    require_once __DIR__ . '/../analytics/CookieConsentRepository.php';
    CatalogAdminRepository::migrate();
    CouponAdminRepository::migrate();
    NewsletterRepository::migrate();
    AnalyticsRepository::migrate();
    CookieConsentRepository::migrate();

    require_once __DIR__ . '/../reviews/ProductReviewRepository.php';
    ProductReviewRepository::migrate();
}
