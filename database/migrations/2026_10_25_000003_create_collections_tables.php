<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// კოლექციები (config/collections.php) და მათი კატეგორიები. slug-ის უნიკალურობა (კოლექცია + ენა) — კოდში.
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            collection VARCHAR(50) NOT NULL,
            parent_id INT UNSIGNED NULL,
            sort SMALLINT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            ' . self::TIMESTAMPS . ',
            INDEX idx_categories_collection (collection, parent_id, sort),
            CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE category_translations (
            category_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(150) NOT NULL,
            PRIMARY KEY (category_id, lang),
            INDEX idx_category_slug (lang, slug),
            CONSTRAINT fk_category_translations_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        // settings/media_ids — როგორც page_blocks-ში (დამატებითი ველები config-ის სქემით)
        $db->statement('CREATE TABLE collection_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            collection VARCHAR(50) NOT NULL,
            category_id INT UNSIGNED NULL,
            image_id INT UNSIGNED NULL,
            sort SMALLINT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            settings JSON NULL,
            media_ids JSON NULL,
            ' . self::TIMESTAMPS . ',
            INDEX idx_collection_items (collection, is_active, sort),
            CONSTRAINT fk_collection_items_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL,
            CONSTRAINT fk_collection_items_image FOREIGN KEY (image_id) REFERENCES media (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE collection_item_translations (
            item_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            title VARCHAR(190) NOT NULL,
            slug VARCHAR(150) NOT NULL,
            content JSON NULL,
            PRIMARY KEY (item_id, lang),
            INDEX idx_collection_item_slug (lang, slug),
            CONSTRAINT fk_collection_item_translations_item FOREIGN KEY (item_id) REFERENCES collection_items (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS collection_item_translations');
        $db->statement('DROP TABLE IF EXISTS collection_items');
        $db->statement('DROP TABLE IF EXISTS category_translations');
        $db->statement('DROP TABLE IF EXISTS categories');
    }
};
