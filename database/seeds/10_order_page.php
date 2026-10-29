<?php
declare(strict_types=1);

use App\Models\Category;
use App\Models\Design;
use App\Models\Page;
use App\Models\PageBlock;
use Core\Database\DB;
use Core\Database\Seeder;
use Core\Settings\Settings;

// საიტის შეკვეთის გვერდი (ka: shekveta, en: order) order_form ბლოკით + ჰედერის ღილაკი („შეკვეთა“);
// პაკეტები (დიზაინების ზედა დონის კატეგორიები) დარგებით; ინდივიდუალური შეკვეთის გვერდი (custom_order_form).
// გვერდი უკვე არსებობს → გვერდს არ ეხება. packages() და customPage() — მიგრაციიდანაც (2026_10_29_000009).
return new class extends Seeder {
    private const SLUGS = ['shekveta', 'order'];
    private const CUSTOM_SLUGS = ['individualuri', 'custom'];

    /**
     * პაკეტი: სახელი და slug [ka, en]; merge — არსებული დარგი (en slug), რომელიც პაკეტად იქცევა (მისი დიზაინები რჩება);
     * industries — დარგები [ka, en, ka slug, en slug], რიგი — ინგლისური სახელით ანბანურად.
     * რიგი ემთხვევა 05_demo_pages::PACKAGES-ს (ფასები, „რა შედის“).
     */
    public const PACKAGES = [
        'start' => [
            'name' => ['სტარტი', 'Start'], 'slug' => ['starti', 'start'], 'merge' => null,
            'industries' => [
                ['სოფლის მეურნეობა', 'Agriculture & Farming', 'soplis-meurneoba', 'agriculture-farming'],
                ['სილამაზე და სალონები', 'Beauty & Hair', 'silamaze-da-salonebi', 'beauty-hair'],
                ['ტექნიკური მომსახურება', 'Maintenance & Service', 'teqnikuri-momsakhureba', 'maintenance-service'],
                ['არაკომერციული', 'Nonprofit', 'arakomerciuli', 'nonprofit'],
                ['პერსონალური და პორტფოლიო', 'Personal', 'personaluri-da-portpolio', 'personal'],
                ['ქორწილი', 'Wedding', 'qortsili', 'wedding'],
            ],
        ],
        'business' => [
            'name' => ['ბიზნესი', 'Business'], 'slug' => ['biznesi', 'business'], 'merge' => 'business',
            'industries' => [
                ['სააგენტო', 'Agency', 'saagento', 'agency'],
                ['კორპორატიული', 'Corporate', 'korporatiuli', 'corporate'],
                ['განათლება', 'Education', 'ganatleba', 'education'],
                ['მრეწველობა', 'Industrial', 'mretsveloba', 'industrial'],
                ['მედიცინა და კლინიკები', 'Medical & Hospital', 'medicina-da-klinikebi', 'medical-hospital'],
                ['სიახლეები და ჟურნალი', 'News & Magazine', 'siakhleebi-da-zhurnali', 'news-magazine'],
                ['უძრავი ქონება', 'Real Estate', 'udzravi-qoneba', 'real-estate'],
                ['რესტორნები და კვება', 'Restaurants & Food', 'restornebi-da-kveba', 'restaurants-food'],
                ['ტურიზმი და სასტუმროები', 'Travel & Tourism', 'turizmi-da-sastumroebi', 'travel-tourism'],
            ],
        ],
        'shop' => [
            'name' => ['ონლაინ მაღაზია', 'Online Store'], 'slug' => ['onlain-maghazia', 'online-store'], 'merge' => 'ecommerce',
            'industries' => [],
        ],
    ];

    /** ფასების ბლოკის ინდივიდუალური ბარათი და ღილაკი: ველი => [ka, en] */
    public const PRICING = [
        'button_label'        => ['შეუკვეთეთ', 'Order Now'],
        'custom_name'         => ['ინდივიდუალური', 'Custom'],
        'custom_price'        => ['ფასი შეთანხმებით', 'Price on request'],
        'custom_note'         => ['ვადა — ბრიფის შემდეგ', 'Timeline — after the brief'],
        'custom_features'     => ["უნიკალური დიზაინი თქვენი იდეით\nნებისმიერი რაოდენობის გვერდი\nინტეგრაციები და სპეციალური ფუნქციები\nპროექტის ეტაპობრივი დაგეგმვა\n1 თვე უფასო მხარდაჭერა",
                                  "Unique design built around your idea\nAny number of pages\nIntegrations and special features\nProject planned in stages\n1 month of free support"],
        'custom_button_label' => ['მოგვიყევით იდეაზე', 'Tell Us Your Idea'],
    ];

    public function run(DB $db): void
    {
        $this->packages($db, true);

        if ($db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = ?", [self::SLUGS[1]]) === null) {
            $this->orderPage($db);
        }
        $this->customPage($db);
    }

    private function orderPage(DB $db): void
    {
        $pageId = app(Page::class)->save(null, ['parent_id' => null, 'status' => 'published', 'is_home' => false, 'sort' => 80, 'og_image_id' => null], [
            'ka' => ['title' => 'შეკვეთა', 'slug' => self::SLUGS[0], 'meta_title' => '',
                     'meta_description' => 'შეუკვეთეთ საიტი: აირჩიეთ პაკეტი, დაათვალიერეთ დიზაინები თქვენი დარგისთვის და მოგვიყევით თქვენ შესახებ.'],
            'en' => ['title' => 'Order', 'slug' => self::SLUGS[1], 'meta_title' => '',
                     'meta_description' => 'Order a website: pick a package, browse designs for your industry and tell us about your business.'],
        ]);

        $blocks = app(PageBlock::class);
        $blocks->save(null, $pageId, 'page_header', ['image' => null, 'breadcrumb' => true], ['ka' => ['title' => ''], 'en' => ['title' => '']], []);
        $blocks->save(null, $pageId, 'order_form', ['background' => 'dark'], [
            'ka' => ['subtitle' => 'ახალი საიტი', 'title' => 'შეუკვეთეთ საიტი', 'button_label' => '',
                     'text' => '<p>აირჩიეთ პაკეტი და დარგი, დაათვალიერეთ დიზაინები და აირჩიეთ თქვენთვის სასურველი — დანარჩენს ჩვენ მოვაგვარებთ.</p>'],
            'en' => ['subtitle' => 'New Website', 'title' => 'Order Your Website', 'button_label' => '',
                     'text' => '<p>Pick a package and your industry, browse the designs and choose the one you like — we will take care of the rest.</p>'],
        ], []);

        // მენიუს პუნქტის ნაცვლად — ჰედერის ღილაკი (08_site_settings-ში კონტაქტზეა) → შეკვეთა
        app(Settings::class)->save([
            'general.cta_page'  => (string)$pageId,
            'general.cta_label' => ['ka' => 'შეკვეთა', 'en' => 'Order'],
        ]);

        $this->heroButton($db, $blocks, $pageId);
    }

    /** ინდივიდუალური შეკვეთის გვერდი (custom_order_form ბლოკი); უკვე არსებობს (ბლოკით ან slug-ით) → არაფერი */
    public function customPage(DB $db): void
    {
        $blocks = app(PageBlock::class);
        if ($blocks->pageIdsOfType('custom_order_form') !== []
            || $db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = ?", [self::CUSTOM_SLUGS[1]]) !== null) {
            return;
        }
        $pageId = app(Page::class)->save(null, ['parent_id' => null, 'status' => 'published', 'is_home' => false, 'sort' => 81, 'og_image_id' => null], [
            'ka' => ['title' => 'ინდივიდუალური შეკვეთა', 'slug' => self::CUSTOM_SLUGS[0], 'meta_title' => '',
                     'meta_description' => 'ინდივიდუალური საიტი თქვენი იდეით: მოგვიყევით პროექტზე და შეთავაზებას 1 სამუშაო დღეში მიიღებთ.'],
            'en' => ['title' => 'Custom Order', 'slug' => self::CUSTOM_SLUGS[1], 'meta_title' => '',
                     'meta_description' => 'A custom website built around your idea: tell us about the project and get a proposal within 1 business day.'],
        ]);
        $blocks->save(null, $pageId, 'page_header', ['image' => null, 'breadcrumb' => true], ['ka' => ['title' => ''], 'en' => ['title' => '']], []);
        $blocks->save(null, $pageId, 'custom_order_form', ['background' => 'dark'], [
            'ka' => ['subtitle' => 'ინდივიდუალური პროექტი', 'title' => 'მოგვიყევით თქვენს იდეაზე', 'button_label' => '',
                     'text' => '<p>ვერ იპოვეთ შესაფერისი დიზაინი ან განსაკუთრებული ფუნქციები გჭირდებათ? აღწერეთ პროექტი — შევქმნით უნიკალურ საიტს და ზუსტ ფასს დაგისახელებთ.</p>'],
            'en' => ['subtitle' => 'Custom Project', 'title' => 'Tell Us About Your Idea', 'button_label' => '',
                     'text' => '<p>Couldn’t find the right design or need special features? Describe the project — we will build a unique website and give you an exact quote.</p>'],
        ], []);
    }

    /** მთავარის ჰეროს პირველი სლაიდი: მთავარი ღილაკი („შეუკვეთეთ საიტი“, 05_demo_pages) → შეკვეთის გვერდი */
    private function heroButton(DB $db, PageBlock $blocks, int $pageId): void
    {
        $id = $db->value("SELECT b.id FROM page_blocks b JOIN pages p ON p.id = b.page_id WHERE p.is_home = 1 AND b.type = 'hero_carousel' ORDER BY b.sort LIMIT 1");
        $block = $id !== null ? $blocks->find((int)$id) : null;
        if ($block === null || !isset($block['settings']['slides'][0])) {
            return;
        }
        $block['settings']['slides'][0]['button_link'] = ['type' => 'page', 'page_id' => $pageId, 'url' => '', 'new_tab' => false];
        $blocks->save((int)$id, (int)$block['page_id'], 'hero_carousel', $block['settings'], $block['content'], json_decode((string)$block['media_ids'], true) ?: []);
    }

    /**
     * პაკეტები და დარგები (განმეორებით გაშვებადი). $create = false (მიგრაცია, არსებული საიტი) — მხოლოდ არსებული დარგები
     * გადადის პაკეტებში, წაშლილი თავიდან არ იქმნება. „სხვა“ (other) იშლება — პაკეტში ჩიფი „ყველა“ მის ნაცვლადაა.
     * ფასების ბლოკის ძველი repeater-ის ტექსტები პაკეტებში გადადის (upgradePricing).
     */
    public function packages(DB $db, bool $create): void
    {
        $categories = app(Category::class);
        $find = fn (string $slug) => (int)$db->value(
            "SELECT c.id FROM categories c JOIN category_translations t ON t.category_id = c.id AND t.lang = 'en' WHERE c.collection = ? AND t.slug = ?",
            [Design::CATEGORIES, $slug],
        );
        $sort = 0;
        foreach (self::PACKAGES as $code => $package) {
            $sort++;
            $id = (int)$db->value('SELECT id FROM categories WHERE collection = ? AND code = ?', [Design::CATEGORIES, $code]);
            if ($id === 0) {
                $id = $package['merge'] !== null ? $find($package['merge']) : 0;
                $id = $categories->save($id ?: null, Design::CATEGORIES, ['parent_id' => null, 'sort' => $sort, 'is_active' => true], [
                    'ka' => ['name' => $package['name'][0], 'slug' => $package['slug'][0]],
                    'en' => ['name' => $package['name'][1], 'slug' => $package['slug'][1]],
                ]);
                $db->update('categories', ['code' => $code], 'id = ?', [$id]);
            }
            foreach ($package['industries'] as $position => [$ka, $en, $kaSlug, $enSlug]) {
                $child = $find($enSlug);
                if ($child > 0) {
                    $db->update('categories', ['parent_id' => $id], 'id = ? AND id <> ?', [$child, $id]);
                } elseif ($create) {
                    $categories->save(null, Design::CATEGORIES, ['parent_id' => $id, 'sort' => $position + 1, 'is_active' => true], [
                        'ka' => ['name' => $ka, 'slug' => $kaSlug],
                        'en' => ['name' => $en, 'slug' => $enSlug],
                    ]);
                }
            }
        }
        if ($other = $find('other')) {
            $categories->delete($other);
        }
        $this->upgradePricing($db);
    }

    /**
     * ფასების ბლოკი: ძველი repeater (packages) → ტექსტები პაკეტებში (თუ პაკეტს ჯერ არ აქვს), ღილაკის წარწერა და
     * ინდივიდუალური ბარათი — ბლოკის ველებში. ბლოკი არ არის → პაკეტები 05_demo_pages::PACKAGES-დან.
     */
    private function upgradePricing(DB $db): void
    {
        $codes = array_keys(self::PACKAGES);
        $texts = [];
        foreach ((require base_path('database/seeds/05_demo_pages.php'))::PACKAGES as $i => $row) {
            foreach (['ka' => 0, 'en' => 1] as $lang => $j) {
                $texts[$codes[$i]][$lang] = ['price' => $row['price'][$j], 'note' => $row['note'][$j], 'features' => $row['features'][$j], 'badge' => $row['badge'][$j]];
            }
        }

        $blocks = app(PageBlock::class);
        $legacy = null;
        foreach ($blocks->pageIdsOfType('pricing') as $pageId) {
            foreach ($blocks->forPage($pageId) as $block) {
                if ($block['type'] !== 'pricing' || !isset($block['settings']['packages'])) {
                    continue;
                }
                $legacy ??= $block;
                $first = (string)($block['settings']['packages'][0]['_id'] ?? '');
                $settings = ['custom' => true] + array_diff_key($block['settings'], ['packages' => true]);
                $content = [];
                foreach ($block['content'] as $lang => $value) {
                    $label = (string)($value['packages'][$first]['button_label'] ?? '');
                    $content[$lang] = array_diff_key($value, ['packages' => true]) + ['button_label' => $label];
                    foreach (self::PRICING as $field => [$ka, $en]) {
                        $content[$lang][$field] ??= $lang === 'ka' ? $ka : ($lang === 'en' ? $en : '');
                    }
                }
                $blocks->save((int)$block['id'], $pageId, 'pricing', $settings, $content, json_decode((string)$block['media_ids'], true) ?: []);
            }
        }
        if ($legacy !== null) {
            foreach ($legacy['settings']['packages'] as $i => $item) {
                if (!isset($codes[$i])) {
                    break;
                }
                foreach ($legacy['content'] as $lang => $value) {
                    $row = (array)($value['packages'][(string)($item['_id'] ?? '')] ?? []);
                    $texts[$codes[$i]][$lang] = array_map(fn ($field) => (string)($row[$field] ?? ''), ['price' => 'price', 'note' => 'note', 'features' => 'features', 'badge' => 'badge']);
                }
            }
        }

        foreach ($texts as $code => $langs) {
            $id = (int)$db->value('SELECT id FROM categories WHERE collection = ? AND code = ?', [Design::CATEGORIES, $code]);
            foreach ($langs as $lang => $row) {
                if ($id > 0) {
                    $db->query('INSERT IGNORE INTO package_translations (category_id, lang, price, note, features, badge) VALUES (?, ?, ?, ?, ?, ?)',
                        [$id, $lang, $row['price'], $row['note'], $row['features'], $row['badge']]);
                }
            }
        }
    }
};
