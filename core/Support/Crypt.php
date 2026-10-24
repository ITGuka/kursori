<?php
declare(strict_types=1);

namespace Core\Support;

use RuntimeException;

/**
 * საიდუმლოების (მაგ. SMTP პაროლი) დაშიფვრა ბაზაში შესანახად: libsodium secretbox, გასაღები — .env APP_KEY.
 * გასაღების შექმნა: php bin/console key:generate
 */
final class Crypt
{
    private const PREFIX = 'enc:';

    public static function encrypt(string $value): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return self::PREFIX . base64_encode($nonce . sodium_crypto_secretbox($value, $nonce, self::key()));
    }

    /** დაზიანებული ან სხვა გასაღებით დაშიფრული მნიშვნელობა → '' */
    public static function decrypt(string $payload): string
    {
        if (!str_starts_with($payload, self::PREFIX)) {
            return '';
        }
        $raw = base64_decode(substr($payload, strlen(self::PREFIX)), true);
        if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return '';
        }
        $plain = sodium_crypto_secretbox_open(
            substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            self::key(),
        );
        return $plain === false ? '' : $plain;
    }

    public static function generateKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
    }

    private static function key(): string
    {
        $key = (string)env('APP_KEY', '');
        $key = str_starts_with($key, 'base64:') ? (string)base64_decode(substr($key, 7), true) : '';
        if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException('APP_KEY is missing or invalid. Run: php bin/console key:generate');
        }
        return $key;
    }
}
