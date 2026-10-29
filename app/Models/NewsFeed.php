<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * სიახლეების feed-ები: dev.to-ს ტეგები → ბლოგის კატეგორია, გამოსარიცხი ტეგები, პერიოდი, ყდის სურათის მოთხოვნა.
 */
final class NewsFeed
{
    public function __construct(private readonly DB $db)
    {
    }

    public function find(int $id): ?array
    {
        $feed = $id > 0 ? $this->db->first('SELECT * FROM news_feeds WHERE id = ?', [$id]) : null;
        return $feed === null ? null : self::cast($feed);
    }

    /** @return list<array> ყველა feed ადმინისთვის: + კატეგორიის სახელი და სიახლეების რაოდენობა სტატუსით */
    public function all(string $lang): array
    {
        $rows = $this->db->select(
            "SELECT f.*,
                    (SELECT name FROM category_translations t WHERE t.category_id = f.category_id ORDER BY t.lang = ? DESC LIMIT 1) AS category_name,
                    (SELECT COUNT(*) FROM news_items i WHERE i.feed_id = f.id) AS items_total,
                    (SELECT COUNT(*) FROM news_items i WHERE i.feed_id = f.id AND i.status = 'failed') AS items_failed,
                    (SELECT COUNT(*) FROM news_items i WHERE i.feed_id = f.id AND i.status = 'published') AS items_published,
                    (SELECT MAX(i.published_at) FROM news_items i WHERE i.feed_id = f.id) AS last_published_at
             FROM news_feeds f ORDER BY f.sort, f.id",
            [$lang],
        );
        return array_map([self::class, 'cast'], $rows);
    }

    /** @return array<int, string> id => სახელი (ფილტრისთვის) */
    public function options(): array
    {
        $options = [];
        foreach ($this->db->select('SELECT id, name FROM news_feeds ORDER BY sort, id') as $row) {
            $options[(int)$row['id']] = $row['name'];
        }
        return $options;
    }

    /** @param array<string, mixed> $data news_feeds-ის სვეტები */
    public function save(?int $id, array $data): int
    {
        if ($id === null) {
            $data['sort'] ??= (int)$this->db->value('SELECT COALESCE(MAX(sort), 0) + 1 FROM news_feeds');
            return $this->db->insert('news_feeds', $data);
        }
        $this->db->update('news_feeds', $data, 'id = ?', [$id]);
        return $id;
    }

    /** სიახლეები რჩება (feed_id → NULL), გამოქვეყნებული სტატიები ბლოგში — უცვლელად */
    public function delete(int $id): void
    {
        $this->db->delete('news_feeds', 'id = ?', [$id]);
    }

    /** @return list<int> აქტიური feed-ების ბლოგის კატეგორიები */
    public function activeCategories(): array
    {
        return array_map('intval', $this->db->column('SELECT DISTINCT category_id FROM news_feeds WHERE is_active = 1 AND category_id IS NOT NULL'));
    }

    /** აქტიური feed კატეგორიის გარეშე (მას დღიური შეზღუდვა არ აქვს) */
    public function hasActiveWithoutCategory(): bool
    {
        return (bool)$this->db->value('SELECT 1 FROM news_feeds WHERE is_active = 1 AND category_id IS NULL LIMIT 1');
    }

    /** @return list<array> აქტიური feed-ები რიგით (ერთ სტატიას პირველი შესაბამისი feed იღებს) */
    public function active(): array
    {
        return array_map([self::class, 'cast'], $this->db->select('SELECT * FROM news_feeds WHERE is_active = 1 ORDER BY sort, id'));
    }

    public function markFetched(int $id, ?string $error): void
    {
        $this->db->update('news_feeds', [
            'last_fetched_at' => date('Y-m-d H:i:s'),
            'last_error'      => $error === null ? null : mb_substr($error, 0, 500),
        ], 'id = ?', [$id]);
    }

    /** tags სტრიქონი → სუფთა სია (პატარა ასოები, [a-z0-9], დუბლიკატების გარეშე) */
    public static function parseTags(string $tags): array
    {
        $list = array_map(fn ($t) => preg_replace('/[^a-z0-9]/', '', strtolower($t)), explode(',', $tags));
        return array_values(array_unique(array_filter($list, fn ($t) => $t !== '')));
    }

    private static function cast(array $feed): array
    {
        $feed['tag_list'] = self::parseTags((string)$feed['tags']);
        $feed['exclude_list'] = self::parseTags((string)$feed['exclude_tags']);
        foreach (['id', 'top_days', 'sort'] as $key) {
            $feed[$key] = (int)$feed[$key];
        }
        $feed['category_id'] = $feed['category_id'] !== null ? (int)$feed['category_id'] : null;
        $feed['require_image'] = (bool)$feed['require_image'];
        $feed['is_active'] = (bool)$feed['is_active'];
        return $feed;
    }
}
