<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// სიახლეების იმპორტი (dev.to): feed = ტეგების ნაკრები → ბლოგის კატეგორია; news_items — წამოღებული სტატიები
// (ერთი სტატია ერთხელ — unique source + external_id), გამოქვეყნების შემდეგ — ბმული ბლოგის ჩანაწერზე.
return new class extends Migration {
    public function up(DB $db): void
    {
        // publish_mode: auto — პირდაპირ ბლოგში, review — მოდერაციის რიგში; tags / exclude_tags — მძიმით გამოყოფილი
        $db->statement("CREATE TABLE news_feeds (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            source VARCHAR(30) NOT NULL DEFAULT 'devto',
            tags VARCHAR(500) NOT NULL,
            exclude_tags VARCHAR(500) NOT NULL DEFAULT '',
            category_id INT UNSIGNED NULL,
            publish_mode ENUM('auto', 'review') NOT NULL DEFAULT 'review',
            per_run TINYINT UNSIGNED NOT NULL DEFAULT 3,
            top_days TINYINT UNSIGNED NOT NULL DEFAULT 7,
            min_reactions SMALLINT UNSIGNED NOT NULL DEFAULT 10,
            require_image TINYINT(1) NOT NULL DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort SMALLINT NOT NULL DEFAULT 0,
            last_fetched_at DATETIME NULL,
            last_error VARCHAR(500) NULL,
            " . self::TIMESTAMPS . ",
            INDEX idx_news_feeds (is_active, sort),
            CONSTRAINT fk_news_feeds_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
        ) " . self::TABLE_OPTIONS);

        // canonical_url — ორიგინალი (ავტორის ბლოგი, თუ dev.to-ზე გადმოტანილია), url — dev.to-ს გვერდი
        $db->statement("CREATE TABLE news_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            feed_id INT UNSIGNED NULL,
            source VARCHAR(30) NOT NULL,
            external_id VARCHAR(64) NOT NULL,
            url VARCHAR(500) NOT NULL,
            canonical_url VARCHAR(500) NOT NULL,
            title VARCHAR(300) NOT NULL,
            description TEXT NULL,
            body_html MEDIUMTEXT NULL,
            image_url VARCHAR(1000) NULL,
            tags JSON NULL,
            author_name VARCHAR(150) NULL,
            author_url VARCHAR(500) NULL,
            reactions INT UNSIGNED NOT NULL DEFAULT 0,
            reading_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            source_published_at DATETIME NULL,
            status ENUM('new', 'approved', 'published', 'rejected', 'failed') NOT NULL DEFAULT 'new',
            collection_item_id INT UNSIGNED NULL,
            published_at DATETIME NULL,
            error VARCHAR(1000) NULL,
            " . self::TIMESTAMPS . ",
            UNIQUE KEY uq_news_items_source (source, external_id),
            INDEX idx_news_items_status (status, created_at),
            INDEX idx_news_items_published (published_at),
            CONSTRAINT fk_news_items_feed FOREIGN KEY (feed_id) REFERENCES news_feeds (id) ON DELETE SET NULL,
            CONSTRAINT fk_news_items_collection_item FOREIGN KEY (collection_item_id) REFERENCES collection_items (id) ON DELETE SET NULL
        ) " . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS news_items');
        $db->statement('DROP TABLE IF EXISTS news_feeds');
    }
};
