<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Middleware\PageCache;
use App\Models\NewsItem;
use App\Services\News\NewsPublisher;
use Core\Console\Command;
use Core\Support\Cache;
use Throwable;

/**
 * გამოქვეყნებული სიახლეების ქართული თარგმანი (Claude): id-ით ან ყველა ჯერ უთარგმნელი (--limit-ით), ძველი პირველი.
 * გამოქვეყნებისას თარგმანი ავტომატურად ეშვება — ეს ბრძანება ხელახალი ცდისა და ძველი სტატიებისთვისაა.
 */
final class NewsTranslateCommand extends Command
{
    protected string $name = 'news:translate';
    protected string $description = 'Translate published news into Georgian with Claude (retries untranslated ones)';
    protected string $usage = '[news item id] [--limit=5]';

    public function handle(array $args, array $options): int
    {
        if (setting('news.translate') !== true || (string)setting('news.claude_api_key') === '') {
            $this->output->warn('Translation is off or the Claude API key is not set (Settings → News).');
            return 1;
        }
        $ids = isset($args[0])
            ? [(int)$args[0]]
            : $this->app->make(NewsItem::class)->untranslatedIds(NewsPublisher::TRANSLATION_LANG, max(1, (int)($options['limit'] ?? 5)));
        if ($ids === []) {
            $this->output->info('Nothing to translate.');
            return 0;
        }

        $publisher = $this->app->make(NewsPublisher::class);
        $news = $this->app->make(NewsItem::class);
        $failed = 0;
        foreach ($ids as $id) {
            try {
                $done = $publisher->translate($id);
                $this->output->line("  #$id: " . ($done ? 'translated' : 'skipped — ' . ($news->find($id)['error'] ?? 'not published')));
            } catch (Throwable $e) {
                $failed++;
                $news->setError($id, 'Translation: ' . $e->getMessage());
                $this->output->error("  #$id: " . $e->getMessage());
            }
        }
        $this->app->make(Cache::class)->flush(PageCache::PREFIX);
        $this->output->info('Done.');
        return $failed > 0 ? 1 : 0;
    }
}
