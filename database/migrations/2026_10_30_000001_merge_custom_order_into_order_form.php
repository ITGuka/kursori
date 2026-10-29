<?php
declare(strict_types=1);

use App\Models\Page;
use App\Models\Redirect;
use Core\Database\DB;
use Core\Database\Migration;

// ინდივიდუალური შეკვეთა გადავიდა შეკვეთის ფორმაში (?package=custom): ცალკე გვერდი (custom_order_form ბლოკით, 2026_10_29_000009)
// იშლება, მისი მისამართები → შეკვეთის გვერდი ?package=custom (301).
return new class extends Migration {
    public function up(DB $db): void
    {
        $order = [];
        foreach ($db->select("SELECT t.lang, t.slug FROM page_blocks b JOIN page_translations t ON t.page_id = b.page_id WHERE b.type = 'order_form'") as $row) {
            $order[$row['lang']] ??= $row['slug'];
        }
        $pages = app(Page::class);
        $redirects = app(Redirect::class);
        foreach (array_map('intval', $db->column("SELECT DISTINCT page_id FROM page_blocks WHERE type = 'custom_order_form'")) as $pageId) {
            foreach ($db->select('SELECT lang, slug FROM page_translations WHERE page_id = ?', [$pageId]) as $row) {
                $from = Redirect::normalize('/' . $row['lang'] . '/' . $row['slug']);
                if (isset($order[$row['lang']]) && !$redirects->fromExists($from)) {
                    $redirects->save(null, ['from_path' => $from, 'to_url' => '/' . $row['lang'] . '/' . $order[$row['lang']] . '?package=custom', 'code' => 301, 'is_active' => true]);
                }
            }
            $pages->delete($pageId);
        }
    }

    public function down(DB $db): void
    {
        // გვერდი თავიდან არ იქმნება — ინდივიდუალური შეკვეთა შეკვეთის ფორმაშია
    }
};
