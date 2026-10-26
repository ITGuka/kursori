<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // „დამიმახსოვრე“: cookie = selector:validator, ბაზაში მხოლოდ validator-ის hash და პაროლის ანაბეჭდი
        $db->statement('CREATE TABLE admin_remember_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            selector CHAR(24) NOT NULL,
            validator_hash CHAR(64) NOT NULL,
            fingerprint CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_admin_remember_selector (selector),
            INDEX idx_admin_remember_user (user_id),
            CONSTRAINT fk_admin_remember_user FOREIGN KEY (user_id) REFERENCES admin_users (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS admin_remember_tokens');
    }
};
