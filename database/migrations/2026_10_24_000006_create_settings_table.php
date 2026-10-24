<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// Core\Settings\Settings-ის მნიშვნელობები; განსაზღვრებები — config/settings.php.
// lang = '' არათარგმნადი ველისთვის.
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE settings (
            setting_key VARCHAR(100) NOT NULL,
            lang VARCHAR(5) NOT NULL DEFAULT \'\',
            value MEDIUMTEXT NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (setting_key, lang)
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS settings');
    }
};
