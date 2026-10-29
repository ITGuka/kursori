<?php
declare(strict_types=1);

namespace App\Models;

use App\Blocks\BlockResolver;
use Core\Database\DB;

/**
 * კოლექციების ჩანაწერები (config/collections.php): collection_items + collection_item_translations.
 * ძირითადი ველები (სათაური, slug, სურათი, კატეგორია) — სვეტებში, დამატებითი — settings/content JSON-ში
 * (ბლოკების ფორმატი: BlockInput / BlockResolver).
 */
final class Collection
{
    public function __construct(private readonly DB $db, private readonly Category $categories)
    {
    }

    // ---- ტიპები (კონფიგი) ----

    /** @return array<string, array> */
    public static function types(): array
    {
        return (array)config('collections', []);
    }

    public static function type(string $type): ?array
    {
        $definition = self::types()[$type] ?? null;
        return $definition === null ? null : $definition + ['fields' => [], 'categories' => false, 'public' => false, 'route' => [],
                                                          'title_label' => 'სათაური', 'singular' => $definition['label'] ?? $type];
    }

    /** საჯარო გვერდის URL-ის ნაწილი ენაზე: route[lang] → route[ძირითადი] → კოლექციის გასაღები */
    public static function routeSlug(string $type, string $lang, string $defaultLang): string
    {
        $route = (array)(self::type($type)['route'] ?? []);
        return (string)($route[$lang] ?? $route[$defaultLang] ?? $type);
    }

    /** @return string|null კოლექცია, რომლის საჯარო მისამართიც ენაზე $slug-ია */
    public static function typeByRoute(string $lang, string $slug, string $defaultLang): ?string
    {
        foreach (self::types() as $type => $definition) {
            if (!empty($definition['public']) && self::routeSlug($type, $lang, $defaultLang) === $slug) {
                return $type;
            }
        }
        return null;
    }

    // ---- ადმინი ----

