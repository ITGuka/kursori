<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // სადემონსტრაციო favicon.png (08_site_settings) KURSORI-ის ხატულას გადაფარავდა (layouts/main.php) —
        // იშლება მხოლოდ ის; ადმინში სხვა ატვირთული favicon რჩება
        $demo = $db->value("SELECT id FROM media WHERE folder = 'demo' AND original_name = 'favicon.png'");
        if ($demo !== null) {
            $db->update('settings', ['value' => ''], "setting_key = 'general.favicon' AND value = ?", [(string)$demo]);
        }
    }

    public function down(DB $db): void
    {
        // მონაცემის ცვლილებაა — წინა მნიშვნელობა ადმინიდან აღდგება
    }
};
