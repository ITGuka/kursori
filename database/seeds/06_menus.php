<?php
declare(strict_types=1);

use App\Models\Menu;
use Core\Database\DB;
use Core\Database\Seeder;

// საწყისი მენიუები. header — კლიენტის გადაწყვეტილების რიგით: სერვისები ▾ (თითო სერვისი) · პორტფოლიო · ფასები · ბლოგი · ჩვენ შესახებ · კონტაქტი;
// სტუდიის გვერდები არ არის → გვერდების ხე. footer — ზედა დონე მთავრის გარეშე. მენიუში უკვე არის პუნქტი → არ ეხება.
return new class extends Seeder {
    /** header-ის რიგი — გვერდების ინგლისური slug-ები */
    public const HEADER = ['services', 'portfolio', 'pricing', 'blog', 'about', 'contact'];

    public function run(DB $db): void
    {
        $menus = app(Menu::class);
        $pages = $db->select('SELECT id, parent_id, is_home FROM pages ORDER BY sort, id');
        $topLevel = array_filter($pages, fn ($page) => $page['parent_id'] === null);
        $item = fn (int $pageId, ?int $parent) => ['type' => 'page', 'page_id' => $pageId, 'url' => '', 'parent_id' => $parent, 'new_tab' => false, 'is_active' => true];

        if ($menus->isEmpty('header')) {
            $menuId = $menus->idFor('header');
            if (self::pageId($db, 'services') !== null) {
                self::studioHeader($db, $menus, $menuId);
            } else {
                foreach ($topLevel as $page) {
                    $parentItem = $menus->saveItem(null, $menuId, $item((int)$page['id'], null), []);
                    foreach ($pages as $child) {
                        if ((int)$child['parent_id'] === (int)$page['id']) {
                            $menus->saveItem(null, $menuId, $item((int)$child['id'], $parentItem), []);
                        }
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

    /** სტუდიის header: გვერდები HEADER-ის რიგით + სერვისების ქვემენიუ (თუ სერვისები უკვე არის) */
    public static function studioHeader(DB $db, Menu $menus, int $menuId): void
    {
        foreach (self::HEADER as $slug) {
            if (($pageId = self::pageId($db, $slug)) !== null) {
                $menus->saveItem(null, $menuId, ['type' => 'page', 'page_id' => $pageId, 'url' => '', 'parent_id' => null, 'new_tab' => false, 'is_active' => true], []);
            }
        }
        self::serviceChildren($db, $menus);
    }

    /**
     * „სერვისების“ პუნქტის ქვეშ — თითო სერვისის გვერდი (კოლექცია). ქვემენიუ უკვე აქვს → არ ეხება.
     * 07_demo_collections-იც იძახებს: სერვისები მენიუს შემდეგ იქმნება.
     */
    public static function serviceChildren(DB $db, Menu $menus): void
    {
        $servicesPage = self::pageId($db, 'services');
        $menuId = $menus->idFor('header');
        $parent = $servicesPage === null ? null
            : $db->value('SELECT id FROM menu_items WHERE menu_id = ? AND page_id = ? AND parent_id IS NULL', [$menuId, $servicesPage]);
        if ($parent === null || (int)$db->value('SELECT COUNT(*) FROM menu_items WHERE parent_id = ?', [$parent]) > 0) {
            return;
        }
        $route = (array)(require dirname(__DIR__, 2) . '/config/collections.php')['services']['route'];
        foreach ($db->select("SELECT id FROM collection_items WHERE collection = 'services' AND is_active = 1 ORDER BY sort, id") as $service) {
            $translations = [];
            foreach ($db->select('SELECT lang, title, slug FROM collection_item_translations WHERE item_id = ?', [$service['id']]) as $t) {
                $translations[$t['lang']] = ['label' => $t['title'], 'url' => '/' . $t['lang'] . '/' . ($route[$t['lang']] ?? 'services') . '/' . $t['slug']];
            }
            if (($first = reset($translations)) !== false) {
                $menus->saveItem(null, $menuId, ['type' => 'url', 'page_id' => null, 'url' => $first['url'], 'parent_id' => (int)$parent, 'new_tab' => false, 'is_active' => true], $translations);
            }
        }
    }

    private static function pageId(DB $db, string $slug): ?int
    {
        $id = $db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = ?", [$slug]);
        return $id === null ? null : (int)$id;
    }
};