    /**
     * @param array{q?: string, category?: int} $filters
     * @return array{rows: list<array>, total: int}
     */
    public function paginate(string $type, array $filters, string $lang, int $page, int $perPage): array
    {
        $where = ['i.collection = ?'];
        $bindings = [$type];
        if (($filters['q'] ?? '') !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM collection_item_translations s WHERE s.item_id = i.id AND s.title LIKE ?)';
            $bindings[] = '%' . addcslashes($filters['q'], '%_\\') . '%';
        }
        if (!empty($filters['category'])) {
            $ids = $this->categories->withChildren((int)$filters['category']);
            $where[] = 'i.category_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')';
            array_push($bindings, ...$ids);
        }
        $whereSql = implode(' AND ', $where);

        $total = (int)$this->db->value("SELECT COUNT(*) FROM collection_items i WHERE $whereSql", $bindings);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $this->db->select(
            "SELECT i.*,
                    COALESCE((SELECT title FROM collection_item_translations t WHERE t.item_id = i.id AND t.lang = ?),
                             (SELECT title FROM collection_item_translations t WHERE t.item_id = i.id LIMIT 1), CONCAT('#', i.id)) AS title,
                    (SELECT GROUP_CONCAT(lang) FROM collection_item_translations t WHERE t.item_id = i.id) AS langs,
                    (SELECT name FROM category_translations c WHERE c.category_id = i.category_id ORDER BY c.lang = ? DESC LIMIT 1) AS category_name
             FROM collection_items i WHERE $whereSql ORDER BY i.sort, i.id LIMIT $perPage OFFSET $offset",
            [$lang, $lang, ...$bindings],
        );
        foreach ($rows as &$row) {
            $row['langs'] = $row['langs'] ? explode(',', $row['langs']) : [];
        }
        return ['rows' => $rows, 'total' => $total];
    }

    /** @return array|null ყოველს: settings (array), translations (lang => title, slug, content array) */
    public function find(int $id): ?array
    {
        $item = $id > 0 ? $this->db->first('SELECT * FROM collection_items WHERE id = ?', [$id]) : null;
        if ($item === null) {
            return null;
        }
        $item['settings'] = self::decode($item['settings']);
        $item['translations'] = [];
        foreach ($this->db->select('SELECT * FROM collection_item_translations WHERE item_id = ?', [$id]) as $row) {
            $row['content'] = self::decode($row['content']);
            $item['translations'][$row['lang']] = $row;
        }
        return $item;
    }

    public function slugExists(string $type, string $lang, string $slug, ?int $exceptId = null): bool
    {
        return (bool)$this->db->value(
            'SELECT 1 FROM collection_item_translations t JOIN collection_items i ON i.id = t.item_id
             WHERE i.collection = ? AND t.lang = ? AND t.slug = ?' . ($exceptId ? ' AND i.id <> ?' : ''),
            $exceptId ? [$type, $lang, $slug, $exceptId] : [$type, $lang, $slug],
        );
    }

    /**
     * @param array{category_id: ?int, image_id: ?int, is_active: bool} $core
     * @param array<string, array{title: string, slug: string}> $translations ცარიელი title → თარგმანი იშლება
     * @param array<string, array> $content დამატებითი თარგმნადი ველები ენაზე
     * @param list<int> $mediaIds
     */
    public function save(?int $id, string $type, array $core, array $settings, array $translations, array $content, array $mediaIds): int
    {
        return $this->db->transaction(function (DB $db) use ($id, $type, $core, $settings, $translations, $content, $mediaIds): int {
            $media = array_values(array_unique(array_filter([...$mediaIds, $core['image_id']])));
            $row = [
                'category_id' => $core['category_id'],
                'image_id'    => $core['image_id'],
                'is_active'   => (int)$core['is_active'],
                'settings'    => self::encode($settings),
                'media_ids'   => json_encode(array_map('intval', $media)),
            ];
            if ($id === null) {
                $row['sort'] = (int)$db->value('SELECT COALESCE(MAX(sort), 0) + 1 FROM collection_items WHERE collection = ?', [$type]);
                $id = $db->insert('collection_items', $row + ['collection' => $type]);
            } else {
                $db->update('collection_items', $row, 'id = ?', [$id]);
            }
            foreach ($translations as $lang => $t) {
                if ($t['title'] === '') {
                    $db->delete('collection_item_translations', 'item_id = ? AND lang = ?', [$id, $lang]);
                    continue;
                }
                $db->query(
                    'INSERT INTO collection_item_translations (item_id, lang, title, slug, content) VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), content = VALUES(content)',
                    [$id, $lang, $t['title'], $t['slug'], self::encode($content[$lang] ?? [])],
                );
            }
            return $id;
        });
    }

    /** ერთი ენის თარგმანის დამატება/ჩანაცვლება არსებულ ჩანაწერზე (სხვა ენები და ძირითადი ველები უცვლელია) */
    public function saveTranslation(int $id, string $lang, string $title, string $slug, array $content): void
    {
        $this->db->query(
            'INSERT INTO collection_item_translations (item_id, lang, title, slug, content) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), content = VALUES(content)',
            [$id, $lang, $title, $slug, self::encode($content)],
        );
    }

    public function setActive(int $id, bool $active): void
    {
        $this->db->update('collection_items', ['is_active' => (int)$active], 'id = ?', [$id]);
    }

    /** @param list<int> $ids ახალი რიგი (გვერდზე ნაჩვენები ჩანაწერები); სხვა კოლექციის id-ები იგნორირდება */
    public function reorder(string $type, array $ids, int $offset = 0): void
    {
        $this->db->transaction(function (DB $db) use ($type, $ids, $offset): void {
            foreach (array_values($ids) as $position => $id) {
                $db->update('collection_items', ['sort' => $offset + $position + 1], 'id = ? AND collection = ?', [(int)$id, $type]);
            }
        });
    }

    public function delete(int $id): void
    {
        $this->db->delete('collection_items', 'id = ?', [$id]);
    }

    // ---- ფრონტი ----

    /** @return array|null აქტიური ჩანაწერი slug-ით (resolve-ის ფორმატით) */
    public function findBySlug(string $type, string $lang, string $slug, string $defaultLang): ?array
    {
        $id = $this->db->value(
            'SELECT i.id FROM collection_items i JOIN collection_item_translations t ON t.item_id = i.id
             WHERE i.collection = ? AND i.is_active = 1 AND t.lang = ? AND t.slug = ?',
            [$type, $lang, $slug],
        );
        return $id === null ? null : $this->resolve($this->find((int)$id), $lang, $defaultLang);
    }

    /**
     * აქტიური ჩანაწერები ბლოკისთვის/სიისთვის მიმდინარე ენაზე (თარგმანი ენაზე არ აქვს → არ ჩანს).
     * @return list<array> resolve-ის ფორმატით
     */
    public function list(string $type, ?int $categoryId, int $limit, string $lang, string $defaultLang): array
    {
        $where = 'i.collection = ? AND i.is_active = 1';
        $bindings = [$lang, $type];
        if ($categoryId) {
            $ids = $this->categories->withChildren($categoryId);
            $where .= ' AND i.category_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')';
            array_push($bindings, ...$ids);
        }
        $order = (self::type($type)['order'] ?? '') === 'newest' ? 'i.created_at DESC, i.id DESC' : 'i.sort, i.id';
        $ids = $this->db->column(
            "SELECT i.id FROM collection_items i JOIN collection_item_translations t ON t.item_id = i.id AND t.lang = ?
             WHERE $where ORDER BY $order" . ($limit > 0 ? ' LIMIT ' . $limit : ''),
            $bindings,
        );
        return array_map(fn (array $item) => $this->resolve($item, $lang, $defaultLang), $this->findMany(array_map('intval', $ids)));
    }

    /**
     * რამდენიმე ჩანაწერი თარგმანებით ორ query-ში (find()-ის ფორმატით), $ids-ის რიგით.
     * @param list<int> $ids
     * @return list<array>
     */
    public function findMany(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $items = [];
        foreach ($this->db->select("SELECT * FROM collection_items WHERE id IN ($placeholders)", $ids) as $row) {
            $row['settings'] = self::decode($row['settings']);
            $row['translations'] = [];
            $items[(int)$row['id']] = $row;
        }
        foreach ($this->db->select("SELECT * FROM collection_item_translations WHERE item_id IN ($placeholders)", $ids) as $row) {
            $row['content'] = self::decode($row['content']);
            $items[(int)$row['item_id']]['translations'][$row['lang']] = $row;
        }
        return array_values(array_filter(array_map(fn (int $id) => $items[$id] ?? null, $ids)));
    }

    /** საჯარო URL ენაზე ('' — კოლექცია საჯარო არ არის ან თარგმანი არ აქვს) */
    public function url(array $item, string $lang, string $defaultLang): string
    {
        $type = self::type($item['collection']);
        $slug = $item['translations'][$lang]['slug'] ?? null;
        if (empty($type['public']) || $slug === null) {
            return '';
        }
        return url($lang . '/' . self::routeSlug($item['collection'], $lang, $defaultLang) . '/' . $slug);
    }

    /**
     * ჩანაწერი → view-ის მონაცემები ენაზე: id, title, slug, image, category_id, category, url, date (შექმნის, Y-m-d) + დამატებითი ველები.
     */
    public function resolve(array $item, string $lang, string $defaultLang): array
    {
        $type = self::type($item['collection']) ?? ['fields' => []];
        $translation = $item['translations'][$lang] ?? $item['translations'][$defaultLang] ?? ['title' => '', 'slug' => '', 'content' => []];
        $fields = app(BlockResolver::class)->resolve(
            $type['fields'],
            $item['settings'],
            $item['translations'][$lang]['content'] ?? [],
            $item['translations'][$defaultLang]['content'] ?? [],
            $lang,
        );
        return [
            'id'          => (int)$item['id'],
            'collection'  => $item['collection'],
            'title'       => $translation['title'],
            'slug'        => $translation['slug'],
            'image'       => $item['image_id'] !== null ? (int)$item['image_id'] : null,
            'category_id' => $item['category_id'] !== null ? (int)$item['category_id'] : null,
            'category'    => $item['category_id'] !== null ? $this->categories->name((int)$item['category_id'], $lang, $defaultLang) : '',
            'url'         => $this->url($item, $lang, $defaultLang),
            'date'        => substr((string)($item['created_at'] ?? ''), 0, 10),
            'alternates'  => array_filter(array_map(fn ($l) => $this->url($item, $l, $defaultLang), array_combine(array_keys($item['translations']), array_keys($item['translations'])))),
        ] + $fields;
    }

    private static function decode(?string $json): array
    {
        return $json ? (array)json_decode($json, true) : [];
    }

    private static function encode(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
