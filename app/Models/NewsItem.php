<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * წამოღებული სიახლეები (news_items): new → approved → published, ან rejected / failed.
 */
final class NewsItem
{
    /** სტატუსი => [წარწერა, Bootstrap ფერი] */
    public const STATUSES = [
        'new'       => ['მოდერაციაში', 'warning'],
        'approved'  => ['გამოსაქვეყნებელი', 'info'],
        'published' => ['გამოქვეყნებული', 'success'],
        'rejected'  => ['უარყოფილი', 'secondary'],
        'failed'    => ['შეცდომა', 'danger'],
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
            $counts[$row['status']] = (int)$row['c'];
        }
        return $counts;
    }

    /**
     * სტატუსის შეცვლა მოდერაციიდან. დასაშვები გადასვლები: new/rejected/failed → approved, new/approved/failed → rejected.
     * @param list<int> $ids
     * @return int შეცვლილი ჩანაწერები
     */
    public function moderate(array $ids, string $status): int
    {
        $from = match ($status) {
            'approved' => ['new', 'rejected', 'failed'],
            'rejected' => ['new', 'approved', 'failed'],
            default    => [],
        };
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === [] || $from === []) {
            return 0;
        }
        $in = implode(', ', array_fill(0, count($ids), '?'));
        $states = implode(', ', array_fill(0, count($from), '?'));
        return $this->db->query(
            "UPDATE news_items SET status = ?, error = NULL WHERE id IN ($in) AND status IN ($states)",
            [$status, ...$ids, ...$from],
        )->rowCount();
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
