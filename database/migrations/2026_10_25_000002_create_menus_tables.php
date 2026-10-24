<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // location — config/menus.php-ის გასაღები (header, footer ...)
        $db->statement('CREATE TABLE menus (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            location VARCHAR(50) NOT NULL UNIQUE,
            ' . self::TIMESTAMPS . '
        ) ' . self::TABLE_OPTIONS);

        // გვერდის წაშლისას მისი პუნქტები იშლება; მშობლის წაშლისას — შვილებიც
        $db->statement('CREATE TABLE menu_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            menu_id INT UNSIGNED NOT NULL,
            parent_id INT UNSIGNED NULL,
            type ENUM(\'page\', \'url\') NOT NULL DEFAULT \'page\',
            page_id INT UNSIGNED NULL,
            url VARCHAR(2000) NOT NULL DEFAULT \'\',
            new_tab TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort SMALLINT NOT NULL DEFAULT 0,
            ' . self::TIMESTAMPS . ',
            INDEX idx_menu_items_menu (menu_id, parent_id, sort),
            CONSTRAINT fk_menu_items_menu FOREIGN KEY (menu_id) REFERENCES menus (id) ON DELETE CASCADE,
            CONSTRAINT fk_menu_items_parent FOREIGN KEY (parent_id) REFERENCES menu_items (id) ON DELETE CASCADE,
            CONSTRAINT fk_menu_items_page FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        // label ცარიელია → გვერდის სათაური ამ ენაზე; url — ენის მიხედვით განსხვავებული ბმულისთვის (არასავალდებულო)
        $db->statement('CREATE TABLE menu_item_translations (
            item_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            label VARCHAR(100) NOT NULL DEFAULT \'\',
            url VARCHAR(2000) NOT NULL DEFAULT \'\',
            PRIMARY KEY (item_id, lang),
            CONSTRAINT fk_menu_item_translations_item FOREIGN KEY (item_id) REFERENCES menu_items (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);
    }

    public function down(DB $db): void
    {
        $db->statement('DROP TABLE IF EXISTS menu_item_translations');
        $db->statement('DROP TABLE IF EXISTS menu_items');
        $db->statement('DROP TABLE IF EXISTS menus');
    }
};
