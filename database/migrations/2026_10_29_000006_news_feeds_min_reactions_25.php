<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

// სიახლის მინიმალური რეაქციები: 25 (ხარისხის ფილტრი) — ნაგულისხმევი ახალ feed-ზე და არსებულებში, სადაც ნაკლებია
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('ALTER TABLE news_feeds MODIFY min_reactions SMALLINT UNSIGNED NOT NULL DEFAULT 25');
        $db->statement('UPDATE news_feeds SET min_reactions = 25 WHERE min_reactions < 25');
    }

    public function down(DB $db): void
    {
        // წინა მნიშვნელობები არ ინახება — ფილტრი ადმინიდან იცვლება
        $db->statement('ALTER TABLE news_feeds MODIFY min_reactions SMALLINT UNSIGNED NOT NULL DEFAULT 10');
    }
};
