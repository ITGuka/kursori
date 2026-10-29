<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * ფასების პაკეტები = დიზაინების ზედა დონის კატეგორიები (categories, collection = Design::CATEGORIES, parent_id = NULL).
 * code (start / business / shop) — ბმულისთვის (?package=); ფასი, მინიშნება, „რა შედის“, ნიშანი — package_translations.
 * ქვეკატეგორიები — დარგები (ადმინი: CategoryController). ადმინი: PackageController.
 */
final class Package
{
    public const FIELDS = ['price' => 60, 'note' => 150, 'features' => 1500, 'badge' => 60];

    public function __construct(private readonly DB $db, private readonly Category $categories)
    {
    }

    /**
     * ადმინის სია: name (ძირითად ენაზე ან პირველი), code, price, children (დარგების რაოდენობა), designs, langs.
     * @return list<array>
     */
    public function all(string $lang): array
    {
        $rows = [];
        foreach ($this->categories->tree(Design::CATEGORIES, $lang) as $row) {
            if ($row['depth'] > 0) {
                continue;
            }
            $id = (int)$row['id'];
            $ids = $this->categories->withChildren($id);
            $row['children'] = count($ids) - 1;
            $row['designs'] = (int)$this->db->value(
                'SELECT COUNT(DISTINCT design_id) FROM design_category WHERE category_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')',
                $ids,
            );
            $row['price'] = (string)($this->db->value('SELECT price FROM package_translations WHERE category_id = ? AND lang = ?', [$id, $lang]) ?? '');
            $rows[] = $row;
        }
        return $rows;
    }

    /** @return array|null კატეგორია + translations (lang => name, slug, price, note, features, badge) */
    public function find(int $id): ?array
    {
        $package = $this->categories->find($id);
        if ($package === null || $package['collection'] !== Design::CATEGORIES || $package['parent_id'] !== null) {
            return null;
        }
        $package['translations'] = $this->categories->translations($id);
        foreach ($this->db->select('SELECT * FROM package_translations WHERE category_id = ?', [$id]) as $row) {
            $package['translations'][$row['lang']] = ($package['translations'][$row['lang']] ?? ['name' => '', 'slug' => '']) + $row;
        }
        return $package;
    }

    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        return (bool)$this->db->value(
            'SELECT 1 FROM categories WHERE collection = ? AND code = ?' . ($exceptId ? ' AND id <> ?' : ''),
            $exceptId ? [Design::CATEGORIES, $code, $exceptId] : [Design::CATEGORIES, $code],
        );
    }

    /**
     * @param array{code: string, sort: int, is_active: bool} $data
     * @param array<string, array{name: string, slug: string, price: string, note: string, features: string, badge: string}> $translations
     *        ცარიელი name → თარგმანი იშლება
     */
    public function save(?int $id, array $data, array $translations): int
    {
        $id = $this->categories->save($id, Design::CATEGORIES, ['parent_id' => null, 'sort' => $data['sort'], 'is_active' => $data['is_active']],
            array_map(fn (array $t) => ['name' => $t['name'], 'slug' => $t['slug']], $translations));
        $this->db->transaction(function (DB $db) use ($id, $data, $translations): void {
            $db->update('categories', ['code' => $data['code']], 'id = ?', [$id]);
            foreach ($translations as $lang => $t) {
                if ($t['name'] === '') {
                    $db->delete('package_translations', 'category_id = ? AND lang = ?', [$id, $lang]);
                    continue;
                }
                $db->query(
                    'INSERT INTO package_translations (category_id, lang, price, note, features, badge) VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE price = VALUES(price), note = VALUES(note), features = VALUES(features), badge = VALUES(badge)',
                    [$id, $lang, $t['price'], $t['note'], $t['features'], $t['badge']],
                );
            }
        });
        return $id;
    }

    // ---- ფრონტი ----

    /**
     * აქტიური პაკეტები კოდით, მიმდინარე ენაზე (თარგმანი აკლია → ძირითადი ენის).
     * ids — პაკეტისა და დარგების id-ები (დიზაინების ფილტრი); children — აქტიური დარგები.
     * @param list<array>|null $designs Design::published() → დარგები დიზაინების გარეშე გამოტოვდება
     * @return list<array{id: int, code: string, name: string, price: string, note: string, features: list<string>, badge: string,
     *                    ids: list<int>, children: list<array{id: int, name: string}>}>
     */
    public function published(string $lang, string $defaultLang, ?array $designs = null): array
    {
        $used = $designs === null ? null : array_unique(array_merge([], ...array_column($designs, 'categories')));
        $packages = [];
        $current = null;
        foreach ($this->categories->tree(Design::CATEGORIES, $lang) as $row) {
            $id = (int)$row['id'];
            if ($row['depth'] === 0) {
                $current = null;
                $name = $this->categories->name($id, $lang, $defaultLang);
                if (!$row['is_active'] || (string)$row['code'] === '' || $name === '') {
                    continue;
                }
                $texts = $this->db->first(
                    'SELECT * FROM package_translations WHERE category_id = ? AND lang IN (?, ?) ORDER BY lang = ? DESC LIMIT 1',
                    [$id, $lang, $defaultLang, $lang],
                ) ?? ['price' => '', 'note' => '', 'features' => '', 'badge' => ''];
                $packages[] = [
                    'id'       => $id,
                    'code'     => (string)$row['code'],
                    'name'     => $name,
                    'price'    => $texts['price'],
                    'note'     => $texts['note'],
                    'features' => array_values(array_filter(array_map('trim', explode("\n", $texts['features'])))),
                    'badge'    => $texts['badge'],
                    'ids'      => [$id],
                    'children' => [],
                ];
                $current = array_key_last($packages);
                continue;
            }
            if ($current === null) {
                continue;
            }
            // გათიშულ დარგს ფილტრში არ ვითვლით, მაგრამ მისი დიზაინები პაკეტის „ყველაში“ რჩება
            $packages[$current]['ids'][] = $id;
            $name = $this->categories->name($id, $lang, $defaultLang);
            if ($row['is_active'] && $name !== '' && ($used === null || in_array($id, $used, true))) {
                $packages[$current]['children'][] = ['id' => $id, 'name' => $name];
            }
        }
        return $packages;
    }
}
