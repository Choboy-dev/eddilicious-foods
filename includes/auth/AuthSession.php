<?php

class AuthSession
{
    public static function user(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }

        return UserRepository::findById((int) $id);
    }

    public static function isLoggedIn(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();

        return $user !== null && auth_is_admin_email((string) $user['email']);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function entryUrl(): string
    {
        return self::isLoggedIn() ? 'account.php' : 'signup.php';
    }

    public static function requireGuest(string $redirectTo = 'account.php'): void
    {
        if (self::isLoggedIn()) {
            header('Location: ' . $redirectTo, true, 302);
            exit;
        }
    }

    public static function requireUser(string $redirectTo = 'signup.php'): void
    {
        if (!self::isLoggedIn()) {
            $target = $redirectTo;
            $return = $_SERVER['REQUEST_URI'] ?? 'account.php';
            if ($return && $return !== '/') {
                $target .= '?redirect=' . urlencode($return);
            }
            header('Location: ' . $target, true, 302);
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::isLoggedIn()) {
            $return = $_SERVER['REQUEST_URI'] ?? '';
            $target = 'signin.php?redirect=' . urlencode(auth_admin_safe_redirect($return));
            header('Location: ' . $target, true, 302);
            exit;
        }

        if (!self::isAdmin()) {
            header('Location: ../account.php', true, 302);
            exit;
        }
    }
}
