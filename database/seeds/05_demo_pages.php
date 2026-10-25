<?php
declare(strict_types=1);

use App\Models\Page;
use App\Models\PageBlock;
use Core\Database\DB;
use Core\Database\Seeder;

// ვებ სტუდიის საწყისი გვერდები ბლოკებით (ka + en). სურათები — 04_demo_media (resources/stock), ჩაანაცვლეთ რეალურით.
// სერვისები, გუნდი, შეფასებები, პორტფოლიო — კოლექციებიდან (07_demo_collections).
// გაეშვება მხოლოდ მაშინ, როცა გვერდები ჯერ არ არის. თავიდან: php bin/console content:reset --force
return new class extends Seeder {
    /** meta description გვერდებზე [ka, en] — ინგლისური slug-ით */
    private const DESCRIPTIONS = [
        'home' => [
            'ვებ დიზაინ სტუდია თბილისში: თანამედროვე საიტები, ონლაინ მაღაზიები, ბრენდინგი და SEO, რომლებიც ვიზიტორს კლიენტად აქცევს.',
            'Web design studio in Tbilisi: modern websites, online stores, branding and SEO that turn visitors into clients.',
        ],
        'about' => [
            'გაიცანით WEBDEV — დიზაინერებისა და დეველოპერების გუნდი, რომელიც ბიზნესებისთვის სწრაფ და მოსახერხებელ საიტებს ქმნის.',
            'Meet WEBDEV — a team of designers and developers building fast, user-friendly websites for businesses.',
        ],
        'services' => [
            'ვებ დიზაინი, ვებ დეველოპმენტი, ბრენდინგი და SEO — სრული ციკლი იდეიდან გაშვებამდე და შემდგომ მხარდაჭერამდე.',
            'Web design, web development, branding and SEO — the full cycle from idea to launch and ongoing support.',
        ],
        'portfolio' => [
            'WEBDEV-ის პროექტები: კორპორატიული საიტები, ონლაინ მაღაზიები და ბრენდინგი სხვადასხვა ინდუსტრიისთვის.',
            'WEBDEV projects: corporate websites, online stores and branding for different industries.',
        ],
        'team' => [
            'WEBDEV-ის გუნდი: არტ-დირექტორი, UI/UX დიზაინერი, ფრონტენდ დეველოპერი და პროექტის მენეჯერი.',
            'The WEBDEV team: art director, UI/UX designer, frontend developer and project manager.',
        ],
        'testimonials' => [
            'რას ამბობენ კლიენტები WEBDEV-თან თანამშრომლობაზე — შეფასებები საიტებისა და ბრენდინგის პროექტებზე.',
            'What clients say about working with WEBDEV — reviews of our website and branding projects.',
        ],
        'contact' => [
            'დაგვიკავშირდით ახალი საიტის, ონლაინ მაღაზიის ან ბრენდინგის პროექტისთვის — პირველი კონსულტაცია უფასოა.',
            'Get in touch about a new website, online store or branding project — the first consultation is free.',
        ],
    ];

    private const MAP = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d47657.23!2d44.7646!3d41.7151!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40440cd7e64f626b%3A0x61d084ede2576ea3!2sTbilisi!5e0!3m2!1sen!2sge!4v1700000000000!5m2!1sen!2sge';

    private Page $pages;
    private PageBlock $blocks;
    /** @var array<string, int> original_name => media id */
    private array $media = [];

    public function run(DB $db): void
    {
        if ((int)$db->value('SELECT COUNT(*) FROM pages') > 0) {
            return;
        }
        $this->pages = app(Page::class);
        $this->blocks = app(PageBlock::class);
        foreach ($db->select("SELECT id, original_name FROM media WHERE folder = 'demo'") as $row) {
            $this->media[$row['original_name']] = (int)$row['id'];
        }

        $home = $this->page(['მთავარი', 'Home'], ['mtavari', 'home'], 1, true);
        $about = $this->page(['ჩვენ შესახებ', 'About'], ['chven-shesakheb', 'about'], 2);
        $services = $this->page(['სერვისები', 'Services'], ['servisebi', 'services'], 3);
        $portfolio = $this->page(['პორტფოლიო', 'Portfolio'], ['portpolio', 'portfolio'], 4);
        $team = $this->page(['გუნდი', 'Our Team'], ['gundi', 'team'], 1, false, $about);
        $reviews = $this->page(['შეფასებები', 'Testimonials'], ['shepasebebi', 'testimonials'], 2, false, $about);
        $contact = $this->page(['კონტაქტი', 'Contact'], ['kontakti', 'contact'], 5);

        // მთავარი
        $this->hero($home, $portfolio, $contact);
        $this->about($home, $portfolio, $contact, 'secondary');
        $this->collectionBlock($home, 'services', ['ჩვენი სერვისები', 'Our Services'], ['რას ვაკეთებთ', 'What We Do'], ['limit' => 4]);
        $this->portfolio($home, 6, 'secondary');
        $this->collectionBlock($home, 'testimonials', ['შეფასებები', 'Testimonials'], ['რას ამბობენ კლიენტები', 'What Clients Say'], ['background' => 'dark']);
        $this->banner($home);

        // ჩვენ შესახებ
        $this->header($about);
        $this->block($about, 'rich_text', ['align' => 'left', 'background' => 'dark'], [
            'subtitle' => ['ჩვენი ისტორია', 'Our Story'],
            'title'    => ['ციფრული სტუდია თბილისიდან', 'A Digital Studio from Tbilisi'],
            'body'     => [
                '<p>WEBDEV პატარა, მაგრამ გამოცდილი გუნდია: დიზაინერები, დეველოპერები და მარკეტოლოგები, რომლებიც ერთად ვქმნით საიტებს, ონლაინ მაღაზიებს და ბრენდებს.</p>'
                . '<p>ყოველი პროექტი იწყება თქვენი ბიზნესის გაგებით და მთავრდება შედეგით, რომელიც იზომება — მეტი ზარი, მეტი შეკვეთა, ძლიერი ბრენდი.</p>',
                '<p>WEBDEV is a small but experienced team of designers, developers and marketers creating websites, online stores and brands together.</p>'
                . '<p>Every project starts with understanding your business and ends with measurable results — more calls, more orders, a stronger brand.</p>',
            ],
        ]);
        $this->collectionBlock($about, 'team_grid', ['გუნდი', 'Team'], ['ვინ ვართ', 'Who We Are'], ['limit' => 4]);
        $this->banner($about);

        // სერვისები
        $this->header($services);
        $this->collectionBlock($services, 'services', ['სერვისები', 'Services'], ['როგორ დაგეხმარებით', 'How We Help You'], []);
        $this->block($services, 'application_form', ['background' => 'secondary'], [
            'subtitle'      => ['ახალი პროექტი', 'New Project'],
            'title'         => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
            'text'          => ['<p>მოგვიყევით იდეაზე — 1 სამუშაო დღეში დაგიკავშირდებით შეთავაზებით.</p>', '<p>Tell us about your idea — we will get back to you with a proposal within 1 business day.</p>'],
            'message_label' => ['მოგვიყევით პროექტზე', 'Tell us about the project'],
            'button_label'  => ['მოთხოვნის გაგზავნა', 'Send Request'],
        ]);

        // პორტფოლიო (slug = კოლექციის მისამართი — პროექტის გვერდზე „უკან“ აქ მოდის)
        $this->header($portfolio);
        $this->portfolio($portfolio, 0, 'dark');

        $this->header($team);
        $this->collectionBlock($team, 'team_grid', ['გუნდი', 'Team'], ['ჩვენი გუნდი', 'Meet the Team'], []);

        $this->header($reviews);
        $this->collectionBlock($reviews, 'testimonials', ['შეფასებები', 'Testimonials'], ['რას ამბობენ კლიენტები', 'What Clients Say'], ['background' => 'dark']);

        $this->header($contact);
        $this->block($contact, 'contact', ['show_form' => true], [
            'subtitle' => ['კონტაქტი', 'Contact'],
            'title'    => ['დაგვიკავშირდით', 'Get in Touch'],
            'text'     => ['<p>გაქვთ კითხვა ან იდეა? მოგვწერეთ ან დაგვირეკეთ — სიამოვნებით გიპასუხებთ.</p>', '<p>Have a question or an idea? Write or call us — we are happy to help.</p>'],
        ]);
        $this->block($contact, 'map', ['embed_url' => self::MAP, 'height' => 450], []);
    }

    private function page(array $titles, array $slugs, int $sort, bool $home = false, ?int $parent = null): int
    {
        $descriptions = self::DESCRIPTIONS[$slugs[1]] ?? ['', ''];
        return $this->pages->save(null, ['parent_id' => $parent, 'status' => 'published', 'is_home' => $home, 'sort' => $sort, 'og_image_id' => null], [
            'ka' => ['title' => $titles[0], 'slug' => $slugs[0], 'meta_title' => '', 'meta_description' => $descriptions[0]],
            'en' => ['title' => $titles[1], 'slug' => $slugs[1], 'meta_title' => '', 'meta_description' => $descriptions[1]],
        ]);
    }

    /** @param array<string, array{0: mixed, 1: mixed}> $translated field => [ka, en] */
    private function block(int $pageId, string $type, array $settings, array $translated): void
    {
        $content = ['ka' => [], 'en' => []];
        foreach ($translated as $field => [$ka, $en]) {
            $content['ka'][$field] = $ka;
            $content['en'][$field] = $en;
        }
        $media = [];
        array_walk_recursive($settings, function ($value, $key) use (&$media): void {
            if ($key === 'image' && is_int($value)) {
                $media[] = $value;
            }
        });
        $this->blocks->save(null, $pageId, $type, $settings, $content, array_values(array_unique($media)));
    }

    /** @return array{0: list<array>, 1: array, 2: array} repeater: settings, ka, en */
    private function items(array $items): array
    {
        $settings = $ka = $en = [];
        foreach ($items as [$itemSettings, $texts]) {
            $id = bin2hex(random_bytes(5));
            $settings[] = ['_id' => $id] + $itemSettings;
            foreach ($texts as $field => [$k, $e]) {
                $ka[$id][$field] = $k;
                $en[$id][$field] = $e;
            }
        }
        return [$settings, $ka, $en];
    }

    private function img(string $name): ?int
    {
        return $this->media[$name] ?? null;
    }

    private function link(?int $pageId): array
    {
        return ['type' => $pageId ? 'page' : '', 'page_id' => $pageId, 'url' => '', 'new_tab' => false];
    }

    private function header(int $pageId): void
    {
        $this->block($pageId, 'page_header', ['image' => null, 'breadcrumb' => true], ['title' => ['', '']]);
    }

    /** services / team_grid / testimonials — კოლექციიდან */
    private function collectionBlock(int $pageId, string $type, array $subtitle, array $title, array $settings): void
    {
        $this->block($pageId, $type, $settings + ['source' => 'collection', 'category' => null, 'limit' => 0], ['subtitle' => $subtitle, 'title' => $title]);
    }

    private function portfolio(int $pageId, int $limit, string $background): void
    {
        $this->block($pageId, 'portfolio_grid', ['category' => null, 'limit' => $limit, 'show_filter' => true, 'background' => $background], [
            'subtitle' => ['პორტფოლიო', 'Portfolio'],
            'title'    => ['ბოლო პროექტები', 'Recent Work'],
        ]);
    }

    private function hero(int $pageId, int $portfolio, int $contact): void
    {
        [$slides, $ka, $en] = $this->items([
            [['image' => $this->img('carousel-1.jpg'), 'button_link' => $this->link($contact), 'button2_link' => $this->link($portfolio)], [
                'subtitle'      => ['ვებ დიზაინ სტუდია', 'Web Design Studio'],
                'title'         => ['საიტები, რომლებიც მუშაობს', 'Websites That Work'],
                'text'          => ['ვქმნით ლამაზ, სწრაფ და მოსახერხებელ საიტებს, რომლებიც ვიზიტორს კლიენტად აქცევს.', 'We build beautiful, fast and easy-to-use websites that turn visitors into customers.'],
                'button_label'  => ['უფასო კონსულტაცია', 'Free Consultation'],
                'button2_label' => ['ნამუშევრები', 'Our Work'],
            ]],
            [['image' => $this->img('carousel-2.jpg'), 'button_link' => $this->link($contact)], [
                'subtitle'     => ['ბრენდინგი', 'Branding'],
                'title'        => ['ბრენდი, რომელიც გამოირჩევა', 'A Brand That Stands Out'],
                'text'         => ['ლოგოდან სრულ ვიზუალურ იდენტობამდე — ერთი გუნდით.', 'From logo to a complete visual identity — with one team.'],
                'button_label' => ['დაგვიკავშირდით', 'Contact Us'],
            ]],
        ]);
        $this->block($pageId, 'hero_carousel', ['slides' => $slides], ['slides' => [$ka, $en]]);
    }

    private function about(int $pageId, int $portfolio, int $contact, string $background): void
    {
        [$items, $ka, $en] = $this->items([
            [[], ['text' => ['უნიკალური დიზაინი — შაბლონების გარეშე', 'Unique design — no generic templates']]],
            [[], ['text' => ['სწრაფი, მობილურზე მორგებული და SEO-მზა საიტები', 'Fast, mobile-friendly, SEO-ready websites']]],
            [[], ['text' => ['მართვის პანელი — კონტენტს თავად ცვლით', 'An admin panel — you edit the content yourself']]],
        ]);
        $this->block($pageId, 'about', [
            'items' => $items, 'image' => $this->img('about.jpg'), 'background' => $background,
            'button1_link' => $this->link($portfolio), 'button2_link' => $this->link($contact),
        ], [
            'subtitle'      => ['ჩვენ შესახებ', 'About Us'],
            'title'         => ['ციფრული სტუდია', 'Digital Studio'],
            'text'          => ['<p>10 წელზე მეტია ვეხმარებით ბიზნესებს ონლაინ ზრდაში: ვაპროექტებთ, ვქმნით და ვავითარებთ საიტებს, ონლაინ მაღაზიებს და ბრენდებს.</p>',
                                '<p>For over 10 years we have helped businesses grow online: we plan, design and develop websites, online stores and brands.</p>'],
            'items'         => [$ka, $en],
            'button1_label' => ['პორტფოლიო', 'Portfolio'],
            'button2_label' => ['დაგვიკავშირდით', 'Contact Us'],
        ]);
    }

    private function banner(int $pageId): void
    {
        $this->block($pageId, 'banner_signup', ['background' => 'secondary'], [
            'subtitle' => ['სიახლეები', 'Newsletter'],
            'title'    => ['იყავით საქმის კურსში', 'Stay in the Loop'],
            'text'     => ['თვეში ერთხელ — რჩევები ვებ დიზაინზე, ახალი პროექტები და სტუდიის სიახლეები.', 'Once a month — web design tips, new projects and studio news.'],
        ]);
    }
};
