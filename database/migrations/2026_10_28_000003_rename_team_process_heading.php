<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // გუნდის გვერდის პროცესს მთავარის სათაური („იდეიდან გაშვებამდე“) ჰქონდა — ახალი სათაური (05_demo_pages::TEAM_PROCESS_HEADING);
        // ადმინში უკვე შეცვლილ სათაურს არ ეხება
        $heading = (require base_path('database/seeds/05_demo_pages.php'))::TEAM_PROCESS_HEADING;
        $old = ['ka' => 'იდეიდან გაშვებამდე', 'en' => 'From Idea to Launch'];
        $blocks = $db->column(
            "SELECT b.id FROM page_blocks b JOIN page_translations p ON p.page_id = b.page_id AND p.lang = 'en'
             WHERE p.slug = 'team' AND b.type = 'process'",
        );
        foreach ($blocks as $id) {
            foreach (['ka' => 0, 'en' => 1] as $lang => $i) {
                $db->query(
                    "UPDATE page_block_translations SET content = JSON_SET(content, '$.subtitle', ?, '$.title', ?)
                     WHERE block_id = ? AND lang = ? AND JSON_UNQUOTE(JSON_EXTRACT(content, '$.title')) = ?",
                    [$heading[0][$i], $heading[1][$i], (int)$id, $lang, $old[$lang]],
                );
            }
        }
    }

    public function down(DB $db): void
    {
        // მონაცემის ცვლილებაა — სათაური ადმინიდან იცვლება
    }
};
