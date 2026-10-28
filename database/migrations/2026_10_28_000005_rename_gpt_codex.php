<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // OpenAI-ის პროდუქტს „Codex“ ჰქვია — გუნდის გვერდის meta აღწერაში „GPT Codex“ → „Codex“ (ადმინში ხელით შეცვლილს არ ეხება)
        $db->query(
            "UPDATE page_translations SET meta_description = REPLACE(meta_description, 'GPT Codex', 'Codex')
             WHERE page_id IN (SELECT page_id FROM (SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = 'team') t)
               AND meta_description LIKE '%GPT Codex%'",
        );
    }

    public function down(DB $db): void
    {
        // მონაცემის ცვლილებაა — აღწერა ადმინიდან იცვლება
    }
};
