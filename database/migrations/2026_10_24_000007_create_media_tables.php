<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // path — public/uploads-ის მიმართ; variants — {"thumb": {"width": .., "height": ..}, ...}
        $db->statement('CREATE TABLE media (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            path VARCHAR(255) NOT NULL UNIQUE,
            original_name VARCHAR(190) NOT NULL,
            mime VARCHAR(100) NOT NULL,
            extension VARCHAR(10) NOT NULL,
            size INT UNSIGNED NOT NULL,
            width SMALLINT UNSIGNED NULL,
            height SMALLINT UNSIGNED NULL,
            variants JSON NULL,
            folder VARCHAR(50) NULL,
            uploaded_by INT UNSIGNED NULL,
            ' . self::TIMESTAMPS . ',
            INDEX idx_media_folder (folder),
            INDEX idx_media_mime (mime),
            CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by) REFERENCES admin_users (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE media_translations (
            media_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            alt VARCHAR(255) NOT NULL DEFAULT \'\',
            caption VARCHAR(500) NOT NULL DEFAULT \'\',
            PRIMARY KEY (media_id, lang),
            CONSTRAINT fk_media_translations_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS media_translations');
        $db->statement('DROP TABLE IF EXISTS media');
    }
};
