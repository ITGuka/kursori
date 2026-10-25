<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * Dashboard-ის შეჯამებები: რაოდენობები, დღიური დინამიკა, ბოლოს დამატებული ჩანაწერები.
 */
final class Dashboard
{
    public function __construct(private readonly DB $db)
    {
    }

    /** @return array{total: int, published: int} */
    public function pages(): array
    {
        $row = $this->db->first("SELECT COUNT(*) total, COALESCE(SUM(status = 'published'), 0) published FROM pages");
        return ['total' => (int)$row['total'], 'published' => (int)$row['published']];
    }

    /** @return array{count: int, size: int} */
    public function media(): array
    {
        $row = $this->db->first('SELECT COUNT(*) n, COALESCE(SUM(size), 0) size FROM media');
        return ['count' => (int)$row['n'], 'size' => (int)$row['size']];
    }

    /** @return array<string, array{total: int, active: int}> კოლექცია => რაოდენობები */
    public function collections(): array
    {
        $counts = [];
        foreach ($this->db->select('SELECT collection, COUNT(*) total, SUM(is_active) active FROM collection_items GROUP BY collection') as $row) {
            $counts[$row['collection']] = ['total' => (int)$row['total'], 'active' => (int)$row['active']];
        }
        return $counts;
    }

    /**
     * ბოლო $days დღე: შემოსული შეტყობინებები და ახალი გამომწერები დღეების მიხედვით.
     * @return array{labels: list<string>, submissions: list<int>, subscribers: list<int>}
     */
    public function daily(int $days = 30): array
    {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $series = fn (string $table) => array_column(
            $this->db->select("SELECT DATE(created_at) d, COUNT(*) n FROM $table WHERE created_at >= ? GROUP BY DATE(created_at)", [$from]),
            'n',
            'd',
        );
        $submissions = $series('form_submissions');
        $subscribers = $series('subscribers');

        $result = ['labels' => [], 'submissions' => [], 'subscribers' => []];
        for ($i = 0; $i < $days; $i++) {
            $day = date('Y-m-d', strtotime("$from +$i days"));
            $result['labels'][] = date('d.m', strtotime($day));
            $result['submissions'][] = (int)($submissions[$day] ?? 0);
            $result['subscribers'][] = (int)($subscribers[$day] ?? 0);
        }
        return $result;
    }

    /** @return list<array> კოლექციის ბოლოს დამატებული ჩანაწერები: id, title, image_id, is_active, created_at, category_name */
    public function latestItems(string $collection, string $lang, int $limit = 5): array
    {
        return $this->db->select(
            "SELECT i.id, i.image_id, i.is_active, i.created_at,
                    COALESCE((SELECT title FROM collection_item_translations t WHERE t.item_id = i.id AND t.lang = ?),
                             (SELECT title FROM collection_item_translations t WHERE t.item_id = i.id LIMIT 1), CONCAT('#', i.id)) AS title,
                    (SELECT name FROM category_translations c WHERE c.category_id = i.category_id ORDER BY c.lang = ? DESC LIMIT 1) AS category_name
             FROM collection_items i WHERE i.collection = ? ORDER BY i.created_at DESC, i.id DESC LIMIT " . max(1, $limit),
            [$lang, $lang, $collection],
        );
    }

    /** @return list<array> ბოლოს შეცვლილი გვერდები: id, title, status, is_home, updated_at, blocks */
    public function latestPages(string $lang, int $limit = 5): array
    {
        return $this->db->select(
            "SELECT p.id, p.status, p.is_home, p.updated_at,
                    COALESCE((SELECT title FROM page_translations t WHERE t.page_id = p.id AND t.lang = ?),
                             (SELECT title FROM page_translations t WHERE t.page_id = p.id LIMIT 1), CONCAT('#', p.id)) AS title,
                    (SELECT COUNT(*) FROM page_blocks b WHERE b.page_id = p.id) AS blocks
             FROM pages p ORDER BY p.updated_at DESC, p.id DESC LIMIT " . max(1, $limit),
            [$lang],
        );
    }

    /** @return list<array> ბოლო შეტყობინებები Inbox-იდან (data — მასივი) */
    public function latestSubmissions(int $limit = 4): array
    {
        $rows = $this->db->select('SELECT id, form, lang, data, is_read, created_at FROM form_submissions ORDER BY id DESC LIMIT ' . max(1, $limit));
        foreach ($rows as &$row) {
            $row['data'] = (array)json_decode($row['data'], true);
        }
        return $rows;
    }
}
