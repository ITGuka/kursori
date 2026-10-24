<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // from_path — საიტის ბილიკი base path-ისა და query-ის გარეშე (/ka/dzveli-gverdi);
        // to_url — შიდა ბილიკი (/ka/akhali) ან სრული https:// ბმული
        $db->statement('CREATE TABLE redirects (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            from_path VARCHAR(500) NOT NULL,
            to_url VARCHAR(2000) NOT NULL,
            code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            is_auto TINYINT(1) NOT NULL DEFAULT 0,
            hits INT UNSIGNED NOT NULL DEFAULT 0,
            last_hit_at DATETIME NULL,
            ' . self::TIMESTAMPS . ',
            UNIQUE KEY uq_redirects_from (from_path(191))
        ) ' . self::TABLE_OPTIONS);

        // 404 მისამართების ჟურნალი (ბმულების გასასწორებლად / გადამისამართების შესაქმნელად)
        $db->statement('CREATE TABLE not_found_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            path VARCHAR(500) NOT NULL,
            hits INT UNSIGNED NOT NULL DEFAULT 1,
            referer VARCHAR(500) NULL,
            first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_not_found_path (path(191)),
            INDEX idx_not_found_last (last_seen_at)
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS not_found_log');
        $db->statement('DROP TABLE IF EXISTS redirects');
    }
};
