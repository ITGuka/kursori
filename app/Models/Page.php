<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;
use Core\Support\Cache;

/**
 * გვერდები: pages (სტრუქტურა, სტატუსი) + page_translations (სათაური, slug, SEO თითო ენაზე).
 * URL: მთავარი გვერდი → /{lang}, დანარჩენი → /{lang}/{slug}.
 */
final class Page
{
    /** @var array<int, array{is_home: bool, status: string, parent_id: ?int, sort: int, slugs: array<string, string>, titles: array<string, string>}>|null */
    private ?array $map = null;

    public function __construct(private readonly DB $db, private readonly Cache $cache)
    {
    }

    public function find(int $id): ?array
    {
        return $id > 0 ? $this->db->first('SELECT * FROM pages WHERE id = ?', [$id]) : null;
    }

    /** @return array<string, array<string, string>> lang => translation */
    public function translations(int $id): array
    {
        $rows = [];
        foreach ($this->db->select('SELECT * FROM page_translations WHERE page_id = ?', [$id]) as $row) {
            $rows[$row['lang']] = $row;
        }
        return $rows;
    }

    /** @return array{page: array, translation: array}|null */
    public function findBySlug(string $lang, string $slug, bool $withDrafts = false): ?array
    {
        $row = $this->db->first(
            'SELECT p.*, t.title, t.slug, t.meta_title, t.meta_description FROM pages p
             JOIN page_translations t ON t.page_id = p.id AND t.lang = ?
             WHERE t.slug = ? AND p.is_home = 0' . ($withDrafts ? '' : " AND p.status = 'published'"),
            [$lang, $slug],
        );
        return $row === null ? null : $this->split($row);
    }

    /** @return array{page: array, translation: array}|null */
    public function home(string $lang, bool $withDrafts = false): ?array
    {
        $row = $this->db->first(
            'SELECT p.*, t.title, t.slug, t.meta_title, t.meta_description FROM pages p
             JOIN page_translations t ON t.page_id = p.id AND t.lang = ?
             WHERE p.is_home = 1' . ($withDrafts ? '' : " AND p.status = 'published'"),
            [$lang],
        );
        return $row === null ? null : $this->split($row);
    }

    /**
     * ადმინის სია ხედ: მშობელი → შვილები, სორტით.
     * @return list<array> ყოველ სტრიქონს: depth, title (ძირითად ენაზე ან პირველი არსებული), langs
     */
    public function tree(string $defaultLang): array
    {
        $map = $this->map(true);
        $rows = [];
        $walk = function (?int $parent, int $depth) use (&$walk, &$rows, $map, $defaultLang): void {
            foreach ($map as $id => $page) {
                if ($page['parent_id'] === $parent) {
                    $rows[] = ['id' => $id, 'depth' => $depth, 'title' => $page['titles'][$defaultLang] ?? (reset($page['titles']) ?: '#' . $id),
                               'langs' => array_keys($page['slugs'])] + $page;
                    if ($depth < 5) {
                        $walk($id, $depth + 1);
                    }
                }
            }
        };
        $walk(null, 0);
        return $rows;
    }

    /** @return array<int, string> id => სათაური (select-ებისთვის, ჩაღრმავებით) */
    public function options(string $defaultLang, ?int $exclude = null): array
    {
        $options = [];
        foreach ($this->tree($defaultLang) as $row) {
            if ($row['id'] !== $exclude) {
                $options[$row['id']] = str_repeat('— ', $row['depth']) . $row['title'];
            }
        }
        return $options;
    }

    /** გამოქვეყნებული გვერდის URL ენაზე; თარგმანი აკლია ან მონახაზია → '' */
    public function url(int $id, string $lang, bool $withDrafts = false): string
    {
        $page = $this->map($withDrafts)[$id] ?? null;
        if ($page === null || !isset($page['slugs'][$lang])) {
            return '';
        }
        return $page['is_home'] ? url($lang) : url($lang . '/' . $page['slugs'][$lang]);
    }

    /**
     * ნავიგაცია (ფაზა 5-მდე, მენიუების ნაცვლად): ზედა დონის გამოქვეყნებული გვერდები + მათი შვილები.
     * @return list<array{id: int, title: string, href: string, children: list<array>}>
     */
    public function navigation(string $lang): array
    {
        $map = $this->map(false);
        $items = function (?int $parent) use ($map, $lang): array {
            $list = [];
            foreach ($map as $id => $page) {
                if ($page['parent_id'] === $parent && isset($page['slugs'][$lang])) {
                    $list[] = ['id' => $id, 'title' => $page['titles'][$lang], 'href' => $this->url($id, $lang)];
                }
            }
            return $list;
        };
        $nav = $items(null);
        foreach ($nav as &$item) {
            $item['children'] = $items($item['id']);
        }
        return $nav;
    }

