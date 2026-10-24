<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;
use Core\Support\Cache;
use Core\Support\Url;

/**
 * მენიუები ადგილების მიხედვით (config/menus.php): menus + menu_items (ხე) + menu_item_translations.
 * პუნქტი: გვერდი (URL და სათაური ენის მიხედვით ავტომატურად) ან ბმული.
 */
final class Menu
{
    public const CACHE_PREFIX = 'menus.';

    public function __construct(
        private readonly DB $db,
        private readonly Cache $cache,
        private readonly Page $pages,
    ) {
    }

    /** @return array<string, array{label: string, max_depth: int}> */
    public static function locations(): array
    {
        return (array)config('menus', []);
    }

    /** მენიუს id ადგილისთვის (არ არსებობს → იქმნება) */
    public function idFor(string $location): int
    {
        $id = $this->db->value('SELECT id FROM menus WHERE location = ?', [$location]);
        if ($id !== null) {
            return (int)$id;
        }
        $this->db->query('INSERT IGNORE INTO menus (location) VALUES (?)', [$location]);
        return (int)$this->db->value('SELECT id FROM menus WHERE location = ?', [$location]);
    }

    public function find(int $id): ?array
    {
        return $this->db->first('SELECT * FROM menus WHERE id = ?', [$id]);
    }

    /**
     * ადმინისთვის: ყველა პუნქტი ხედ, ყოველს — translations (lang => label/url) და children.
     * @return list<array>
     */
    public function adminTree(int $menuId): array
    {
        $items = $this->db->select('SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort, id', [$menuId]);
        $translations = [];
        if ($items !== []) {
            $ids = array_column($items, 'id');
            foreach ($this->db->select('SELECT * FROM menu_item_translations WHERE item_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')', $ids) as $row) {
                $translations[$row['item_id']][$row['lang']] = $row;
            }
        }
        foreach ($items as &$item) {
            $item['translations'] = $translations[$item['id']] ?? [];
        }
        return self::nest($items);
    }

    /**
     * ფრონტისთვის: აქტიური პუნქტები მიმდინარე ენაზე (ქეშირებული).
     * გვერდი ამ ენაზე არ არის ან გამოქვეყნებული არ არის → პუნქტი (შვილებით) არ ჩანს.
     * @return list<array{label: string, href: string, new_tab: bool, children: list<array>}>
     */
    public function tree(string $location, string $lang, string $fallbackLang): array
    {
        // href-ში base path-ია (localhost/WEBDEV vs webdev.test), ამიტომ ის გასაღების ნაწილია
        $key = self::CACHE_PREFIX . $location . '.' . $lang . '.' . substr(md5(app()->baseUrlPath()), 0, 6);
        return $this->cache->remember($key, 0, function () use ($location, $lang, $fallbackLang): array {
            $menuId = $this->db->value('SELECT id FROM menus WHERE location = ?', [$location]);
            if ($menuId === null) {
                return [];
            }
            $rows = $this->db->select(
                'SELECT i.*, t.label, t.url AS lang_url, f.label AS fallback_label, f.url AS fallback_url, pt.title AS page_title
                 FROM menu_items i
                 LEFT JOIN menu_item_translations t ON t.item_id = i.id AND t.lang = ?
                 LEFT JOIN menu_item_translations f ON f.item_id = i.id AND f.lang = ?
                 LEFT JOIN page_translations pt ON pt.page_id = i.page_id AND pt.lang = ?
                 WHERE i.menu_id = ? AND i.is_active = 1
                 ORDER BY i.sort, i.id',
                [$lang, $fallbackLang, $lang, $menuId],
            );

            $items = [];
            foreach ($rows as $row) {
                if ($row['type'] === 'page') {
                    $href = $this->pages->url((int)$row['page_id'], $lang);
                    $label = (string)($row['label'] ?: $row['page_title']);
                } else {
                    $raw = (string)($row['lang_url'] ?: $row['url']);
                    $href = Url::isSafeHref($raw) ? Url::resolve($raw) : '';
                    $label = (string)($row['label'] ?: $row['fallback_label']);
                }
                if ($href === '' || $label === '') {
                    continue;
                }
                $items[] = ['id' => (int)$row['id'], 'parent_id' => $row['parent_id'] !== null ? (int)$row['parent_id'] : null,
                            'label' => $label, 'href' => $href, 'new_tab' => (bool)$row['new_tab']];
            }
            return self::nest($items, true);
        });
    }

    public function findItem(int $id): ?array
    {
        $item = $this->db->first('SELECT * FROM menu_items WHERE id = ?', [$id]);
        if ($item !== null) {
            $item['translations'] = [];
            foreach ($this->db->select('SELECT * FROM menu_item_translations WHERE item_id = ?', [$id]) as $row) {
                $item['translations'][$row['lang']] = $row;
            }
        }
        return $item;
    }

