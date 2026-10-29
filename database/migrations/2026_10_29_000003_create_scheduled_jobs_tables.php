<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// ჯობების გრაფიკი (config/schedule.php, schedule:run): ერთი cron → ჯობები ინტერვალით, ადმინიდან სამართავი.
// running_since — lock (ერთი ჯობი ერთდროულად ერთხელ), job_runs — ჟურნალი (გამოსავალი, ხანგრძლივობა, კოდი).
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE scheduled_jobs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            command VARCHAR(60) NOT NULL,
            args VARCHAR(190) NOT NULL DEFAULT \'\',
            interval_minutes INT UNSIGNED NOT NULL DEFAULT 60,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            next_run_at DATETIME NULL,
            running_since DATETIME NULL,
            last_run_at DATETIME NULL,
            last_status ENUM(\'success\', \'failed\') NULL,
            ' . self::TIMESTAMPS . ',
            INDEX idx_scheduled_jobs_due (is_active, next_run_at)
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE job_runs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            job_id INT UNSIGNED NOT NULL,
            started_at DATETIME NOT NULL,
            finished_at DATETIME NULL,
            status ENUM(\'running\', \'success\', \'failed\') NOT NULL DEFAULT \'running\',
            exit_code SMALLINT NULL,
            output MEDIUMTEXT NULL,
            INDEX idx_job_runs_job (job_id, started_at),
            CONSTRAINT fk_job_runs_job FOREIGN KEY (job_id) REFERENCES scheduled_jobs (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        // საწყისი ჯობები — გრაფიკის მნიშვნელობები config/schedule.php-დან; პირველი გაშვება cron-ის პირველ ტიკზე
        foreach ((array)config('schedule.commands') as $command => $definition) {
            $db->insert('scheduled_jobs', [
                'command'          => $command,
                'args'             => $definition['default']['args'],
                'interval_minutes' => $definition['default']['interval'],
            ]);
        }
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS job_runs');
        $db->statement('DROP TABLE IF EXISTS scheduled_jobs');
    }
};
