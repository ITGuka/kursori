<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * კოლექციის კატეგორიები (2 დონემდე): categories + category_translations (სახელი, slug ენებზე).
 * collection — კოლექციის გასაღები ან Design::CATEGORIES (დიზაინები, design_category-ით).
 */
final class Category
{
    /** @var array<string, string> */
    private array $names = [];

    public function __construct(private readonly DB $db)
    {
    }

    public function find(int $id): ?array
    {
        return $id > 0 ? $this->db->first('SELECT * FROM categories WHERE id = ?', [$id]) : null;
    }

    /** @return array<string, array{name: string, slug: string}> */
    public function translations(int $id): array
    {
        $rows = [];
        foreach ($this->db->select('SELECT lang, name, slug FROM category_translations WHERE category_id = ?', [$id]) as $row) {
            $rows[$row['lang']] = $row;
        }
        return $rows;
    }

    /**
     * ხე ადმინისთვის: ყოველს — depth, name (ძირითად ენაზე ან პირველი), langs, items (ჩანაწერების რაოდენობა).
     * @return list<array>
     */
    public function tree(string $collection, string $lang): array
    {
        $rows = $this->db->select(
            "SELECT c.*,
                    COALESCE((SELECT name FROM category_translations t WHERE t.category_id = c.id AND t.lang = ?),
                             (SELECT name FROM category_translations t WHERE t.category_id = c.id LIMIT 1), CONCAT('#', c.id)) AS name,
                    (SELECT GROUP_CONCAT(lang) FROM category_translations t WHERE t.category_id = c.id) AS langs,
                    (SELECT COUNT(*) FROM collection_items i WHERE i.category_id = c.id)
                      + (SELECT COUNT(*) FROM design_category d WHERE d.category_id = c.id) AS items
             FROM categories c WHERE c.collection = ? ORDER BY c.sort, c.id",
            [$lang, $collection],
        );
        $tree = [];
        foreach ($rows as $row) {
            if ($row['parent_id'] === null) {
                $tree[] = ['depth' => 0, 'langs' => explode(',', (string)$row['langs'])] + $row;
                foreach ($rows as $child) {
                    if ((int)$child['parent_id'] === (int)$row['id']) {
                        $tree[] = ['depth' => 1, 'langs' => explode(',', (string)$child['langs'])] + $child;
                    }
                }
            }
        }
        return $tree;
    }

    /** @return array<int, string> id => სახელი (ქვეკატეგორია „— “-ით) */
    public function options(string $collection, string $lang, bool $onlyTopLevel = false, ?int $exclude = null): array
    {
        $options = [];
        foreach ($this->tree($collection, $lang) as $row) {
            if (($onlyTopLevel && $row['depth'] > 0) || (int)$row['id'] === $exclude) {
                continue;
            }
            $options[(int)$row['id']] = str_repeat('— ', $row['depth']) . $row['name'];
        }
        return $options;
    }

    /** @return list<int> კატეგორია + მისი ქვეკატეგორიები (ფილტრისთვის) */
    public function withChildren(int $id): array
    {
        return [$id, ...array_map('intval', $this->db->column('SELECT id FROM categories WHERE parent_id = ?', [$id]))];
    }

    public function name(int $id, string $lang, string $fallbackLang): string
    {
        // ერთი მოთხოვნის განმავლობაში ერთი და იგივე კატეგორია ბევრ ჩანაწერს აქვს
        return $this->names["$id|$lang|$fallbackLang"] ??= (string)($this->db->value(
            'SELECT name FROM category_translations WHERE category_id = ? AND lang IN (?, ?) ORDER BY lang = ? DESC LIMIT 1',
            [$id, $lang, $fallbackLang, $lang],
        ) ?? '');
    }

    public function slugExists(string $collection, string $lang, string $slug, ?int $exceptId = null): bool
    {
        return (bool)$this->db->value(
            'SELECT 1 FROM category_translations t JOIN categories c ON c.id = t.category_id
             WHERE c.collection = ? AND t.lang = ? AND t.slug = ?' . ($exceptId ? ' AND c.id <> ?' : ''),
            $exceptId ? [$collection, $lang, $slug, $exceptId] : [$collection, $lang, $slug],
        );
    }

    /**
     * @param array{parent_id: ?int, sort: int, is_active: bool} $data
     * @param array<string, array{name: string, slug: string}> $translations ცარიელი name → თარგმანი იშლება
     */
    public function save(?int $id, string $collection, array $data, array $translations): int
    {
        return $this->db->transaction(function (DB $db) use ($id, $collection, $data, $translations): int {
            $row = ['parent_id' => $data['parent_id'], 'sort' => $data['sort'], 'is_active' => (int)$data['is_active']];
            if ($id === null) {
                $id = $db->insert('categories', $row + ['collection' => $collection]);
            } else {
                $db->update('categories', $row, 'id = ?', [$id]);
            }
            foreach ($translations as $lang => $t) {
                if ($t['name'] === '') {
                    $db->delete('category_translations', 'category_id = ? AND lang = ?', [$id, $lang]);
                    continue;
                }
                $db->query(
                    'INSERT INTO category_translations (category_id, lang, name, slug) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug)',
                    [$id, $lang, $t['name'], $t['slug']],
                );
            }
            return $id;
        });
    }

    public function hasChildren(int $id): bool
    {
        return (bool)$this->db->value('SELECT 1 FROM categories WHERE parent_id = ? LIMIT 1', [$id]);
    }

    /** ჩანაწერები კატეგორიის გარეშე რჩება (FK: SET NULL), ქვეკატეგორიები — ზედა დონეზე */
    public function delete(int $id): void
    {
        $this->db->delete('categories', 'id = ?', [$id]);
    }
}
