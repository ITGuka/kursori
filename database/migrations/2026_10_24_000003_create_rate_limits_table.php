<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// Core\Support\RateLimiter-ის ცხრილი (login-ის ლიმიტი, მოგვიანებით — საჯარო ფორმები)
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE rate_limits (
            limiter_key CHAR(64) NOT NULL PRIMARY KEY,
            hits INT UNSIGNED NOT NULL DEFAULT 0,
            reset_at DATETIME NOT NULL,
            INDEX idx_rate_limits_reset (reset_at)
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS rate_limits');
    }
};
