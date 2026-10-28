<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Migration;

return new class extends Migration {
    public function up(DB $db): void
    {
        // ახალ ბაზაზე AI გუნდს seed-ები ქმნის (07_demo_collections) — აქ მხოლოდ უკვე შევსებული საიტი
        if ((int)$db->value('SELECT COUNT(*) FROM pages') === 0
            || $db->value("SELECT item_id FROM collection_item_translations WHERE lang = 'en' AND slug = 'product-owner'") !== null) {
            return;
        }

        // 4 დემო წევრი (07_demo_collections-ის ძველი ვერსია) და მათი „სტუდიის“ კატეგორია
        $demo = $db->column(
            "SELECT i.id FROM collection_items i JOIN collection_item_translations t ON t.item_id = i.id AND t.lang = 'en'
             WHERE i.collection = 'team' AND t.slug IN ('member-1', 'member-2', 'member-3', 'member-4')",
        );
        foreach ($demo as $id) {
            $db->delete('collection_items', 'id = ?', [(int)$id]);
        }
        $studio = $db->value(
            "SELECT c.id FROM categories c JOIN category_translations t ON t.category_id = c.id AND t.lang = 'en'
             WHERE c.collection = 'team' AND t.slug = 'studio'",
        );
        if ($studio !== null && (int)$db->value('SELECT COUNT(*) FROM collection_items WHERE category_id = ?', [(int)$studio]) === 0) {
            $db->delete('categories', 'id = ?', [(int)$studio]);
        }

        $departments = (require base_path('database/seeds/07_demo_collections.php'))->aiTeam();
        $pages = require base_path('database/seeds/05_demo_pages.php');

        // გუნდის გვერდი: ძველი „ჩვენი გუნდი“ ბლოკი → განყოფილებები + სამუშაო პროცესი
        $team = $db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = 'team'");
        if ($team !== null) {
            $db->delete('page_blocks', "page_id = ? AND type = 'team_grid'", [(int)$team]);
            $pages->teamPage($db, $departments);
        }

        // „ჩვენ შესახებ“: „ვინ ვართ“ → AI გუნდის მოკლე ვერსია იმავე ადგილას
        $about = $db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = 'about'");
        $old = $about === null ? null : $db->value("SELECT id FROM page_blocks WHERE page_id = ? AND type = 'team_grid' ORDER BY sort LIMIT 1", [(int)$about]);
        if ($old !== null && $team !== null) {
            $sort = (int)$db->value('SELECT sort FROM page_blocks WHERE id = ?', [(int)$old]);
            $db->delete('page_blocks', 'id = ?', [(int)$old]);
            $pages->aboutTeam((int)$about, (int)$team);
            $new = (int)$db->value("SELECT MAX(id) FROM page_blocks WHERE page_id = ? AND type = 'team_grid'", [(int)$about]);
            $db->update('page_blocks', ['sort' => $sort], 'id = ?', [$new]);
        }
    }

    public function down(DB $db): void
    {
        // მონაცემის ცვლილებაა — დემო გუნდი აღარ ბრუნდება (content:reset ახალ ვერსიას ქმნის)
    }
};
