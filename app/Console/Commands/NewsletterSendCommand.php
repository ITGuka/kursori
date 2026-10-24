<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\Newsletter;
use Core\Console\Command;
use Core\Database\DB;

/**
 * „გაგზავნის“ რეჟიმში მყოფი კამპანიების გაგრძელება CLI-დან (მაგ. cron) — ბრაუზერის დახურვის შემთხვევისთვის.
 */
final class NewsletterSendCommand extends Command
{
    protected string $name = 'newsletter:send';
    protected string $description = 'Continue sending campaigns that are in "sending" state';
    protected string $usage = '[campaign id] [--batch=50]';

    public function handle(array $args, array $options): int
    {
        $db = $this->app->make(DB::class);
        $ids = isset($args[0]) ? [(int)$args[0]] : array_map('intval', $db->column("SELECT id FROM newsletter_campaigns WHERE status = 'sending'"));
        if ($ids === []) {
            $this->output->info('Nothing to send.');
            return 0;
        }

        $newsletter = $this->app->make(Newsletter::class);
        $batch = max(1, (int)($options['batch'] ?? 50));
        foreach ($ids as $id) {
            if ($this->app->make(Campaign::class)->find($id) === null) {
                $this->output->error("Campaign $id not found.");
                continue;
            }
            do {
                $stats = $newsletter->sendBatch($id, $batch);
                $this->output->line(sprintf('  #%d: %d/%d sent, %d failed', $id, $stats['sent'], $stats['total'], $stats['failed']));
            } while (!$stats['done']);
        }
        $this->output->info('Done.');
        return 0;
    }
}
