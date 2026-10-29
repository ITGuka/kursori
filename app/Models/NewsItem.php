<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * dev.to-დან აღებული სიახლეები (news_items): new (შენახვისთანავე) → published ან failed (news:run, D57).
 * rejected — მოდერაციის ძველი რიგიდან (აღარ ქვეყნდება, ხელახლა არ წამოვა). approved აღარ გამოიყენება.
 */
final class NewsItem
{
    /** ადმინში ნაჩვენები სტატუსები => [წარწერა, Bootstrap ფერი] */
    public const STATUSES = [
        'published' => ['გამოქვეყნებული', 'success'],
        'failed'    => ['შეცდომა', 'danger'],
        'rejected'  => ['უარყოფილი', 'secondary'],
    ];

    public function __construct(private readonly DB $db)
    {
    }

    /**
     * @param array{status?: string, feed?: int, q?: string} $filters
     * @return array{rows: list<array>, total: int}
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        $where = ['1 = 1'];
        $bindings = [];
        if (($filters['status'] ?? '') !== '') {
            $where[] = 'i.status = ?';
            $bindings[] = $filters['status'];
        }
        if (!empty($filters['feed'])) {
            $where[] = 'i.feed_id = ?';
            $bindings[] = (int)$filters['feed'];
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = 'i.title LIKE ?';
            $bindings[] = '%' . addcslashes($filters['q'], '%_\\') . '%';
        }
        $whereSql = implode(' AND ', $where);
        $total = (int)$this->db->value("SELECT COUNT(*) FROM news_items i WHERE $whereSql", $bindings);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $this->db->select(
            "SELECT i.id, i.feed_id, i.url, i.title, i.description, i.image_url, i.tags, i.author_name, i.reactions, i.reading_minutes,
                    i.source_published_at, i.status, i.collection_item_id, i.published_at, i.error, i.created_at, f.name AS feed_name,
                    (SELECT GROUP_CONCAT(lang) FROM collection_item_translations t WHERE t.item_id = i.collection_item_id) AS langs
             FROM news_items i LEFT JOIN news_feeds f ON f.id = i.feed_id
             WHERE $whereSql ORDER BY i.created_at DESC, i.id DESC LIMIT $perPage OFFSET $offset",
            $bindings,
        );
        foreach ($rows as &$row) {
            $row['tags'] = (array)json_decode((string)$row['tags'], true);
            $row['langs'] = $row['langs'] ? explode(',', $row['langs']) : [];
        }
        return ['rows' => $rows, 'total' => $total];
    }

    /** @return array<string, int> სტატუსი => რაოდენობა */
    public function counts(): array
    {
        $counts = array_fill_keys(array_keys(self::STATUSES), 0);
        foreach ($this->db->select('SELECT status, COUNT(*) AS c FROM news_items GROUP BY status') as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int)$row['c'];
            }
        }
        return $counts;
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

    /** @return list<int> ბლოგის კატეგორიები, რომლებშიც დღეს სიახლე უკვე გამოქვეყნდა (კატეგორიაში დღეში ერთი) */
    public function categoriesPublishedToday(): array
    {
        return array_map('intval', $this->db->column(
            'SELECT DISTINCT c.category_id FROM news_items i JOIN collection_items c ON c.id = i.collection_item_id
             WHERE i.published_at >= ? AND c.category_id IS NOT NULL',
            [date('Y-m-d 00:00:00')],
        ));
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
            // სიგრძის ლიმიტით გამოტოვებული ხელახლა არ იცდება (ხარჯი; ცალკე — news:translate <id>)
            "SELECT n.id FROM news_items n
             WHERE n.status = 'published' AND n.collection_item_id IS NOT NULL
               AND (n.error IS NULL OR n.error NOT LIKE 'Translation skipped%')
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
