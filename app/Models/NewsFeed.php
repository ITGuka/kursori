<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * სიახლეების feed-ები: წყაროს ტეგები → ბლოგის კატეგორია, გამოქვეყნების რეჟიმი და შერჩევის ფილტრები.
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
        foreach (['id', 'per_run', 'top_days', 'min_reactions', 'sort'] as $key) {
            $feed[$key] = (int)$feed[$key];
        }
        $feed['category_id'] = $feed['category_id'] !== null ? (int)$feed['category_id'] : null;
        $feed['require_image'] = (bool)$feed['require_image'];
        $feed['is_active'] = (bool)$feed['is_active'];
        return $feed;
    }
}
