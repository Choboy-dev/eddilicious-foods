<?php
/**
 * Stores visitor cookie consent choices for marketing and compliance.
 *
 * Table: cookie_consent_log
 * Location: storage/edilicious.sqlite (same database as users and analytics)
 *
 * Use marketing = 1 rows for:
 * - Email campaign audience exports (visitors who opted in)
 * - Checking if you may set ad/remarketing pixels on return visits
 * - Auditing when and what someone agreed to
 */

require_once dirname(__DIR__) . '/auth/database.php';

final class CookieConsentRepository
{
    public static function migrate(): void
    {
        $pdo = auth_database();

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS cookie_consent_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                visitor_id TEXT NOT NULL,
                necessary INTEGER NOT NULL DEFAULT 1,
                functional INTEGER NOT NULL DEFAULT 0,
                analytics INTEGER NOT NULL DEFAULT 0,
                marketing INTEGER NOT NULL DEFAULT 0,
                ip_hash TEXT,
                user_agent TEXT,
                page_path TEXT,
                created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )'
        );

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_cookie_consent_visitor ON cookie_consent_log(visitor_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_cookie_consent_marketing ON cookie_consent_log(marketing, created_at)');

        self::ensureColumn($pdo, 'consent_version', 'TEXT');
        self::ensureColumn($pdo, 'action', 'TEXT');
    }

    private static function ensureColumn(PDO $pdo, string $column, string $type): void
    {
        $stmt = $pdo->query('PRAGMA table_info(cookie_consent_log)');
        $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN, 1) : [];

        if (is_array($columns) && in_array($column, $columns, true)) {
            return;
        }

        $pdo->exec('ALTER TABLE cookie_consent_log ADD COLUMN ' . $column . ' ' . $type);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok: bool, error?: string}
     */
    public static function record(array $data): array
    {
        self::migrate();

        $visitorId = trim((string) ($data['visitor_id'] ?? ''));
        if ($visitorId === '') {
            return ['ok' => false, 'error' => 'Visitor id is required.'];
        }

        $pdo = auth_database();
        $stmt = $pdo->prepare(
            'INSERT INTO cookie_consent_log (
                visitor_id,
                necessary,
                functional,
                analytics,
                marketing,
                ip_hash,
                user_agent,
                page_path,
                consent_version,
                action
            ) VALUES (
                :visitor_id,
                1,
                :functional,
                :analytics,
                :marketing,
                :ip_hash,
                :user_agent,
                :page_path,
                :consent_version,
                :action
            )'
        );

        $stmt->execute([
            ':visitor_id' => substr($visitorId, 0, 64),
            ':functional' => !empty($data['functional']) ? 1 : 0,
            ':analytics' => !empty($data['analytics']) ? 1 : 0,
            ':marketing' => !empty($data['marketing']) ? 1 : 0,
            ':ip_hash' => self::ipHash(),
            ':user_agent' => substr((string) ($data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 255),
            ':page_path' => substr((string) ($data['page_path'] ?? ''), 0, 500),
            ':consent_version' => substr((string) ($data['consent_version'] ?? ''), 0, 32),
            ':action' => substr((string) ($data['action'] ?? 'save'), 0, 32),
        ]);

        return ['ok' => true];
    }

    /**
     * Latest marketing opt-in for a visitor (if you need it server-side later).
     *
     * @return array<string, mixed>|null
     */
    public static function latestForVisitor(string $visitorId): ?array
    {
        self::migrate();

        $visitorId = trim($visitorId);
        if ($visitorId === '') {
            return null;
        }

        $pdo = auth_database();
        $stmt = $pdo->prepare(
            'SELECT visitor_id, necessary, functional, analytics, marketing, created_at
             FROM cookie_consent_log
             WHERE visitor_id = :visitor_id
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([':visitor_id' => $visitorId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    private static function ipHash(): ?string
    {
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        if ($ip === '') {
            return null;
        }

        return hash('sha256', $ip);
    }
}
