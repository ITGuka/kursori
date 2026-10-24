<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;
use Core\Support\Cache;

final class Language
{
    private const CACHE_KEY = 'languages.active';

    /** @var list<array<string, mixed>>|null */
    private ?array $active = null;

    public function __construct(private readonly DB $db, private readonly Cache $cache)
    {
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->select('SELECT * FROM languages ORDER BY sort, id');
    }

    /** @return list<array{code: string, name: string, native_name: string, is_default: int}> აქტიური ენები, ქეშირებული */
    public function active(): array
    {
        return $this->active ??= $this->cache->remember(self::CACHE_KEY, 0, fn () => $this->db->select(
            'SELECT code, name, native_name, is_default FROM languages WHERE is_active = 1 ORDER BY sort, id',
        ));
    }

    /** @return list<string> */
    public function activeCodes(): array
    {
        return array_column($this->active(), 'code');
    }

    public function defaultCode(): string
    {
        foreach ($this->active() as $language) {
            if ($language['is_default']) {
                return $language['code'];
            }
        }
        return (string)($this->active()[0]['code'] ?? config('app.default_locale', 'ka'));
    }

    public function find(int $id): ?array
    {
        return $this->db->first('SELECT * FROM languages WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $id = $this->db->transaction(function (DB $db) use ($data): int {
            $id = $db->insert('languages', [
                'code'        => $data['code'],
                'name'        => $data['name'],
                'native_name' => $data['native_name'],
                'is_active'   => (int)$data['is_active'],
                'sort'        => (int)$data['sort'],
            ]);
            if ($data['is_default']) {
                $this->makeDefault($id);
            }
            return $id;
        });
        $this->forgetCache();
        return $id;
    }

    /** კოდი არ იცვლება — მასზეა მიბმული URL-ები და თარგმანები */
    public function update(int $id, array $data): void
    {
        $this->db->transaction(function (DB $db) use ($id, $data): void {
            $db->update('languages', [
                'name'        => $data['name'],
                'native_name' => $data['native_name'],
                'is_active'   => (int)$data['is_active'],
                'sort'        => (int)$data['sort'],
            ], 'id = ?', [$id]);
            if ($data['is_default']) {
                $this->makeDefault($id);
            }
        });
        $this->forgetCache();
    }

    /** ენა + მისი UI თარგმანები და პარამეტრების მნიშვნელობები */
    public function delete(int $id, string $code): void
    {
        $this->db->transaction(function (DB $db) use ($id, $code): void {
            $db->delete('translation_values', 'lang = ?', [$code]);
            $db->delete('settings', 'lang = ?', [$code]);
            $db->delete('languages', 'id = ?', [$id]);
        });
        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        $this->active = null;
        $this->cache->forget(self::CACHE_KEY);
    }

    /** ძირითადი ენა ყოველთვის ერთია და აქტიური */
    private function makeDefault(int $id): void
    {
        $this->db->query('UPDATE languages SET is_default = (id = ?), is_active = IF(id = ?, 1, is_active)', [$id, $id]);
    }
}
