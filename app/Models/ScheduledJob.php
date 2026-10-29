<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * გრაფიკის ჯობები (scheduled_jobs) და მათი ჟურნალი (job_runs). lock — running_since (ატომური UPDATE).
 */
final class ScheduledJob
{
    public function __construct(private readonly DB $db)
    {
    }

    public function find(int $id): ?array
    {
        return $id > 0 ? $this->db->first('SELECT * FROM scheduled_jobs WHERE id = ?', [$id]) : null;
    }

    /** @return list<array> */
    public function all(): array
    {
        return $this->db->select('SELECT * FROM scheduled_jobs ORDER BY id');
    }

    public function update(int $id, string $args, int $intervalMinutes, bool $active): void
    {
        $this->db->update('scheduled_jobs', ['args' => $args, 'interval_minutes' => $intervalMinutes, 'is_active' => (int)$active], 'id = ?', [$id]);
    }

    /**
     * „ახლავე გაშვება“: ჯობი cron-ის შემდეგ ტიკზე (≤ 5 წთ) გაეშვება. ვებ-მოთხოვნიდან პროცესი არ იწყება —
     * გრძელი ჯობი (თარგმანი) PHP-ის ვებ ლიმიტს გადააჭარბებდა.
     */
    public function queueNow(int $id): void
    {
        $this->db->update('scheduled_jobs', ['next_run_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    /** @return int|null პირველი ჯობის id ამ ბრძანებით (მაგ. „გამოქვეყნება ახლავე“ რიგიდან) */
    public function idByCommand(string $command): ?int
    {
        $id = $this->db->value('SELECT id FROM scheduled_jobs WHERE command = ? ORDER BY is_active DESC, id LIMIT 1', [$command]);
        return $id === null ? null : (int)$id;
    }

    /** ბოლო გაშვების დრო ნებისმიერ ჯობზე — cron მუშაობს თუ არა */
    public function lastActivity(): ?string
    {
        $value = $this->db->value('SELECT MAX(started_at) FROM job_runs');
        return $value === null ? null : (string)$value;
    }

    public function findRun(int $id): ?array
    {
        return $this->db->first('SELECT * FROM job_runs WHERE id = ?', [$id]);
    }

    /** @return list<array> აქტიური ჯობები, რომელთა დროც მოვიდა (next_run_at ცარიელი — დაუყოვნებლივ) */
    public function due(string $now): array
    {
        return $this->db->select(
            'SELECT * FROM scheduled_jobs WHERE is_active = 1 AND (next_run_at IS NULL OR next_run_at <= ?) ORDER BY next_run_at, id',
            [$now],
        );
    }

    /**
     * lock-ის აღება: მხოლოდ თუ ჯობი არ მუშაობს ან წინა პროცესი $staleBefore-მდე დაიწყო (გაითიშა — მაგ. სერვერის ლიმიტით).
     * @return bool აიღო თუ არა ამ პროცესმა
     */
    public function acquire(int $id, string $now, string $staleBefore): bool
    {
        return $this->db->query(
            'UPDATE scheduled_jobs SET running_since = ? WHERE id = ? AND (running_since IS NULL OR running_since < ?)',
            [$now, $id, $staleBefore],
        )->rowCount() === 1;
    }

    /** შეწყვეტილი (დაუსრულებელი) გაშვებები — failed */
    public function closeInterrupted(int $id): void
    {
        $this->db->query(
            "UPDATE job_runs SET status = 'failed', finished_at = started_at, output = CONCAT(COALESCE(output, ''), '\n[interrupted: process did not finish]')
             WHERE job_id = ? AND status = 'running'",
            [$id],
        );
    }

    public function startRun(int $id, string $now): int
    {
        return $this->db->insert('job_runs', ['job_id' => $id, 'started_at' => $now]);
    }

    public function finishRun(int $jobId, int $runId, bool $success, ?int $exitCode, string $output, string $finishedAt, string $nextRunAt): void
    {
        $status = $success ? 'success' : 'failed';
        $this->db->transaction(function (DB $db) use ($jobId, $runId, $status, $exitCode, $output, $finishedAt, $nextRunAt): void {
            $db->update('job_runs', ['status' => $status, 'exit_code' => $exitCode, 'output' => $output, 'finished_at' => $finishedAt], 'id = ?', [$runId]);
            $db->update('scheduled_jobs', [
                'running_since' => null, 'last_run_at' => $finishedAt, 'last_status' => $status, 'next_run_at' => $nextRunAt,
            ], 'id = ?', [$jobId]);
        });
    }

    /** @return list<array> ჯობის ბოლო გაშვებები */
    public function runs(int $jobId, int $limit = 20): array
    {
        return $this->db->select('SELECT * FROM job_runs WHERE job_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit), [$jobId]);
    }

    public function pruneRuns(string $before): int
    {
        return $this->db->delete('job_runs', "started_at < ? AND status <> 'running'", [$before]);
    }
}
