<?php
declare(strict_types=1);

namespace Core\Support;

final class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        $token = Session::get(self::KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }
        return $token;
    }

    public static function verify(mixed $token): bool
    {
        return is_string($token) && $token !== '' && hash_equals(self::token(), $token);
    }
}
