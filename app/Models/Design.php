<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * საიტის დიზაინები (შეკვეთის ფორმა): designs + design_translations (სათაური ენებზე) + design_category.
 * კატეგორიები — საერთო categories ცხრილში, collection = 'designs' (ადმინი: CategoryController).
 * შაბლონის ფაილები — public/uploads/designs/{folder}/ (App\Services\TemplateArchive).
 */
final class Design
{
    /** categories.collection — დიზაინების კატეგორიები: ზედა დონე = პაკეტი (App\Models\Package), ქვეკატეგორია = დარგი */
    public const CATEGORIES = 'designs';
    public const DIRECTORY = 'uploads/designs';

    public function __construct(private readonly DB $db, private readonly Category $categories)
    {
    }

    /** @return array|null ყოველს: translations (lang => title), category_ids */
    public function find(int $id): ?array
    {
        $design = $id > 0 ? $this->db->first('SELECT * FROM designs WHERE id = ?', [$id]) : null;
        if ($design === null) {
            return null;
        }
        $design['translations'] = [];
        foreach ($this->db->select('SELECT lang, title FROM design_translations WHERE design_id = ?', [$id]) as $row) {
            $design['translations'][$row['lang']] = $row['title'];
        }
        $design['category_ids'] = array_map('intval', $this->db->column('SELECT category_id FROM design_category WHERE design_id = ?', [$id]));
        return $design;
    }

    /**
     * ადმინის სია: title (ძირითად ენაზე ან პირველი), langs, categories (სახელები, „, “-ით).
     * @return list<array>
     */
    public function all(string $lang, int $categoryId = 0, string $q = ''): array
    {
        $where = ['1 = 1'];
        $bindings = [];
        if ($categoryId > 0) {
            $ids = $this->categories->withChildren($categoryId);
            $where[] = 'EXISTS (SELECT 1 FROM design_category c WHERE c.design_id = d.id AND c.category_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . '))';
            array_push($bindings, ...$ids);
        }
        if ($q !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM design_translations s WHERE s.design_id = d.id AND s.title LIKE ?)';
            $bindings[] = '%' . addcslashes($q, '%_\\') . '%';
        }
        $rows = $this->db->select(
            "SELECT d.*,
                    COALESCE((SELECT title FROM design_translations t WHERE t.design_id = d.id AND t.lang = ?),
                             (SELECT title FROM design_translations t WHERE t.design_id = d.id LIMIT 1), CONCAT('#', d.id)) AS title,
                    (SELECT GROUP_CONCAT(lang) FROM design_translations t WHERE t.design_id = d.id) AS langs,
                    (SELECT GROUP_CONCAT(n.name ORDER BY n.name SEPARATOR ', ') FROM design_category c
                       JOIN category_translations n ON n.category_id = c.category_id AND n.lang = ?
                      WHERE c.design_id = d.id) AS categories
             FROM designs d WHERE " . implode(' AND ', $where) . ' ORDER BY d.sort, d.id',
            [$lang, $lang, ...$bindings],
        );
        foreach ($rows as &$row) {
            $row['langs'] = $row['langs'] ? explode(',', $row['langs']) : [];
        }
        return $rows;
    }

    /**
     * @param array{image_id: ?int, is_active: bool} $core
     * @param array<string, string> $titles lang => სათაური (ცარიელი → თარგმანი იშლება)
     * @param list<int> $categoryIds
     */
    public function save(?int $id, array $core, array $titles, array $categoryIds): int
    {
        return $this->db->transaction(function (DB $db) use ($id, $core, $titles, $categoryIds): int {
            $row = ['image_id' => $core['image_id'], 'is_active' => (int)$core['is_active']];
            if ($id === null) {
                $row['sort'] = (int)$db->value('SELECT COALESCE(MAX(sort), 0) + 1 FROM designs');
                $id = $db->insert('designs', $row);
            } else {
                $db->update('designs', $row, 'id = ?', [$id]);
            }
            foreach ($titles as $lang => $title) {
                if ($title === '') {
                    $db->delete('design_translations', 'design_id = ? AND lang = ?', [$id, $lang]);
                    continue;
                }
                $db->query('INSERT INTO design_translations (design_id, lang, title) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title)', [$id, $lang, $title]);
            }
            $db->delete('design_category', 'design_id = ?', [$id]);
            foreach (array_unique($categoryIds) as $categoryId) {
                $db->insert('design_category', ['design_id' => $id, 'category_id' => $categoryId]);
            }
            return $id;
        });
    }

    /** @param array{folder: string, entry: string, files: int, size: int} $template */
    public function setTemplate(int $id, array $template): void
    {
        $this->db->update('designs', $template, 'id = ?', [$id]);
    }

    public function setActive(int $id, bool $active): void
    {
        $this->db->update('designs', ['is_active' => (int)$active], 'id = ?', [$id]);
    }

    /** @param list<int> $ids ახალი რიგი */
    public function reorder(array $ids): void
    {
        $this->db->transaction(function (DB $db) use ($ids): void {
            foreach (array_values($ids) as $position => $id) {
                $db->update('designs', ['sort' => $position + 1], 'id = ?', [(int)$id]);
            }
        });
    }

    public function delete(int $id): void
    {
        $this->db->delete('designs', 'id = ?', [$id]);
    }

    /** შაბლონის საჯარო URL (index.html); შაბლონი არ აქვს → '' */
    public static function previewUrl(array $design): string
    {
        return !empty($design['folder']) && !empty($design['entry'])
            ? url(self::DIRECTORY . '/' . $design['folder'] . '/' . implode('/', array_map('rawurlencode', explode('/', $design['entry']))))
            : '';
    }

    // ---- ფრონტი ----

    /**
     * აქტიური დიზაინები შაბლონით, მიმდინარე ენაზე (თარგმანი აკლია → ძირითადი ენის).
     * @return list<array{id: int, title: string, image: ?int, preview: string, categories: list<int>}>
     */
    public function published(string $lang, string $defaultLang): array
    {
        $rows = $this->db->select(
            "SELECT d.*, COALESCE(
                    (SELECT title FROM design_translations t WHERE t.design_id = d.id AND t.lang = ?),
                    (SELECT title FROM design_translations t WHERE t.design_id = d.id AND t.lang = ?)) AS title,
                    (SELECT GROUP_CONCAT(category_id) FROM design_category c WHERE c.design_id = d.id) AS category_ids
             FROM designs d WHERE d.is_active = 1 AND d.folder IS NOT NULL ORDER BY d.sort, d.id",
            [$lang, $defaultLang],
        );
        $designs = [];
        foreach ($rows as $row) {
            if ($row['title'] === null) {
                continue;
            }
            $designs[] = [
                'id'         => (int)$row['id'],
                'title'      => $row['title'],
                'image'      => $row['image_id'] !== null ? (int)$row['image_id'] : null,
                'preview'    => self::previewUrl($row),
                'categories' => $row['category_ids'] ? array_map('intval', explode(',', $row['category_ids'])) : [],
            ];
        }
        return $designs;
    }
}
