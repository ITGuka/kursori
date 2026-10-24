<?php
declare(strict_types=1);

namespace Core\Support;

/**
 * სესია და flash მონაცემები (ერთი შემდეგი მოთხოვნისთვის).
 */
final class Session
{
    public static function start(array $options = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name($options['name'] ?? 'app_session');
        session_set_cookie_params([
            'lifetime' => (int)($options['lifetime'] ?? 0),
            'path'     => $options['path'] ?? '/',
            'secure'   => (bool)($options['secure'] ?? (($_SERVER['HTTPS'] ?? 'off') !== 'off')),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
        self::ageFlash();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::forget($key);
        return $value;
    }

    /** მნიშვნელობა ხელმისაწვდომია მიმდინარე და შემდეგ მოთხოვნაზე */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
        $_SESSION['_flash']['new'][] = $key;
    }

    /** login/logout-ისას — session fixation-ისგან დაცვა */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function invalidate(): void
    {
        $_SESSION = [];
        self::regenerate();
    }

    private static function ageFlash(): void
    {
        foreach ($_SESSION['_flash']['old'] ?? [] as $key) {
            unset($_SESSION[$key]);
        }
        $_SESSION['_flash'] = ['old' => $_SESSION['_flash']['new'] ?? [], 'new' => []];
    }
}
