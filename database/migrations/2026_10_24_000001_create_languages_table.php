<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE languages (
            id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(5) NOT NULL UNIQUE,
            name VARCHAR(50) NOT NULL,
            native_name VARCHAR(50) NOT NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            ' . self::TIMESTAMPS . '
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS languages');
    }
};
