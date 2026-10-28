<?php
declare(strict_types=1);

use App\Models\Category;
use App\Models\Design;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBlock;
use Core\Database\DB;
use Core\Database\Seeder;

// საიტის შეკვეთის გვერდი (ka: shekveta, en: order) order_form ბლოკით + header მენიუს პუნქტი;
// საწყისი ბიზნესის კატეგორიები (თუ ჯერ არ არის). გვერდი უკვე არსებობს → გვერდს არ ეხება.
return new class extends Seeder {
    private const SLUGS = ['shekveta', 'order'];

    // რიგი — ინგლისური სახელით ანბანურად
    private const CATEGORIES = [
        ['სააგენტო', 'Agency', 'saagento', 'agency'],
        ['სოფლის მეურნეობა', 'Agriculture & Farming', 'soplis-meurneoba', 'agriculture-farming'],
        ['სილამაზე და სალონები', 'Beauty & Hair', 'silamaze-da-salonebi', 'beauty-hair'],
        ['ბიზნესი', 'Business', 'biznesi', 'business'],
        ['კორპორატიული', 'Corporate', 'korporatiuli', 'corporate'],
        ['ონლაინ მაღაზია', 'eCommerce', 'onlain-maghazia', 'ecommerce'],
        ['განათლება', 'Education', 'ganatleba', 'education'],
        ['მრეწველობა', 'Industrial', 'mretsveloba', 'industrial'],
        ['ტექნიკური მომსახურება', 'Maintenance & Service', 'teqnikuri-momsakhureba', 'maintenance-service'],
        ['მედიცინა და კლინიკები', 'Medical & Hospital', 'medicina-da-klinikebi', 'medical-hospital'],
        ['სიახლეები და ჟურნალი', 'News & Magazine', 'siakhleebi-da-zhurnali', 'news-magazine'],
        ['არაკომერციული', 'Nonprofit', 'arakomerciuli', 'nonprofit'],
        ['პერსონალური და პორტფოლიო', 'Personal', 'personaluri-da-portpolio', 'personal'],
        ['უძრავი ქონება', 'Real Estate', 'udzravi-qoneba', 'real-estate'],
        ['რესტორნები და კვება', 'Restaurants & Food', 'restornebi-da-kveba', 'restaurants-food'],
        ['ტურიზმი და სასტუმროები', 'Travel & Tourism', 'turizmi-da-sastumroebi', 'travel-tourism'],
        ['ქორწილი', 'Wedding', 'qortsili', 'wedding'],
    ];

    public function run(DB $db): void
    {
        $this->categories($db);

        if ($db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = ?", [self::SLUGS[1]]) !== null) {
            return;
        }
        $pageId = app(Page::class)->save(null, ['parent_id' => null, 'status' => 'published', 'is_home' => false, 'sort' => 80, 'og_image_id' => null], [
            'ka' => ['title' => 'შეკვეთა', 'slug' => self::SLUGS[0], 'meta_title' => '',
                     'meta_description' => 'შეუკვეთეთ საიტი: აირჩიეთ დიზაინი თქვენი ბიზნესის კატეგორიის მიხედვით და მოგვიყევით თქვენ შესახებ.'],
            'en' => ['title' => 'Order', 'slug' => self::SLUGS[1], 'meta_title' => '',
                     'meta_description' => 'Order a website: choose a design for your type of business and tell us about it.'],
        ]);

        $blocks = app(PageBlock::class);
        $blocks->save(null, $pageId, 'page_header', ['image' => null, 'breadcrumb' => true], ['ka' => ['title' => ''], 'en' => ['title' => '']], []);
        $blocks->save(null, $pageId, 'order_form', ['background' => 'dark'], [
            'ka' => ['subtitle' => 'ახალი საიტი', 'title' => 'შეუკვეთეთ საიტი', 'button_label' => '',
                     'text' => '<p>აირჩიეთ ბიზნესის კატეგორია, დაათვალიერეთ დიზაინები და აირჩიეთ თქვენთვის სასურველი — დანარჩენს ჩვენ მოვაგვარებთ.</p>'],
            'en' => ['subtitle' => 'New Website', 'title' => 'Order Your Website', 'button_label' => '',
                     'text' => '<p>Pick your business category, browse the designs and choose the one you like — we will take care of the rest.</p>'],
        ], []);

        $menus = app(Menu::class);
        $menus->saveItem(null, $menus->idFor('header'), ['type' => 'page', 'page_id' => $pageId, 'url' => '', 'parent_id' => null, 'new_tab' => false, 'is_active' => true], []);

        $this->heroButton($db, $blocks, $pageId);
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

    private function categories(DB $db): void
    {
        $categories = app(Category::class);
        if ((int)$db->value('SELECT COUNT(*) FROM categories WHERE collection = ?', [Design::CATEGORIES]) === 0) {
            foreach (self::CATEGORIES as $sort => [$ka, $en, $kaSlug, $enSlug]) {
                $categories->save(null, Design::CATEGORIES, ['parent_id' => null, 'sort' => $sort + 1, 'is_active' => true], [
                    'ka' => ['name' => $ka, 'slug' => $kaSlug],
                    'en' => ['name' => $en, 'slug' => $enSlug],
                ]);
            }
        }
        // „სხვა“ — სიის ბოლოს; ყოველთვის ჩანს და ყველა დიზაინს აჩვენებს (Design::OTHER_SLUG)
        if (!$categories->slugExists(Design::CATEGORIES, 'en', Design::OTHER_SLUG)) {
            $categories->save(null, Design::CATEGORIES, ['parent_id' => null, 'sort' => 999, 'is_active' => true], [
                'ka' => ['name' => 'სხვა', 'slug' => 'skhva'],
                'en' => ['name' => 'Other', 'slug' => Design::OTHER_SLUG],
            ]);
        }
    }
};
