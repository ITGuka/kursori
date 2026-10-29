<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * წამოღებული სიახლეები (news_items): new → approved → published, ან rejected / failed.
 */
final class NewsItem
{
    public function __construct(private readonly DB $db)
    {
    }

    /**
     * @param list<string> $externalIds
     * @return list<string> მათგან უკვე შენახული
     */
    public function existing(string $source, array $externalIds): array
    {
        if ($externalIds === []) {
            return [];
        }
        $placeholders = implode(', ', array_fill(0, count($externalIds), '?'));
        return array_map('strval', $this->db->column(
            "SELECT external_id FROM news_items WHERE source = ? AND external_id IN ($placeholders)",
            [$source, ...$externalIds],
        ));
    }

    /** @param array<string, mixed> $data news_items-ის სვეტები (tags — მასივი) */
    public function create(array $data): int
    {
        $data['tags'] = json_encode(array_values($data['tags'] ?? []), JSON_UNESCAPED_UNICODE);
        return $this->db->insert('news_items', $data);
    }

    /** @return array|null სიახლე + feed-ის კატეგორია (category_id) */
    public function find(int $id): ?array
    {
        return $this->db->first(
            'SELECT i.*, f.category_id FROM news_items i LEFT JOIN news_feeds f ON f.id = i.feed_id WHERE i.id = ?',
            [$id],
        );
    }

    /** @return list<int> დამტკიცებული (ან ავტომატური) სიახლეები გამოსაქვეყნებლად — ძველი პირველი */
    public function approvedIds(int $limit): array
    {
        return array_map('intval', $this->db->column(
            "SELECT id FROM news_items WHERE status = 'approved' ORDER BY created_at, id LIMIT " . max(0, $limit),
        ));
    }

    public function publishedToday(): int
    {
        return (int)$this->db->value(
            'SELECT COUNT(*) FROM news_items WHERE published_at >= ?',
            [date('Y-m-d 00:00:00')],
        );
    }

    public function markPublished(int $id, int $collectionItemId): void
    {
        $this->db->update('news_items', [
            'status' => 'published', 'collection_item_id' => $collectionItemId, 'published_at' => date('Y-m-d H:i:s'), 'error' => null,
        ], 'id = ?', [$id]);
    }

    public function setError(int $id, ?string $error): void
    {
        $this->db->update('news_items', ['error' => $error === null ? null : mb_substr($error, 0, 1000)], 'id = ?', [$id]);
    }

    /** @return list<int> გამოქვეყნებული სიახლეები, რომელთა ბლოგის ჩანაწერს $lang თარგმანი არ აქვს — ძველი პირველი */
    public function untranslatedIds(string $lang, int $limit): array
    {
        return array_map('intval', $this->db->column(
            "SELECT n.id FROM news_items n
             WHERE n.status = 'published' AND n.collection_item_id IS NOT NULL
               AND NOT EXISTS (SELECT 1 FROM collection_item_translations t WHERE t.item_id = n.collection_item_id AND t.lang = ?)
             ORDER BY n.published_at, n.id LIMIT " . max(0, $limit),
            [$lang],
        ));
    }

    public function markFailed(int $id, string $error): void
    {
        $this->db->update('news_items', ['status' => 'failed', 'error' => mb_substr($error, 0, 1000)], 'id = ?', [$id]);
    }
}
