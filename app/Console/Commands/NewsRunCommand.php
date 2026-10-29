<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Middleware\PageCache;
use App\Models\NewsFeed;
use App\Models\NewsItem;
use App\Services\News\NewsFetcher;
use App\Services\News\NewsPublisher;
use Core\Console\Command;
use Core\Support\Cache;
use Throwable;

/**
 * სიახლეების ერთადერთი ჯობი (6 სთ, D57): ყველა აქტიური feed-იდან უახლესი სტატია > 30 რეაქციით იმ კატეგორიიდან,
 * რომელშიც დღეს ჯერ არაფერი გამოქვეყნებულა → თარგმანი → ბლოგში. თარგმანი თუ ჩავარდა, სტატია ინგლისურად ქვეყნდება
 * და ქართული შემდეგ გაშვებებზე იცდება. გამოქვეყნება თუ ჩავარდა (მაგ. სურათი), იღებს შემდეგ კანდიდატს.
 */
final class NewsRunCommand extends Command
{
    private const ATTEMPTS = 3;
    private const TRANSLATION_RETRIES = 2;

    protected string $name = 'news:run';
    protected string $description = 'Publish the newest dev.to article (> 30 reactions, one per category per day) with translation';
    protected string $usage = '[--dry-run]';

    public function handle(array $args, array $options): int
    {
        $news = $this->app->make(NewsItem::class);
        $publisher = $this->app->make(NewsPublisher::class);
        $dryRun = isset($options['dry-run']);

        // წინა გაშვებებზე უთარგმნელად დარჩენილი
        if (!$dryRun && setting('news.translate') === true && (string)setting('news.claude_api_key') !== '') {
            foreach ($news->untranslatedIds(NewsPublisher::TRANSLATION_LANG, self::TRANSLATION_RETRIES) as $id) {
                try {
                    $this->output->line("  #$id: " . ($publisher->translate($id) ? 'translated' : 'translation skipped'));
                } catch (Throwable $e) {
                    $news->setError($id, 'Translation: ' . $e->getMessage());
                    $this->output->error("  #$id: translation failed — " . $e->getMessage());
                }
            }
        }

        $feeds = $this->app->make(NewsFeed::class)->active();
        $candidates = $this->app->make(NewsFetcher::class)->candidatesFromAll($feeds, $news->categoriesPublishedToday(), !$dryRun);
        if ($candidates === []) {
            $this->output->info('No new article: nothing above ' . NewsFetcher::REACTIONS_ABOVE . ' reactions in categories without a post today.');
            return 0;
        }
        if ($dryRun) {
            foreach (array_slice($candidates, 0, 5) as $i => ['feed' => $feed, 'article' => $article]) {
                $this->output->line(sprintf('  %s %s: %s (%d reactions, %s)', $i === 0 ? '→' : ' ', $feed['name'], $article['title'],
                    $article['public_reactions_count'], substr((string)($article['published_at'] ?? ''), 0, 10)));
            }
            return 0;
        }

        $fetcher = $this->app->make(NewsFetcher::class);
        $exit = 1;
        foreach (array_slice($candidates, 0, self::ATTEMPTS) as ['feed' => $feed, 'article' => $article]) {
            try {
                $id = $fetcher->save($feed, (int)$article['id']);
                $blogId = $publisher->publish($id);
                $error = $news->find($id)['error'] ?? null;
                $this->output->line("  {$feed['name']}: {$article['title']} → blog item #$blogId" . ($error ? " ($error)" : ''));
                $exit = 0;
                break;
            } catch (Throwable $e) {
                $this->output->error("  {$feed['name']}: {$article['title']} — " . $e->getMessage());
            }
        }
        $this->app->make(Cache::class)->flush(PageCache::PREFIX);
        return $exit;
    }
}
