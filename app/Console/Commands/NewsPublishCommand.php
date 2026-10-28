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
 * დამტკიცებული სიახლეების გამოქვეყნება ბლოგში (დღიური ლიმიტით — news.daily_limit) ან ერთის — id-ით, სტატუსის მიუხედავად.
 */
final class NewsPublishCommand extends Command
{
    protected string $name = 'news:publish';
    protected string $description = 'Publish approved news items to the blog (respects the daily limit)';
    protected string $usage = '[news item id]';

    public function handle(array $args, array $options): int
    {
        $news = $this->app->make(NewsItem::class);
        if (isset($args[0])) {
            $ids = [(int)$args[0]];
        } else {
            $limit = (int)setting('news.daily_limit');
            $remaining = $limit > 0 ? max(0, $limit - $news->publishedToday()) : 1000;
            $ids = $news->approvedIds($remaining);
            if ($ids === []) {
                $this->output->info($remaining === 0 ? 'Daily limit reached.' : 'Nothing to publish.');
                return 0;
            }
        }

        $publisher = $this->app->make(NewsPublisher::class);
        $failed = 0;
        foreach ($ids as $id) {
            try {
                $blogId = $publisher->publish($id);
                $this->output->line("  #$id → blog item #$blogId");
            } catch (Throwable $e) {
                $failed++;
                $this->output->error("  #$id: " . $e->getMessage());
            }
        }
        // ახალი სტატია სიაში (blog_list) და sitemap-ში — გვერდის ქეში იშლება, როგორც ადმინში შენახვისას
        $this->app->make(Cache::class)->flush(PageCache::PREFIX);
        $this->output->info('Done.');
        return $failed > 0 ? 1 : 0;
    }
}
