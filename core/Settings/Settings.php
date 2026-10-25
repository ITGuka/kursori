<?php
declare(strict_types=1);

namespace Core\Settings;

use Core\Database\DB;
use Core\Support\Cache;
use Core\Support\Crypt;

/**
 * საიტის პარამეტრები: განსაზღვრებები კოდშია (config/settings.php — ჯგუფები, ველის ტიპები, default),
 * მნიშვნელობები — ბაზაში (setting_key, lang, value). თარგმნადი ველისთვის lang = ენის კოდი, სხვაგვარად ''.
 *
 *   $settings->get('contact.phone');            // არათარგმნადი
 *   $settings->get('general.site_name', 'en');  // თარგმნადი; ცარიელი → fallback ენა → default
 */
final class Settings
{
    private const CACHE_KEY = 'settings.values';

    /** @var array<string, array<string, string>>|null setting_key => [lang => value] */
    private ?array $values = null;

    /** @var array<string, array> setting_key => განსაზღვრება */
    private array $fields = [];

    /**
     * @param array<string, array{label?: string, fields: array<string, array>}> $groups
     */
    public function __construct(
        private readonly DB $db,
        private readonly Cache $cache,
        private readonly array $groups,
        private string $fallback,
        private readonly string $table = 'settings',
    ) {
        foreach ($groups as $group => $definition) {
            foreach ($definition['fields'] ?? [] as $name => $field) {
                $this->fields["$group.$name"] = $field + ['type' => 'text', 'translatable' => false];
            }
        }
    }

    public function setFallback(string $locale): void
    {
        $this->fallback = $locale;
    }

    public function get(string $key, ?string $locale = null): mixed
    {
        $field = $this->fields[$key] ?? null;
        $stored = $this->values()[$key] ?? [];

        if (!empty($field['translatable'])) {
            $locale ??= $this->fallback;
            $value = $stored[$locale] ?? '';
            if ($value === '' && $locale !== $this->fallback) {
                $value = $stored[$this->fallback] ?? '';
            }
        } else {
            $value = $stored[''] ?? '';
        }

        if ($value === '') {
            $default = $field['default'] ?? null;
            return is_array($default) ? ($default[$locale ?? $this->fallback] ?? $default[$this->fallback] ?? null) : $default;
        }
        return match ($field['type'] ?? '') {
            'toggle'   => $value === '1',
            'password' => Crypt::decrypt($value),
            default    => $value,
        };
    }

    /** შენახული მნიშვნელობა default-ისა და fallback-ის გარეშე (ადმინის ფორმისთვის) */
    public function raw(string $key, string $locale = ''): string
    {
        return $this->values()[$key][$locale] ?? '';
    }

    /** @return array<string, array{label?: string, fields: array<string, array>}> */
    public function groups(): array
    {
        return $this->groups;
    }

    public function field(string $key): ?array
    {
        return $this->fields[$key] ?? null;
    }

    /**
     * @param array<string, string|array<string, string>> $values setting_key => value ან [lang => value]
     */
    public function save(array $values): void
    {
        $this->db->transaction(function (DB $db) use ($values): void {
            foreach ($values as $key => $value) {
                if (!isset($this->fields[$key])) {
                    continue;
                }
                foreach (is_array($value) ? $value : ['' => $value] as $lang => $text) {
                    $db->query(
                        'INSERT INTO ' . DB::ident($this->table) . ' (setting_key, lang, value) VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE value = VALUES(value)',
                        [$key, (string)$lang, (string)$text],
                    );
                }
            }
        });
        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        $this->values = null;
        $this->cache->forget(self::CACHE_KEY);
    }

    /** @return array<string, array<string, string>> */
    private function values(): array
    {
        return $this->values ??= $this->cache->remember(self::CACHE_KEY, 0, function (): array {
            $values = [];
            foreach ($this->db->select('SELECT setting_key, lang, value FROM ' . DB::ident($this->table)) as $row) {
                $values[$row['setting_key']][$row['lang']] = (string)$row['value'];
            }
            return $values;
        });
    }
}
