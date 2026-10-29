<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ScheduledJob;
use App\Services\Scheduler;
use Core\Console\Command;

/**
 * cron-ის ერთადერთი ბრძანება (ყოველ 5 წუთში): უშვებს ჯობებს, რომელთა დროც მოვიდა, თითოს ცალკე პროცესში და lock-ით.
 * ჯობის შემდეგ ეშვება მისი ჯაჭვი (after_job_id); გაშვებამდე — შემოწმება (JobChecks), სამუშაოს გარეშე ჯობი გამოტოვდება.
 * --job=ID — ერთი ჯობი ახლავე (გრაფიკის მიუხედავად, + ჯაჭვი), --force — --job-ს შემოწმების გარეშე, --list — მდგომარეობა.
 */
final class ScheduleRunCommand extends Command
{
    protected string $name = 'schedule:run';
    protected string $description = 'Run due scheduled jobs and their chains (call from cron every 5 minutes)';
    protected string $usage = '[--job=ID [--force]] [--list]';

    public function handle(array $args, array $options): int
    {
        $jobs = $this->app->make(ScheduledJob::class);
        if (isset($options['list'])) {
            $this->output->table(['id', 'command', 'args', 'every', 'active', 'next run', 'last run', 'status', 'running'], array_map(fn (array $j) => [
                $j['id'], $j['command'], $j['args'], $j['after_job_id'] !== null ? "after #{$j['after_job_id']}" : $j['interval_minutes'] . ' min',
                $j['is_active'] ? 'yes' : 'no', $j['next_run_at'] ?? ($j['after_job_id'] !== null ? '—' : 'now'), $j['last_run_at'] ?? '—',
                ($j['last_status'] ?? '—') . ($j['last_message'] ? ": {$j['last_message']}" : ''), $j['running_since'] ?? '',
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
        $force = isset($options['force']);
        $failed = 0;
        $done = [];
        // რიგი: მოსული ჯობები + მათი ჯაჭვი (მაგ. წამოღება → გამოქვეყნება, თარგმანი) — იმავე ტიკში, თანმიმდევრულად
        while (($job = array_shift($due)) !== null) {
            if (isset($done[$job['id']])) {
                continue;
            }
            $done[$job['id']] = true;
            $label = "#{$job['id']} {$job['command']}" . ($job['args'] !== '' ? " {$job['args']}" : '');
            $result = $scheduler->run($job, $force && count($done) === 1 && isset($options['job']));
            if ($result === null) {
                $this->output->warn("  $label: already running, skipped");
                continue;
            }
            if ($result['skipped'] !== null) {
                $this->output->line("  $label: nothing to do — {$result['skipped']}");
            } elseif ($result['success']) {
                $this->output->line("  $label: success ({$result['seconds']}s)");
            } else {
                $failed++;
                $this->output->error("  $label: failed ({$result['seconds']}s)");
            }
            // მშობელი დამუშავდა (გაეშვა ან გამოტოვდა) — შემდეგი ჯაჭვში; მათი შემოწმება ზედმეტ სამუშაოს აცილებს
            foreach ($jobs->dependents((int)$job['id']) as $dependent) {
                $due[] = $dependent;
            }
        }
        $jobs->pruneRuns(date('Y-m-d H:i:s', time() - max(1, (int)config('schedule.keep_runs_days', 30)) * 86400));
        return $failed > 0 ? 1 : 0;
    }
}
