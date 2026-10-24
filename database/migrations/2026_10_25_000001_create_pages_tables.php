<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE pages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            parent_id INT UNSIGNED NULL,
            status ENUM(\'draft\', \'published\') NOT NULL DEFAULT \'draft\',
            is_home TINYINT(1) NOT NULL DEFAULT 0,
            sort SMALLINT NOT NULL DEFAULT 0,
            og_image_id INT UNSIGNED NULL,
            ' . self::TIMESTAMPS . ',
            INDEX idx_pages_parent (parent_id, sort),
            CONSTRAINT fk_pages_parent FOREIGN KEY (parent_id) REFERENCES pages (id) ON DELETE SET NULL,
            CONSTRAINT fk_pages_og_image FOREIGN KEY (og_image_id) REFERENCES media (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);

        // slug უნიკალურია ენის ფარგლებში: /ka/about და /en/about სხვადასხვა გვერდი შეიძლება იყოს
        $db->statement('CREATE TABLE page_translations (
            page_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            title VARCHAR(190) NOT NULL,
            slug VARCHAR(150) NOT NULL,
            meta_title VARCHAR(190) NOT NULL DEFAULT \'\',
            meta_description VARCHAR(300) NOT NULL DEFAULT \'\',
            PRIMARY KEY (page_id, lang),
            UNIQUE KEY uq_page_slug (lang, slug),
            CONSTRAINT fk_page_translations_page FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        // settings — არათარგმნადი ველები (სურათები, ბმულები, რეპიტერის სტრუქტურა), media_ids — გამოყენებული მედია
        $db->statement('CREATE TABLE page_blocks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id INT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL,
            sort SMALLINT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            settings JSON NULL,
            media_ids JSON NULL,
            ' . self::TIMESTAMPS . ',
            INDEX idx_page_blocks_page (page_id, sort),
            CONSTRAINT fk_page_blocks_page FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE page_block_translations (
            block_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            content JSON NULL,
            PRIMARY KEY (block_id, lang),
            CONSTRAINT fk_page_block_translations_block FOREIGN KEY (block_id) REFERENCES page_blocks (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS page_block_translations');
        $db->statement('DROP TABLE IF EXISTS page_blocks');
        $db->statement('DROP TABLE IF EXISTS page_translations');
        $db->statement('DROP TABLE IF EXISTS pages');
    }
};
