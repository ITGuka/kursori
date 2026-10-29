<?php
declare(strict_types=1);

namespace App\Services\News;

use App\Models\NewsFeed;
use App\Models\NewsItem;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * სტატიების შერჩევა dev.to-დან: feed-ის ტეგების top სტატიები (ბოლო top_days დღე) → ფილტრი (ინგლისური, > 30 რეაქცია,
 * ყდის სურათი, გამოსარიცხი ტეგები, ჯერ არ შენახული) → უახლესი პირველი. შენახვა — save() (news_items), გამოქვეყნება — NewsPublisher.
 */
final class NewsFetcher
{
    public const SOURCE = 'devto';
    /** სტატიას უნდა ჰქონდეს ამაზე მეტი რეაქცია */
    public const REACTIONS_ABOVE = 30;

    private ?DevToClient $client = null;

    public function __construct(private readonly NewsFeed $feeds, private readonly NewsItem $items)
    {
    }

    /**
     * ყველა feed-ის კანდიდატები ერთად, უახლესი პირველი; $skipCategories — დღეს უკვე გამოქვეყნებული კატეგორიები.
     * ერთი სტატია რამდენიმე feed-ს თუ ემთხვევა, იღებს პირველი რიგით. feed-ის შეცდომა (მაგ. API) — last_error-ში.
     * @param list<array> $feeds
     * @param list<int> $skipCategories
     * @return list<array{feed: array, article: array}>
     */
    public function candidatesFromAll(array $feeds, array $skipCategories = [], bool $markFetched = true): array
    {
        $pool = [];
        foreach ($feeds as $feed) {
            if ($feed['category_id'] !== null && in_array($feed['category_id'], $skipCategories, true)) {
                continue;
            }
            $error = null;
            try {
                foreach ($this->candidates($feed) as $id => $article) {
                    $pool[$id] ??= ['feed' => $feed, 'article' => $article];
                }
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
            if ($markFetched) {
                $this->feeds->markFetched($feed['id'], $error);
            }
        }
        uasort($pool, fn ($a, $b) => strcmp((string)($b['article']['published_at'] ?? ''), (string)($a['article']['published_at'] ?? '')));
        return array_values($pool);
    }

    /**
     * feed-ის კანდიდატები ფილტრის შემდეგ, ჯერ არ შენახული, უახლესი პირველი. ადმინის „ტესტი“ ამას აჩვენებს — არაფერს ინახავს.
     * @return array<string, array> external id => dev.to-ს სიის ჩანაწერი
     */
    public function candidates(array $feed): array
    {
        $candidates = [];
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
        uasort($candidates, fn ($a, $b) => strcmp((string)($b['published_at'] ?? ''), (string)($a['published_at'] ?? '')));
        return $candidates;
    }

    /** სრული სტატია dev.to-დან → news_items (status new); @return int ჩანაწერის id */
    public function save(array $feed, int $articleId): int
    {
        return $this->items->create($this->row($feed, $this->client()->article($articleId)));
    }

    private function accepts(array $feed, array $article): bool
    {
        return isset($article['id'], $article['title'], $article['url'])
            && ($article['language'] ?? 'en') === 'en'
            && (int)($article['public_reactions_count'] ?? 0) > self::REACTIONS_ABOVE
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
