<?php
declare(strict_types=1);

namespace Core\Support;

/**
 * ფაილური ქეში storage/cache/data-ში. მნიშვნელობები ინახება PHP ფაილებად (var_export),
 * ამიტომ opcache-ით სწრაფად იკითხება. მხოლოდ სკალარები და მასივები — ობიექტები არა.
 */
final class Cache
{
    public function __construct(private readonly string $path)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->file($key);
        if (!is_file($file)) {
            return $default;
        }

        $entry = @include $file;
        if (!is_array($entry) || ($entry['expires'] !== 0 && $entry['expires'] < time())) {
            return $default;
        }
        return $entry['value'];
    }

    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
    }

    /** @param int $ttl წამები; 0 — ვადის გარეშე (სანამ forget/flush არ გამოიძახება) */
    public function put(string $key, mixed $value, int $ttl = 0): void
    {
        if (!is_dir($this->path)) {
            @mkdir($this->path, 0775, true);
        }

        $file = $this->file($key);
        $entry = ['expires' => $ttl > 0 ? time() + $ttl : 0, 'value' => $value];
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($tmp, '<?php return ' . var_export($entry, true) . ';' . PHP_EOL, LOCK_EX);
        rename($tmp, $file);
        $this->invalidateOpcache($file);
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key, $this);
        if ($value !== $this) {
            return $value;
        }
        $value = $callback();
        $this->put($key, $value, $ttl);
        return $value;
    }

    public function forget(string $key): void
    {
        $file = $this->file($key);
        if (is_file($file)) {
            @unlink($file);
            $this->invalidateOpcache($file);
        }
    }

    /** ყველა ჩანაწერის წაშლა, ან მხოლოდ prefix-ით დაწყებულის (მაგ. "translations.") */
    public function flush(string $prefix = ''): void
    {
        $pattern = $this->path . DIRECTORY_SEPARATOR . ($prefix === '' ? '' : self::slug($prefix)) . '*.php';
        foreach (glob($pattern) ?: [] as $file) {
            @unlink($file);
            $this->invalidateOpcache($file);
        }
    }

    private function file(string $key): string
    {
        // წასაკითხი სახელი prefix-ით flush-ისთვის + hash, რომ სხვადასხვა გასაღები არ დაემთხვეს
        return $this->path . DIRECTORY_SEPARATOR . self::slug($key) . '-' . substr(md5($key), 0, 8) . '.php';
    }

    private static function slug(string $key): string
    {
        return (string)preg_replace('/[^A-Za-z0-9_.-]/', '_', $key);
    }

    private function invalidateOpcache(string $file): void
    {
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }
    }
}
