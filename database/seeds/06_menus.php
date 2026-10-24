<?php
declare(strict_types=1);

use App\Models\Menu;
use Core\Database\DB;
use Core\Database\Seeder;

// საწყისი მენიუები არსებული გვერდებიდან: header — გვერდების ხე (ზედა დონე + შვილები), footer — ზედა დონე მთავრის გარეშე.
// მენიუში უკვე არის პუნქტი → არ ეხება.
return new class extends Seeder {
    public function run(DB $db): void
    {
        $menus = app(Menu::class);
        $pages = $db->select('SELECT id, parent_id, is_home FROM pages ORDER BY sort, id');
        $topLevel = array_filter($pages, fn ($page) => $page['parent_id'] === null);
        $item = fn (int $pageId, ?int $parent) => ['type' => 'page', 'page_id' => $pageId, 'url' => '', 'parent_id' => $parent, 'new_tab' => false, 'is_active' => true];

        if ($menus->isEmpty('header')) {
            $menuId = $menus->idFor('header');
            foreach ($topLevel as $page) {
                $parentItem = $menus->saveItem(null, $menuId, $item((int)$page['id'], null), []);
                foreach ($pages as $child) {
                    if ((int)$child['parent_id'] === (int)$page['id']) {
                        $menus->saveItem(null, $menuId, $item((int)$child['id'], $parentItem), []);
                    }
                }
            }
        }

        if ($menus->isEmpty('footer')) {
            $menuId = $menus->idFor('footer');
            foreach ($topLevel as $page) {
                if (!$page['is_home']) {
                    $menus->saveItem(null, $menuId, $item((int)$page['id'], null), []);
                }
            }
        }
    }
};
