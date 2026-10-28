<?php
declare(strict_types=1);

namespace App\Services\News;

use App\Models\NewsFeed;
use App\Models\NewsItem;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * feed-ის შევსება: ყოველი ტეგის top სტატიები → ფილტრი (ინგლისური, რეაქციები, ყდის სურათი, ჯერ არ შენახული)
 * → პოპულარობით პირველი per_run → სრული ტექსტი → news_items (status new). გამოქვეყნება — შემდეგი ეტაპი.
 */
final class NewsFetcher
{
    public const SOURCE = 'devto';

    private ?DevToClient $client = null;

    public function __construct(private readonly NewsFeed $feeds, private readonly NewsItem $items)
    {
    }

    /** @return array{candidates: int, saved: int, error: ?string} */
    public function fetch(array $feed, bool $dryRun = false): array
    {
        $candidates = [];
        $saved = 0;
        $error = null;
        try {
            foreach ($feed['tag_list'] as $tag) {
                foreach ($this->client()->articles($tag, $feed['top_days']) as $article) {
                    if ($this->accepts($feed, $article)) {
                        $candidates[(string)$article['id']] = $article;
                    }
                }
            }
            foreach ($this->items->existing(self::SOURCE, array_map('strval', array_keys($candidates))) as $id) {
                unset($candidates[$id]);
            }
            uasort($candidates, fn ($a, $b) => (int)$b['public_reactions_count'] <=> (int)$a['public_reactions_count']);
            $picked = array_slice($candidates, 0, $feed['per_run'], true);

            foreach ($picked as $article) {
                if (!$dryRun) {
                    $this->items->create($this->row($feed, $this->client()->article((int)$article['id'])));
                }
                $saved++;
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
        if (!$dryRun) {
            $this->feeds->markFetched($feed['id'], $error);
        }
        return ['candidates' => count($candidates), 'saved' => $saved, 'error' => $error];
    }

    private function accepts(array $feed, array $article): bool
    {
        return isset($article['id'], $article['title'], $article['url'])
            && ($article['language'] ?? 'en') === 'en'
            && (int)($article['public_reactions_count'] ?? 0) >= $feed['min_reactions']
            && (!$feed['require_image'] || !empty($article['cover_image']))
            && array_intersect(self::tags($article), $feed['exclude_list']) === [];
    }

    /** სიაში tag_list მასივია და tags — სტრიქონი, სრულ სტატიაში — პირიქით */
    private static function tags(array $article): array
    {
        foreach (['tags', 'tag_list'] as $key) {
            if (is_array($article[$key] ?? null)) {
                return NewsFeed::parseTags(implode(',', $article[$key]));
            }
        }
        return NewsFeed::parseTags((string)($article['tags'] ?? $article['tag_list'] ?? ''));
    }

    /** dev.to-ს სრული სტატია → news_items-ის სვეტები */
    private function row(array $feed, array $a): array
    {
        $user = (array)($a['user'] ?? []);
        $username = (string)($user['username'] ?? '');
        return [
            'feed_id'             => $feed['id'],
            'source'              => self::SOURCE,
            'external_id'         => (string)$a['id'],
            'url'                 => (string)$a['url'],
            'canonical_url'       => (string)(($a['canonical_url'] ?? '') ?: $a['url']),
            'title'               => mb_substr((string)$a['title'], 0, 300),
            'description'         => (string)($a['description'] ?? ''),
            'body_html'           => (string)($a['body_html'] ?? ''),
            'image_url'           => ($a['cover_image'] ?? '') ?: null,
            'tags'                => self::tags($a),
            'author_name'         => mb_substr((string)($user['name'] ?? $username), 0, 150),
            'author_url'          => $username !== '' ? 'https://dev.to/' . rawurlencode($username) : null,
            'reactions'           => (int)($a['public_reactions_count'] ?? 0),
            'reading_minutes'     => (int)($a['reading_time_minutes'] ?? 0),
            'source_published_at' => self::localTime($a['published_at'] ?? null),
        ];
    }

    private static function localTime(?string $iso): ?string
    {
        if (!$iso) {
            return null;
        }
        try {
            return (new DateTimeImmutable($iso))->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function client(): DevToClient
    {
        return $this->client ??= new DevToClient((string)setting('news.devto_api_key'));
    }
}
