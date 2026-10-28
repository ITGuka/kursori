<?php
declare(strict_types=1);

use App\Models\Category;
use Core\Database\DB;
use Core\Database\Seeder;

// სიახლეების 10 feed (dev.to ტეგები) და მათი ბლოგის კატეგორიები. გაეშვება მხოლოდ მაშინ, როცა feed-ები ჯერ არ არის;
// არსებული კატეგორია (იგივე en slug) ხელახლა არ იქმნება. ტეგები 2026-10-29-ს შემოწმდა API-ით (accessibility, w3c — ცარიელია).
return new class extends Seeder {
    /** ყველა feed-ში გამოსარიცხი ტეგები: სტარტაპის/კარიერის თემები და რეკლამა, რომლებიც ტექნიკურ ტეგებსაც ატარებს */
    public const EXCLUDE = 'career,startup,buildinpublic,marketing,business,saas,sponsored';

    /** feed => [ტეგები, კატეგორიის სახელი [ka, en], slug [ka, en]] */
    public const FEEDS = [
        'PHP'             => ['php,laravel,symfony,wordpress', ['PHP', 'PHP'], ['php', 'php']],
        'HTML & CSS'      => ['html,css,tailwindcss', ['HTML და CSS', 'HTML & CSS'], ['html-css', 'html-css']],
        'Frontend'        => ['frontend,javascript,typescript,react,vue', ['ფრონტენდი', 'Frontend'], ['frontendi', 'frontend']],
        'Backend'         => ['backend,node,api,database', ['ბექენდი', 'Backend'], ['bekendi', 'backend']],
        'Web Design'      => ['design,ui,ux', ['ვებ დიზაინი', 'Web Design'], ['veb-dizaini', 'web-design']],
        'AI'              => ['ai,machinelearning,llm,openai', ['AI დეველოპმენტი', 'AI Development'], ['ai-developmenti', 'ai-development']],
        'Web3'            => ['web3,blockchain,crypto', ['Web3', 'Web3'], ['web3', 'web3']],
        'Web Dev & Core'  => ['webdev,programming,computerscience', ['ვებ დეველოპმენტი', 'Web Development'], ['veb-developmenti', 'web-development']],
        'Standards'       => ['a11y,security,privacy,gdpr,standards', ['სტანდარტები და რეგულაციები', 'Standards & Regulations'], ['standartebi-regulaciebi', 'standards-regulations']],
        'Performance & SEO' => ['performance,webperf,seo,lighthouse,corewebvitals', ['სისწრაფე და SEO', 'Performance & SEO'], ['sistrafe-seo', 'performance-seo']],
    ];

    public function run(DB $db): void
    {
        if ((int)$db->value('SELECT COUNT(*) FROM news_feeds') > 0) {
            return;
        }
        $categories = app(Category::class);
        $sort = 0;
        foreach (self::FEEDS as $name => [$tags, $names, $slugs]) {
            $categoryId = $db->value(
                "SELECT c.id FROM categories c JOIN category_translations t ON t.category_id = c.id
                 WHERE c.collection = 'blog' AND t.lang = 'en' AND t.slug = ?",
                [$slugs[1]],
            );
            $categoryId ??= $categories->save(null, 'blog', ['parent_id' => null, 'sort' => 0, 'is_active' => true], [
                'ka' => ['name' => $names[0], 'slug' => $slugs[0]],
                'en' => ['name' => $names[1], 'slug' => $slugs[1]],
            ]);
            $db->insert('news_feeds', ['name' => $name, 'tags' => $tags, 'exclude_tags' => self::EXCLUDE,
                                       'category_id' => (int)$categoryId, 'sort' => ++$sort]);
        }
    }
};
