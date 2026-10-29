<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// სიახლეების გამარტივება (D57): ერთი ჯობი news:run (6 სთ) — უახლესი სტატია > 30 რეაქციით, კატეგორიაში დღეში ერთი,
// თარგმანი და პირდაპირ გამოქვეყნება. ქრება: მოდერაცია (რიგში დარჩენილი → rejected), feed-ის რეჟიმი/რაოდენობა/რეაქციები,
// ჯობების ჯაჭვი, დღიური ლიმიტების პარამეტრები, ცალკე წამოღების/გამოქვეყნების/თარგმანის ჯობები.
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement("UPDATE news_items SET status = 'rejected' WHERE status IN ('new', 'approved')");

        $db->statement('ALTER TABLE news_feeds DROP COLUMN publish_mode, DROP COLUMN per_run, DROP COLUMN min_reactions');

        $db->statement("DELETE FROM scheduled_jobs WHERE command IN ('news:fetch', 'news:publish', 'news:translate')");
        $db->statement('ALTER TABLE scheduled_jobs DROP FOREIGN KEY fk_scheduled_jobs_after, DROP COLUMN after_job_id');
        if ($db->value("SELECT id FROM scheduled_jobs WHERE command = 'news:run'") === null) {
            $db->insert('scheduled_jobs', ['command' => 'news:run', 'args' => '', 'interval_minutes' => 360]);
        }

        $db->statement("DELETE FROM settings WHERE setting_key IN ('news.daily_limit', 'news.category_daily_limit')");
    }

    public function down(DB $db): void
    {
        $db->statement('ALTER TABLE scheduled_jobs ADD after_job_id INT UNSIGNED NULL AFTER interval_minutes,
            ADD CONSTRAINT fk_scheduled_jobs_after FOREIGN KEY (after_job_id) REFERENCES scheduled_jobs (id) ON DELETE SET NULL');
        $db->statement("ALTER TABLE news_feeds
            ADD publish_mode ENUM('auto', 'review') NOT NULL DEFAULT 'review' AFTER category_id,
            ADD per_run TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER publish_mode,
            ADD min_reactions SMALLINT UNSIGNED NOT NULL DEFAULT 25 AFTER top_days");
        // ჯობები და რიგის სტატუსები ადმინიდან / migrate:rollback-ის შემდეგ ხელით
    }
};