    /**
     * @param array{parent_id: ?int, type: string, page_id: ?int, url: string, new_tab: bool, is_active: bool} $data
     * @param array<string, array{label: string, url: string}> $translations
     */
    public function saveItem(?int $id, int $menuId, array $data, array $translations): int
    {
        $id = $this->db->transaction(function (DB $db) use ($id, $menuId, $data, $translations): int {
            $row = [
                'parent_id' => $data['parent_id'],
                'type'      => $data['type'],
                'page_id'   => $data['type'] === 'page' ? $data['page_id'] : null,
                'url'       => $data['type'] === 'url' ? $data['url'] : '',
                'new_tab'   => (int)$data['new_tab'],
                'is_active' => (int)$data['is_active'],
            ];
            if ($id === null) {
                $row['sort'] = (int)$db->value('SELECT COALESCE(MAX(sort), 0) + 1 FROM menu_items WHERE menu_id = ? AND parent_id <=> ?', [$menuId, $data['parent_id']]);
                $id = $db->insert('menu_items', $row + ['menu_id' => $menuId]);
            } else {
                $db->update('menu_items', $row, 'id = ?', [$id]);
            }
            foreach ($translations as $lang => $t) {
                $db->query(
                    'INSERT INTO menu_item_translations (item_id, lang, label, url) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE label = VALUES(label), url = VALUES(url)',
                    [$id, $lang, $t['label'], $t['url']],
                );
            }
            return $id;
        });
        $this->flush();
        return $id;
    }

    /** მენიუში ჯერ არცერთი პუნქტი არ არის (დამალულიც კი) */
    public function isEmpty(string $location): bool
    {
        return !$this->db->value(
            'SELECT 1 FROM menu_items i JOIN menus m ON m.id = i.menu_id WHERE m.location = ? LIMIT 1',
            [$location],
        );
    }

    public function hasChildren(int $id): bool
    {
        return (bool)$this->db->value('SELECT 1 FROM menu_items WHERE parent_id = ? LIMIT 1', [$id]);
    }

    public function deleteItem(int $id): void
    {
        $this->db->delete('menu_items', 'id = ?', [$id]);
        $this->flush();
    }

    /**
     * ხის შენახვა drag & drop-ის შემდეგ: [{id, children: [{id}, ...]}, ...].
     * max_depth-ზე ღრმა პუნქტები ბოლო დასაშვებ დონეზე ადის.
     * @return bool true — სტრუქტურა შესწორდა (ზედმეტი სიღრმე)
     */
    public function reorder(int $menuId, array $tree, int $maxDepth): bool
    {
        $owned = array_map('intval', $this->db->column('SELECT id FROM menu_items WHERE menu_id = ?', [$menuId]));
        $flattened = false;
        $rows = [];
        $walk = function (array $nodes, ?int $parent, int $depth) use (&$walk, &$rows, &$flattened, $owned, $maxDepth): void {
            foreach (array_values($nodes) as $node) {
                $id = (int)($node['id'] ?? 0);
                if (!in_array($id, $owned, true) || isset($rows[$id])) {
                    continue;
                }
                $rows[$id] = ['parent_id' => $parent, 'sort' => count(array_filter($rows, fn ($r) => $r['parent_id'] === $parent)) + 1];
                $children = is_array($node['children'] ?? null) ? $node['children'] : [];
                if ($children !== []) {
                    if ($depth >= $maxDepth) {
                        $flattened = true;
                        $walk($children, $parent, $depth);
                    } else {
                        $walk($children, $id, $depth + 1);
                    }
                }
            }
        };
        $walk($tree, null, 1);

        $this->db->transaction(function (DB $db) use ($rows): void {
            foreach ($rows as $id => $row) {
                $db->update('menu_items', $row, 'id = ?', [$id]);
            }
        });
        $this->flush();
        return $flattened;
    }

    public function flush(): void
    {
        $this->cache->flush(self::CACHE_PREFIX);
    }

    /** ბრტყელი სია (parent_id) → ხე (children) */
    private static function nest(array $items, bool $dropOrphans = false): array
    {
        $byParent = [];
        $ids = array_flip(array_map(fn ($i) => (int)$i['id'], $items));
        foreach ($items as $item) {
            $parent = $item['parent_id'] !== null ? (int)$item['parent_id'] : null;
            // მშობელი დამალულია/არ ჩანს → ფრონტზე შვილიც არ ჩანს; ადმინში — ზედა დონეზე
            if ($parent !== null && !isset($ids[$parent])) {
                if ($dropOrphans) {
                    continue;
                }
                $parent = null;
            }
            $byParent[$parent ?? 0][] = $item;
        }
        $build = function (int $parent) use (&$build, $byParent): array {
            $list = [];
            foreach ($byParent[$parent] ?? [] as $item) {
                $item['children'] = $build((int)$item['id']);
                $list[] = $item;
            }
            return $list;
        };
        return $build(0);
    }
}
