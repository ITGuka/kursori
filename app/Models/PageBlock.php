<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * გვერდის ბლოკები: page_blocks (ტიპი, რიგი, settings JSON, media_ids) + page_block_translations (content JSON ენაზე).
 */
final class PageBlock
{
    public function __construct(private readonly DB $db)
    {
    }

    /** @return list<array> ყოველს: settings (array), content (lang => array) */
    public function forPage(int $pageId, bool $onlyActive = false): array
    {
        $blocks = $this->db->select(
            'SELECT * FROM page_blocks WHERE page_id = ?' . ($onlyActive ? ' AND is_active = 1' : '') . ' ORDER BY sort, id',
            [$pageId],
        );
        if ($blocks === []) {
            return [];
        }

        $ids = array_column($blocks, 'id');
        $content = [];
        $rows = $this->db->select(
            'SELECT block_id, lang, content FROM page_block_translations WHERE block_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')',
            $ids,
        );
        foreach ($rows as $row) {
            $content[$row['block_id']][$row['lang']] = self::decode($row['content']);
        }

        foreach ($blocks as &$block) {
            $block['settings'] = self::decode($block['settings']);
            $block['content'] = $content[$block['id']] ?? [];
        }
        return $blocks;
    }

    public function find(int $id): ?array
    {
        $block = $this->db->first('SELECT * FROM page_blocks WHERE id = ?', [$id]);
        if ($block === null) {
            return null;
        }
        $block['settings'] = self::decode($block['settings']);
        $block['content'] = [];
        foreach ($this->db->select('SELECT lang, content FROM page_block_translations WHERE block_id = ?', [$id]) as $row) {
            $block['content'][$row['lang']] = self::decode($row['content']);
        }
        return $block;
    }

    /**
     * @param array<string, array> $content lang => content
     * @param list<int> $mediaIds
     */
    public function save(?int $id, int $pageId, string $type, array $settings, array $content, array $mediaIds): int
    {
        return $this->db->transaction(function (DB $db) use ($id, $pageId, $type, $settings, $content, $mediaIds): int {
            $row = [
                'settings'  => self::encode($settings),
                'media_ids' => json_encode(array_values(array_map('intval', $mediaIds))),
            ];
            if ($id === null) {
                $sort = (int)$db->value('SELECT COALESCE(MAX(sort), 0) + 1 FROM page_blocks WHERE page_id = ?', [$pageId]);
                $id = $db->insert('page_blocks', $row + ['page_id' => $pageId, 'type' => $type, 'sort' => $sort]);
            } else {
                $db->update('page_blocks', $row, 'id = ?', [$id]);
            }
            foreach ($content as $lang => $value) {
                $db->query(
                    'INSERT INTO page_block_translations (block_id, lang, content) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)',
                    [$id, $lang, self::encode($value)],
                );
            }
            return $id;
        });
    }

    public function setActive(int $id, bool $active): void
    {
        $this->db->update('page_blocks', ['is_active' => (int)$active], 'id = ?', [$id]);
    }

    /** ასლი იმავე გვერდზე, ორიგინალის შემდეგ */
    public function duplicate(int $id): int
    {
        $block = $this->find($id);
        return $this->db->transaction(function (DB $db) use ($block): int {
            $db->query('UPDATE page_blocks SET sort = sort + 1 WHERE page_id = ? AND sort > ?', [$block['page_id'], $block['sort']]);
            $newId = $db->insert('page_blocks', [
                'page_id'   => $block['page_id'],
                'type'      => $block['type'],
                'sort'      => $block['sort'] + 1,
                'is_active' => 0,
                'settings'  => self::encode($block['settings']),
                'media_ids' => $block['media_ids'],
            ]);
            foreach ($block['content'] as $lang => $content) {
                $db->insert('page_block_translations', ['block_id' => $newId, 'lang' => $lang, 'content' => self::encode($content)]);
            }
            return $newId;
        });
    }

    /** @param list<int> $ids ახალი რიგი; სხვა გვერდის ბლოკები იგნორირდება */
    public function reorder(int $pageId, array $ids): void
    {
        $this->db->transaction(function (DB $db) use ($pageId, $ids): void {
            foreach (array_values($ids) as $position => $id) {
                $db->update('page_blocks', ['sort' => $position + 1], 'id = ? AND page_id = ?', [(int)$id, $pageId]);
            }
        });
    }

    public function delete(int $id): void
    {
        $this->db->delete('page_blocks', 'id = ?', [$id]);
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
