<?php
declare(strict_types=1);

namespace Core\I18n;

use Closure;

/**
 * UI ტექსტები: t('nav.contact'), t('footer.rights', ['year' => 2026]) → ":year" ჩანაცვლდება.
 * ტექსტებს აწვდის loader (ენა → ['nav.contact' => '...']); წყარო (ბაზა, ფაილი) პროექტზეა დამოკიდებული.
 * აკლია მიმდინარე ენაზე → fallback ენა → თავად გასაღები.
 */
final class Translator
{
    /** @var array<string, array<string, string>> */
    private array $loaded = [];

    /** @param Closure(string): array<string, string> $loader */
    public function __construct(private readonly Closure $loader, private string $fallback)
    {
    }

    public function setFallback(string $locale): void
    {
        $this->fallback = $locale;
    }

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale ??= $this->fallback;
        $line = $this->lines($locale)[$key] ?? null;
        if (($line === null || $line === '') && $locale !== $this->fallback) {
            $line = $this->lines($this->fallback)[$key] ?? null;
        }
        if ($line === null || $line === '') {
            return $key;
        }

        if ($replace !== []) {
            $pairs = [];
            foreach ($replace as $name => $value) {
                $pairs[':' . $name] = (string)$value;
            }
            // გრძელი სახელები პირველი, რომ ":name" არ დაარღვიოს ":name_full"
            uksort($pairs, fn ($a, $b) => strlen($b) <=> strlen($a));
            $line = strtr($line, $pairs);
        }
        return $line;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        return ($this->lines($locale ?? $this->fallback)[$key] ?? '') !== '';
    }

    /** ხელახლა ჩატვირთვა (მაგ. ადმინში შენახვის შემდეგ, იმავე მოთხოვნაში) */
    public function reset(): void
    {
        $this->loaded = [];
    }

    /** @return array<string, string> */
    private function lines(string $locale): array
    {
        return $this->loaded[$locale] ??= ($this->loader)($locale);
    }
}
