<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // გუნდის გვერდის პროცესი: მთავარის ბადის ნაცვლად pipeline ხედი, მუქი ფონი (გვერდის დანარჩენი ნაწილივით)
        // და ეტაპების აგენტები 05_demo_pages::TEAM_PROCESS-დან (რიგით)
        $process = (require base_path('database/seeds/05_demo_pages.php'))::TEAM_PROCESS;
        $blocks = $db->select(
            "SELECT b.id, b.settings FROM page_blocks b JOIN page_translations p ON p.page_id = b.page_id AND p.lang = 'en'
             WHERE p.slug = 'team' AND b.type = 'process'",
        );
        foreach ($blocks as $block) {
            $settings = (array)json_decode((string)$block['settings'], true);
            $settings['layout'] = 'pipeline';
            $settings['background'] = 'dark';
            foreach ((array)($settings['steps'] ?? []) as $i => $step) {
                if (isset($process[$i]) && ($step['agents'] ?? '') === '') {
                    $settings['steps'][$i]['agents'] = $process[$i][2];
                }
            }
            $db->update('page_blocks', ['settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 'id = ?', [(int)$block['id']]);
        }
    }

    public function down(DB $db): void
    {
        // მონაცემის ცვლილებაა — ხედი ადმინიდან იცვლება
    }
};
