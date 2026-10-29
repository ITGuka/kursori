<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\NewsFeed;
use App\Services\News\NewsFetcher;
use Core\Console\Command;

/**
 * სიახლეების წამოღება dev.to-დან news_items-ში (ყველა აქტიური feed ან ერთი id-ით).
 */
final class NewsFetchCommand extends Command
{
    protected string $name = 'news:fetch';
    protected string $description = 'Fetch new articles from dev.to into the news queue';
    protected string $usage = '[feed id] [--limit=N] [--dry-run]';

    public function handle(array $args, array $options): int
    {
        $feeds = $this->app->make(NewsFeed::class);
        if (isset($args[0])) {
            $feed = $feeds->find((int)$args[0]);
            if ($feed === null) {
                $this->output->error("Feed {$args[0]} not found.");
                return 1;
            }
            $list = [$feed];
        } else {
            $list = $feeds->active();
        }
        if ($list === []) {
            $this->output->info('No active feeds.');
            return 0;
        }

        $fetcher = $this->app->make(NewsFetcher::class);
        $dryRun = isset($options['dry-run']);
        $failed = 0;

        // --limit=N — ყველა feed-იდან ერთად N ყველაზე პოპულარული (და არა per_run თითოეულიდან)
        if (isset($options['limit'])) {
            $result = $fetcher->fetchTop($list, max(1, (int)$options['limit']), $dryRun);
            foreach ($result['saved'] as $item) {
                $this->output->line(sprintf('  %s %s: %s (%d reactions)', $dryRun ? 'would save' : 'saved', $item['feed'], $item['title'], $item['reactions']));
            }
            foreach ($result['errors'] as $feed => $error) {
                $this->output->error("  $feed — $error");
            }
            $this->output->info(sprintf('Done: %d %s.', count($result['saved']), $dryRun ? 'would be saved' : 'saved'));
            return $result['errors'] !== [] ? 1 : 0;
        }
        foreach ($list as $feed) {
            $stats = $fetcher->fetch($feed, $dryRun);
            $line = sprintf('  #%d %s: %d candidates, %d %s', $feed['id'], $feed['name'], $stats['candidates'], $stats['saved'], $dryRun ? 'would be saved' : 'saved');
            if ($stats['error'] !== null) {
                $failed++;
                $this->output->error($line . ' — ' . $stats['error']);
            } else {
                $this->output->line($line);
            }
        }
        $this->output->info('Done.');
        return $failed > 0 ? 1 : 0;
    }
}
