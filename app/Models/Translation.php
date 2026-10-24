<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;
use Core\Support\Cache;

/**
 * UI თარგმანები (translation_keys + translation_values). Translator-ს აწვდის ერთ ენას ქეშიდან.
 */
final class Translation
{
    public const GROUP_PATTERN = '/^[a-z][a-z0-9_]{0,49}$/';
    public const KEY_PATTERN = '/^[a-z0-9][a-z0-9_.]{0,149}$/';

    public function __construct(private readonly DB $db, private readonly Cache $cache)
    {
    }

    /** @return array<string, string> "group.key" => value */
    public function forLocale(string $locale): array
    {
        return $this->cache->remember('translations.' . $locale, 0, function () use ($locale): array {
            $lines = [];
            $rows = $this->db->select(
                'SELECT k.`group`, k.`key`, v.value FROM translation_keys k
                 JOIN translation_values v ON v.key_id = k.id AND v.lang = ?',
                [$locale],
            );
            foreach ($rows as $row) {
                $lines[$row['group'] . '.' . $row['key']] = (string)$row['value'];
            }
            return $lines;
        });
    }

    /** @return list<string> */
    public function groups(): array
    {
        return $this->db->column('SELECT DISTINCT `group` FROM translation_keys ORDER BY `group`');
    }

    /**
     * @param list<string> $locales ენები, რომელთა მნიშვნელობებიც დაემატება ყოველ სტრიქონს (values[lang])
     * @return array{rows: list<array>, total: int}
     */
    public function paginate(array $locales, ?string $group, string $search, bool $missingOnly, int $page, int $perPage): array
    {
        $where = ['1 = 1'];
        $bindings = [];
        if ($group !== null && $group !== '') {
            $where[] = 'k.`group` = ?';
            $bindings[] = $group;
        }
        if ($search !== '') {
            $where[] = '(CONCAT(k.`group`, \'.\', k.`key`) LIKE ? OR EXISTS (SELECT 1 FROM translation_values sv WHERE sv.key_id = k.id AND sv.value LIKE ?))';
            $like = '%' . addcslashes($search, '%_\\') . '%';
            array_push($bindings, $like, $like);
        }
        if ($missingOnly && $locales !== []) {
            $placeholders = implode(', ', array_fill(0, count($locales), '?'));
            $where[] = "(SELECT COUNT(*) FROM translation_values mv WHERE mv.key_id = k.id AND mv.value <> '' AND mv.lang IN ($placeholders)) < ?";
            array_push($bindings, ...$locales);
            $bindings[] = count($locales);
        }
        $whereSql = implode(' AND ', $where);

        $total = (int)$this->db->value("SELECT COUNT(*) FROM translation_keys k WHERE $whereSql", $bindings);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $this->db->select(
            "SELECT k.* FROM translation_keys k WHERE $whereSql ORDER BY k.`group`, k.`key` LIMIT $perPage OFFSET $offset",
            $bindings,
        );

        if ($rows !== []) {
            $ids = array_column($rows, 'id');
            $placeholders = implode(', ', array_fill(0, count($ids), '?'));
            $values = [];
            foreach ($this->db->select("SELECT key_id, lang, value FROM translation_values WHERE key_id IN ($placeholders)", $ids) as $v) {
                $values[$v['key_id']][$v['lang']] = $v['value'];
            }
            foreach ($rows as &$row) {
                $row['values'] = $values[$row['id']] ?? [];
            }
            unset($row);
        }

        return ['rows' => $rows, 'total' => $total];
    }

    public function exists(string $group, string $key): bool
    {
        return (bool)$this->db->value('SELECT 1 FROM translation_keys WHERE `group` = ? AND `key` = ?', [$group, $key]);
    }

    public function findKey(int $id): ?array
    {
        return $this->db->first('SELECT * FROM translation_keys WHERE id = ?', [$id]);
    }

    /** @param array<string, string> $values lang => value */
    public function createKey(string $group, string $key, ?string $description, array $values): int
    {
        return $this->db->transaction(function (DB $db) use ($group, $key, $description, $values): int {
            $id = $db->insert('translation_keys', ['group' => $group, 'key' => $key, 'description' => $description]);
            $this->writeValues($id, $values);
            return $id;
        });
    }

    /** @param array<int, array<string, string>> $values key_id => [lang => value] */
    public function saveValues(array $values): void
    {
        $this->db->transaction(function () use ($values): void {
            foreach ($values as $keyId => $byLang) {
                $this->writeValues((int)$keyId, $byLang);
            }
        });
        $this->forgetCache();
    }

    public function deleteKey(int $id): void
    {
        $this->db->delete('translation_keys', 'id = ?', [$id]);
        $this->forgetCache();
    }

    /**
     * გასაღების დამატება, თუ არ არსებობს; არსებულ (ადმინში შეცვლილ) მნიშვნელობებს არ ცვლის.
     * @param array<string, string> $values lang => value
     */
    public function ensure(string $group, string $key, array $values, ?string $description = null): void
    {
        $this->db->query('INSERT IGNORE INTO translation_keys (`group`, `key`, description) VALUES (?, ?, ?)', [$group, $key, $description]);
        $id = (int)$this->db->value('SELECT id FROM translation_keys WHERE `group` = ? AND `key` = ?', [$group, $key]);
        foreach ($values as $lang => $value) {
            $this->db->query('INSERT IGNORE INTO translation_values (key_id, lang, value) VALUES (?, ?, ?)', [$id, $lang, $value]);
        }
    }

    public function forgetCache(): void
    {
        $this->cache->flush('translations.');
    }

    /** @param array<string, string> $values */
    private function writeValues(int $keyId, array $values): void
    {
        foreach ($values as $lang => $value) {
            $this->db->query(
                'INSERT INTO translation_values (key_id, lang, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
                [$keyId, (string)$lang, trim((string)$value)],
            );
        }
    }
}
