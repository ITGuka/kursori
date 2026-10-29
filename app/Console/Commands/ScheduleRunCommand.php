<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ScheduledJob;
use App\Services\Scheduler;
use Core\Console\Command;

/**
 * cron-ის ერთადერთი ბრძანება (ყოველ 5 წუთში): უშვებს ჯობებს, რომელთა დროც მოვიდა, თითოს ცალკე პროცესში და lock-ით.
 * --job=ID — ერთი ჯობი ახლავე (გრაფიკის მიუხედავად), --list — ჯობები და მათი მდგომარეობა.
 */
final class ScheduleRunCommand extends Command
{
    protected string $name = 'schedule:run';
    protected string $description = 'Run due scheduled jobs (call from cron every 5 minutes)';
    protected string $usage = '[--job=ID] [--list]';

    public function handle(array $args, array $options): int
    {
        $jobs = $this->app->make(ScheduledJob::class);
        if (isset($options['list'])) {
            $this->output->table(['id', 'command', 'args', 'every', 'active', 'next run', 'last run', 'status', 'running'], array_map(fn (array $j) => [
                $j['id'], $j['command'], $j['args'], $j['interval_minutes'] . ' min', $j['is_active'] ? 'yes' : 'no',
                $j['next_run_at'] ?? 'now', $j['last_run_at'] ?? '—', $j['last_status'] ?? '—', $j['running_since'] ?? '',
            ], $jobs->all()));
            return 0;
        }

        if (isset($options['job'])) {
            $job = $jobs->find((int)$options['job']);
            if ($job === null) {
                $this->output->error("Job {$options['job']} not found.");
                return 1;
            }
            $due = [$job];
        } else {
            $due = $jobs->due(date('Y-m-d H:i:s'));
        }

        $scheduler = $this->app->make(Scheduler::class);
        $failed = 0;
        foreach ($due as $job) {
            $label = "#{$job['id']} {$job['command']}" . ($job['args'] !== '' ? " {$job['args']}" : '');
            $result = $scheduler->run($job);
            if ($result === null) {
                $this->output->warn("  $label: already running, skipped");
                continue;
            }
            $line = "  $label: " . ($result['success'] ? 'success' : 'failed') . " ({$result['seconds']}s)";
            if ($result['success']) {
                $this->output->line($line);
            } else {
                $failed++;
                $this->output->error($line);
            }
        }
        $jobs->pruneRuns(date('Y-m-d H:i:s', time() - max(1, (int)config('schedule.keep_runs_days', 30)) * 86400));
        return $failed > 0 ? 1 : 0;
    }
}
