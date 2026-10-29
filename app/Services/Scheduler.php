<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ScheduledJob;
use RuntimeException;

/**
 * ჯობის გაშვება ცალკე PHP პროცესში (php bin/console <command> <args>): shell-ის გარეშე (proc_open მასივით),
 * გამოსავალი — დროებით ფაილში (Windows-ზეც მუშაობს), ხანგრძლივობის ლიმიტით. ბრძანება — მხოლოდ config/schedule.php-დან.
 */
final class Scheduler
{
    private const MAX_OUTPUT = 65000;

    public function __construct(private readonly ScheduledJob $jobs, private readonly JobChecks $checks)
    {
    }

    /** @return list<string> დაშვებული ბრძანებები */
    public static function commands(): array
    {
        return array_keys((array)config('schedule.commands', []));
    }

    public static function validArgs(string $args): bool
    {
        return (bool)preg_match((string)config('schedule.args_pattern'), $args);
    }

    /**
     * ერთი ჯობი lock-ით; null — ჯობი სხვა პროცესში უკვე მუშაობს. $force = false — ჯერ შემოწმება (JobChecks):
     * სამუშაო არ არის → skipped (პროცესი არ იწყება, ჟურნალში არ იწერება).
     * @return array{success: bool, skipped: ?string, exit_code: ?int, output: string, seconds: int}|null
     */
    public function run(array $job, bool $force = false): ?array
    {
        $timeout = max(1, (int)config('schedule.timeout_minutes', 30));
        $now = date('Y-m-d H:i:s');
        // ჯაჭვის ჯობი შემდეგ ჯერზე მშობელს ელოდება (next_run_at = NULL), ინტერვალიანი — ინტერვალს
        $chained = $job['after_job_id'] !== null;
        $next = fn (int $from) => $chained ? null : date('Y-m-d H:i:s', $from + max(1, (int)$job['interval_minutes']) * 60);

        if (!$force && ($reason = $this->checks->reason((string)$job['command'])) !== null) {
            if ($job['running_since'] !== null && strtotime((string)$job['running_since']) > time() - ($timeout + 5) * 60) {
                return null; // მუშაობს — მდგომარეობას არ ვეხებით
            }
            $this->jobs->markSkipped((int)$job['id'], $reason, $now, $next(time()));
            return ['success' => true, 'skipped' => $reason, 'exit_code' => null, 'output' => '', 'seconds' => 0];
        }
        // წინა პროცესი timeout-ს + 5 წუთს გადასცდა და არ დასრულებულა — ჩავთვალოთ გათიშულად
        if (!$this->jobs->acquire((int)$job['id'], $now, date('Y-m-d H:i:s', time() - ($timeout + 5) * 60))) {
            return null;
        }
        $this->jobs->closeInterrupted((int)$job['id']);
        $runId = $this->jobs->startRun((int)$job['id'], $now);
        $started = time();

        try {
            [$exitCode, $output] = $this->execute((string)$job['command'], (string)$job['args'], $timeout * 60);
            $success = $exitCode === 0;
        } catch (RuntimeException $e) {
            [$exitCode, $output, $success] = [null, $e->getMessage(), false];
        }

        $this->jobs->finishRun((int)$job['id'], $runId, $success, $exitCode, $output, date('Y-m-d H:i:s'), $next($started));
        return ['success' => $success, 'skipped' => null, 'exit_code' => $exitCode, 'output' => $output, 'seconds' => time() - $started];
    }

    /** @return array{0: ?int, 1: string} გასვლის კოდი (null — შეწყდა timeout-ით) და გამოსავალი */
    private function execute(string $command, string $args, int $timeoutSeconds): array
    {
        if (!in_array($command, self::commands(), true)) {
            throw new RuntimeException("Command [$command] is not allowed in config/schedule.php.");
        }
        if (!self::validArgs($args)) {
            throw new RuntimeException("Invalid arguments [$args].");
        }
        if (!function_exists('proc_open')) {
            throw new RuntimeException('proc_open is disabled on this server.');
        }

        $argv = [PHP_BINARY, base_path('bin/console'), $command, ...preg_split('/\s+/', trim($args), -1, PREG_SPLIT_NO_EMPTY)];
        $log = tempnam(sys_get_temp_dir(), 'job');
        try {
            $process = proc_open($argv, [0 => ['pipe', 'r'], 1 => ['file', $log, 'w'], 2 => ['redirect', 1]], $pipes, base_path());
            if (!is_resource($process)) {
                throw new RuntimeException('Could not start the process.');
            }
            fclose($pipes[0]);

            $deadline = time() + $timeoutSeconds;
            $exitCode = null;
            while (true) {
                $status = proc_get_status($process);
                if (!$status['running']) {
                    $exitCode = $status['exitcode'];
                    break;
                }
                if (time() >= $deadline) {
                    proc_terminate($process);
                    break;
                }
                usleep(500_000);
            }
            proc_close($process);

            $output = (string)file_get_contents($log);
            if ($exitCode === null) {
                $output .= "\n[timeout: stopped after {$timeoutSeconds}s]";
            }
            if (strlen($output) > self::MAX_OUTPUT) {
                $output = '…' . substr($output, -self::MAX_OUTPUT);
            }
            return [$exitCode, trim(mb_convert_encoding($output, 'UTF-8', 'UTF-8'))];
        } finally {
            @unlink($log);
        }
    }
}
