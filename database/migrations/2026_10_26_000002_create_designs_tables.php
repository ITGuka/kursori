<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// საიტის დიზაინები შეკვეთის ფორმისთვის: ZIP შაბლონი (public/uploads/designs/{folder}/), სათაური ენებზე,
// კატეგორიები (categories, collection = 'designs') — რამდენიმე ერთ დიზაინს. Inbox-ის შეტყობინებებს — სტატუსი.
return new class extends Migration {
    public function up(DB $db): void
    {
        // folder — შაბლონის საქაღალდე uploads/designs/-ში, entry — index.html-ის გზა მის შიგნით
        $db->statement('CREATE TABLE designs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            image_id INT UNSIGNED NULL,
            folder VARCHAR(100) NULL,
            entry VARCHAR(255) NULL,
            files INT UNSIGNED NOT NULL DEFAULT 0,
            size INT UNSIGNED NOT NULL DEFAULT 0,
            sort SMALLINT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            ' . self::TIMESTAMPS . ',
            INDEX idx_designs (is_active, sort),
            CONSTRAINT fk_designs_image FOREIGN KEY (image_id) REFERENCES media (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE design_translations (
            design_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            title VARCHAR(190) NOT NULL,
            PRIMARY KEY (design_id, lang),
            CONSTRAINT fk_design_translations_design FOREIGN KEY (design_id) REFERENCES designs (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE design_category (
            design_id INT UNSIGNED NOT NULL,
            category_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (design_id, category_id),
            INDEX idx_design_category (category_id),
            CONSTRAINT fk_design_category_design FOREIGN KEY (design_id) REFERENCES designs (id) ON DELETE CASCADE,
            CONSTRAINT fk_design_category_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        $db->statement("ALTER TABLE form_submissions
            ADD status ENUM('new', 'in_progress', 'done') NOT NULL DEFAULT 'new' AFTER is_read,
            ADD INDEX idx_form_submissions_status (status)");
    }

    public function down(DB $db): void
    {
        $db->statement('ALTER TABLE form_submissions DROP INDEX idx_form_submissions_status, DROP COLUMN status');
        $db->statement('DROP TABLE IF EXISTS design_category');
        $db->statement('DROP TABLE IF EXISTS design_translations');
        $db->statement('DROP TABLE IF EXISTS designs');
    }
};
