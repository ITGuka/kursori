<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('CREATE TABLE activity_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            action VARCHAR(50) NOT NULL,
            entity VARCHAR(50) NULL,
            entity_id INT UNSIGNED NULL,
            data JSON NULL,
            ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_entity (entity, entity_id),
            INDEX idx_activity_created (created_at),
            CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES admin_users (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS activity_log');
    }
};
