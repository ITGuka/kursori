<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // საჯარო ფორმებიდან შემოსული (Inbox); data — ველები ფორმის მიხედვით
        $db->statement('CREATE TABLE form_submissions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            form VARCHAR(50) NOT NULL,
            lang VARCHAR(5) NOT NULL,
            data JSON NOT NULL,
            page_url VARCHAR(500) NOT NULL DEFAULT \'\',
            ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_form_submissions (form, is_read, created_at)
        ) ' . self::TABLE_OPTIONS);

        // status: pending (დასტურს ელოდება) | active | unsubscribed; token — დასტურისა და გაუქმების ბმულებისთვის
        $db->statement('CREATE TABLE subscribers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL UNIQUE,
            lang VARCHAR(5) NOT NULL,
            status ENUM(\'pending\', \'active\', \'unsubscribed\') NOT NULL DEFAULT \'pending\',
            token CHAR(64) NOT NULL UNIQUE,
            ip VARCHAR(45) NULL,
            confirmed_at DATETIME NULL,
            unsubscribed_at DATETIME NULL,
            ' . self::TIMESTAMPS . ',
            INDEX idx_subscribers_status (status, lang)
        ) ' . self::TABLE_OPTIONS);

        // lang = \'\' — ყველა აქტიურ გამომწერს
        $db->statement('CREATE TABLE newsletter_campaigns (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(190) NOT NULL,
            body MEDIUMTEXT NOT NULL,
            lang VARCHAR(5) NOT NULL DEFAULT \'\',
            status ENUM(\'draft\', \'sending\', \'sent\') NOT NULL DEFAULT \'draft\',
            created_by INT UNSIGNED NULL,
            started_at DATETIME NULL,
            sent_at DATETIME NULL,
            ' . self::TIMESTAMPS . ',
            CONSTRAINT fk_campaigns_user FOREIGN KEY (created_by) REFERENCES admin_users (id) ON DELETE SET NULL
        ) ' . self::TABLE_OPTIONS);

        $db->statement('CREATE TABLE newsletter_sends (
            campaign_id INT UNSIGNED NOT NULL,
            subscriber_id INT UNSIGNED NOT NULL,
            status ENUM(\'pending\', \'sent\', \'failed\') NOT NULL DEFAULT \'pending\',
            error VARCHAR(500) NULL,
            sent_at DATETIME NULL,
            PRIMARY KEY (campaign_id, subscriber_id),
            INDEX idx_newsletter_sends_status (campaign_id, status),
            CONSTRAINT fk_sends_campaign FOREIGN KEY (campaign_id) REFERENCES newsletter_campaigns (id) ON DELETE CASCADE,
            CONSTRAINT fk_sends_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS newsletter_sends');
        $db->statement('DROP TABLE IF EXISTS newsletter_campaigns');
        $db->statement('DROP TABLE IF EXISTS subscribers');
        $db->statement('DROP TABLE IF EXISTS form_submissions');
    }
};
