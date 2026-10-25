<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // 1 — ორიგინალსა და ყველა ვარიანტს აქვს .webp ასლი (ImageResizer::createWebp)
        $db->statement('ALTER TABLE media ADD COLUMN webp TINYINT(1) NOT NULL DEFAULT 0 AFTER variants');
    }

    public function down(DB $db): void
    {
        $db->statement('ALTER TABLE media DROP COLUMN webp');
    }
};
