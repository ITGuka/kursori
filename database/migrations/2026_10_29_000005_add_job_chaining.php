<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// ჯობების ჯაჭვი: after_job_id — ჯობი ეშვება არა ინტერვალით, არამედ მითითებული ჯობის შემდეგ (იმავე cron-ის ტიკში).
// გაშვებამდე შემოწმება: საჭირო არ არის → last_status = skipped, მიზეზი last_message-ში (job_runs-ში არ იწერება).
// გამოქვეყნება და თარგმანი — სიახლეების წამოღების შემდეგ.
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement("ALTER TABLE scheduled_jobs
            ADD after_job_id INT UNSIGNED NULL AFTER interval_minutes,
            ADD last_message VARCHAR(255) NULL AFTER last_status,
            MODIFY last_status ENUM('success', 'failed', 'skipped') NULL,
            ADD CONSTRAINT fk_scheduled_jobs_after FOREIGN KEY (after_job_id) REFERENCES scheduled_jobs (id) ON DELETE SET NULL");

        $fetch = $db->value("SELECT id FROM scheduled_jobs WHERE command = 'news:fetch' ORDER BY id LIMIT 1");
        if ($fetch !== null) {
            $db->query(
                "UPDATE scheduled_jobs SET after_job_id = ?, next_run_at = NULL WHERE command IN ('news:publish', 'news:translate')",
                [(int)$fetch],
            );
        }
    }

    public function down(DB $db): void
    {
        $db->statement("UPDATE scheduled_jobs SET last_status = NULL WHERE last_status = 'skipped'");
        $db->statement("ALTER TABLE scheduled_jobs
            DROP FOREIGN KEY fk_scheduled_jobs_after,
            DROP COLUMN after_job_id,
            DROP COLUMN last_message,
            MODIFY last_status ENUM('success', 'failed') NULL");
    }
};