    /** @return list<array{id: int, title: string, href: string}> ძირიდან მიმდინარე გვერდამდე (თავად გვერდის გარეშე) */
    public function ancestors(int $id, string $lang): array
    {
        $map = $this->map(true);
        $chain = [];
        $parent = $map[$id]['parent_id'] ?? null;
        while ($parent !== null && isset($map[$parent]) && count($chain) < 10) {
            if (isset($map[$parent]['titles'][$lang])) {
                array_unshift($chain, ['id' => $parent, 'title' => $map[$parent]['titles'][$lang], 'href' => $this->url($parent, $lang)]);
            }
            $parent = $map[$parent]['parent_id'];
        }
        return $chain;
    }

    public function slugExists(string $lang, string $slug, ?int $exceptId = null): bool
    {
        return (bool)$this->db->value(
            'SELECT 1 FROM page_translations WHERE lang = ? AND slug = ?' . ($exceptId ? ' AND page_id <> ?' : ''),
            $exceptId ? [$lang, $slug, $exceptId] : [$lang, $slug],
        );
    }

    /**
     * @param array{parent_id: ?int, status: string, is_home: bool, sort: int, og_image_id: ?int} $data
     * @param array<string, array{title: string, slug: string, meta_title: string, meta_description: string}> $translations
     *        ცარიელი title → ამ ენაზე თარგმანი არ არსებობს (იშლება)
     */
    public function save(?int $id, array $data, array $translations): int
    {
        $id = $this->db->transaction(function (DB $db) use ($id, $data, $translations): int {
            $row = [
                'parent_id'   => $data['parent_id'],
                'status'      => $data['status'],
                'is_home'     => (int)$data['is_home'],
                'sort'        => $data['sort'],
                'og_image_id' => $data['og_image_id'],
            ];
            if ($id === null) {
                $id = $db->insert('pages', $row);
            } else {
                $db->update('pages', $row, 'id = ?', [$id]);
            }
            if ($data['is_home']) {
                $db->query('UPDATE pages SET is_home = 0 WHERE id <> ?', [$id]);
            }

            foreach ($translations as $lang => $t) {
                if ($t['title'] === '') {
                    $db->delete('page_translations', 'page_id = ? AND lang = ?', [$id, $lang]);
                    continue;
                }
                $db->query(
                    'INSERT INTO page_translations (page_id, lang, title, slug, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description)',
                    [$id, $lang, $t['title'], $t['slug'], $t['meta_title'], $t['meta_description']],
                );
            }
            return $id;
        });
        $this->changed();
        return $id;
    }

    public function delete(int $id): void
    {
        // შვილები ზედა დონეზე გადადის (FK: ON DELETE SET NULL), ბლოკები, თარგმანები და მენიუს პუნქტები იშლება (CASCADE)
        $this->db->delete('pages', 'id = ?', [$id]);
        $this->changed();
    }

    /** სათაური/slug/სტატუსი მენიუშიც ჩანს — მისი ქეში იშლება */
    private function changed(): void
    {
        $this->map = null;
        $this->cache->flush(Menu::CACHE_PREFIX);
    }

    /** @return array<int, array> */
    private function map(bool $withDrafts): array
    {
        if ($this->map === null) {
            $this->map = [];
            $rows = $this->db->select(
                'SELECT p.id, p.parent_id, p.status, p.is_home, p.sort, t.lang, t.slug, t.title
                 FROM pages p LEFT JOIN page_translations t ON t.page_id = p.id
                 ORDER BY p.sort, p.id',
            );
            foreach ($rows as $row) {
                $id = (int)$row['id'];
                $this->map[$id] ??= [
                    'is_home'   => (bool)$row['is_home'],
                    'status'    => $row['status'],
                    'parent_id' => $row['parent_id'] !== null ? (int)$row['parent_id'] : null,
                    'sort'      => (int)$row['sort'],
                    'slugs'     => [],
                    'titles'    => [],
                ];
                if ($row['lang'] !== null) {
                    $this->map[$id]['slugs'][$row['lang']] = $row['slug'];
                    $this->map[$id]['titles'][$row['lang']] = $row['title'];
                }
            }
        }
        return $withDrafts ? $this->map : array_filter($this->map, fn ($page) => $page['status'] === 'published');
    }

    /** @return array{page: array, translation: array} */
    private function split(array $row): array
    {
        $translation = array_intersect_key($row, array_flip(['title', 'slug', 'meta_title', 'meta_description']));
        return ['page' => array_diff_key($row, $translation), 'translation' => $translation];
    }
}
