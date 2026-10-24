<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// UI თარგმანები: გასაღები ერთხელ (group.key), მნიშვნელობა თითო ენაზე.
// ცალკე გასაღებების ცხრილი ადმინში ნაკლული თარგმანების ჩვენების საშუალებას იძლევა.
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE translation_keys (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `group` VARCHAR(50) NOT NULL,
            `key` VARCHAR(150) NOT NULL,
            description VARCHAR(255) NULL,
            ' . self::TIMESTAMPS . ',
            UNIQUE KEY uq_translation_keys (`group`, `key`)
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE translation_values (
            key_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            value TEXT NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (key_id, lang),
            INDEX idx_translation_values_lang (lang),
            CONSTRAINT fk_translation_values_key FOREIGN KEY (key_id) REFERENCES translation_keys (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS translation_values');
        $db->statement('DROP TABLE IF EXISTS translation_keys');
    }
};
