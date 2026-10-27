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
            'გაიცანით KURSORI — დიზაინერებისა და დეველოპერების გუნდი, რომელიც ბიზნესებისთვის სწრაფ და მოსახერხებელ საიტებს ქმნის.',
            'Meet KURSORI — a team of designers and developers building fast, user-friendly websites for businesses.',
        ],
        'services' => [
            'ვებ დიზაინი, ვებ დეველოპმენტი, ბრენდინგი და SEO — სრული ციკლი იდეიდან გაშვებამდე და შემდგომ მხარდაჭერამდე.',
            'Web design, web development, branding and SEO — the full cycle from idea to launch and ongoing support.',
        ],
        'portfolio' => [
            'KURSORI-ის პროექტები: კორპორატიული საიტები, ონლაინ მაღაზიები და ბრენდინგი სხვადასხვა ინდუსტრიისთვის.',
            'KURSORI projects: corporate websites, online stores and branding for different industries.',
        ],
        'team' => [
            'KURSORI-ის გუნდი: არტ-დირექტორი, UI/UX დიზაინერი, ფრონტენდ დეველოპერი და პროექტის მენეჯერი.',
            'The KURSORI team: art director, UI/UX designer, frontend developer and project manager.',
        ],
        'testimonials' => [
            'რას ამბობენ კლიენტები KURSORI-თან თანამშრომლობაზე — შეფასებები საიტებისა და ბრენდინგის პროექტებზე.',
            'What clients say about working with KURSORI — reviews of our website and branding projects.',
        ],
        'contact' => [
            'დაგვიკავშირდით ახალი საიტის, ონლაინ მაღაზიის ან ბრენდინგის პროექტისთვის — პირველი კონსულტაცია უფასოა.',
            'Get in touch about a new website, online store or branding project — the first consultation is free.',
        ],
    ];

    /** ჰეროს გზავნილი [ka, en] */
    public const HERO_TITLE = ['საიტები, რომლებიც შეკვეთებს მოგიტანთ', 'Websites That Bring You Orders'];
    public const HERO_TEXT = [
        'ვებ დიზაინი, ონლაინ მაღაზიები, ბრენდინგი და SEO ერთ გუნდში — მართვის პანელით, რომელშიც კონტენტს თავად შეცვლით.',
        'Web design, online stores, branding and SEO under one roof — with an admin panel where you update the content yourself.',
    ];

    /** ნდობის ზოლი: [მნიშვნელობა, წარწერა] — [ka, en] */
    public const STATS = [
        [['10+', '10+'], ['წელი ბაზარზე', 'Years in business']],
        [['150+', '150+'], ['დასრულებული პროექტი', 'Projects delivered']],
        [['4', '4'], ['მიმართულება ერთ გუნდში', 'Services under one roof']],
        [['1 დღე', '1 day'], ['პასუხი ყოველ მოთხოვნაზე', 'Reply to every request']],
    ];

    /** ფასების პაკეტები (სადემონსტრაციო ფასები — გაშვებამდე ჩაანაცვლეთ): ველი => [ka, en] */
    public const PACKAGES = [
        [
            'name'         => ['სტარტი', 'Start'],
            'price'        => ['1 500 ₾-დან', 'From 1,500 GEL'],
            'note'         => ['ვადა: 3–4 კვირა', 'Timeline: 3–4 weeks'],
            'features'     => ["სავიზიტო საიტი 5 გვერდამდე\nმობილურზე მორგებული დიზაინი\nმართვის პანელი\nსაკონტაქტო ფორმა და რუკა\nსაბაზისო SEO",
                               "Business card site, up to 5 pages\nMobile-ready design\nAdmin panel\nContact form and map\nBasic SEO"],
            'badge'        => ['', ''],
            'button_label' => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
        ],
        [
            'name'         => ['ბიზნესი', 'Business'],
            'price'        => ['3 500 ₾-დან', 'From 3,500 GEL'],
            'note'         => ['ვადა: 5–7 კვირა', 'Timeline: 5–7 weeks'],
            'features'     => ["15 გვერდამდე, უნიკალური დიზაინი\nორი ან მეტი ენა\nსიახლეები ან ბლოგი\nპორტფოლიო ან კატალოგი\nტექნიკური SEO და ანალიტიკა\n1 თვე უფასო მხარდაჭერა",
                               "Up to 15 pages, custom design\nTwo or more languages\nNews or blog\nPortfolio or catalogue\nTechnical SEO and analytics\n1 month of free support"],
            'badge'        => ['პოპულარული', 'Most popular'],
            'button_label' => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
        ],
        [
            'name'         => ['ონლაინ მაღაზია', 'Online Store'],
            'price'        => ['6 000 ₾-დან', 'From 6,000 GEL'],
            'note'         => ['ვადა: 7–10 კვირა', 'Timeline: 7–10 weeks'],
            'features'     => ["კატალოგი ფილტრებით\nბარათით გადახდა\nმარაგისა და შეკვეთების მართვა\nმიწოდების ღირებულების გამოთვლა\nპროდუქტების SEO\n1 თვე უფასო მხარდაჭერა",
                               "Catalogue with filters\nCard payments\nStock and order management\nDelivery cost calculation\nProduct SEO\n1 month of free support"],
            'badge'        => ['', ''],
            'button_label' => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
        ],
    ];

    /** ზოგადი ხშირი კითხვები (ფასების გვერდი): [კითხვა, პასუხი] — [ka, en] */
    public const FAQ = [
        [['რამდენი ხანი სჭირდება საიტის დამზადებას?', 'How long does a website take?'],
         ['სავიზიტო საიტს 3–4 კვირა, ბიზნეს საიტს 5–7, ონლაინ მაღაზიას 7–10 კვირა. ზუსტ ვადას ბრიფის შემდეგ გეტყვით.',
          'A business card site takes 3–4 weeks, a business site 5–7 and an online store 7–10. We confirm the exact timeline after the brief.']],
        [['როგორ ხდება გადახდა?', 'How does payment work?'],
         ['50% სამუშაოს დაწყებისას და 50% საიტის გაშვებამდე. დიდ პროექტებს ეტაპებად ვყოფთ.',
          '50% when work starts and 50% before launch. Larger projects are split into stages.']],
        [['შემიძლია კონტენტი თავად შევცვალო?', 'Can I update the content myself?'],
         ['დიახ. ყველა საიტს აქვს მართვის პანელი, სადაც გვერდებს, სურათებს, მენიუსა და თარგმანებს თავად ცვლით.',
          'Yes. Every site has an admin panel where you edit pages, images, menus and translations yourself.']],
        [['ჰოსტინგი და დომენი ფასში შედის?', 'Are hosting and the domain included?'],
         ['არა, ისინი თქვენს სახელზე იყიდება, რომ საიტი სრულად თქვენი იყოს. შერჩევასა და გამართვაში დაგეხმარებით.',
          'No, they are bought in your name so the site fully belongs to you. We help you choose and set them up.']],
        [['რა ხდება გაშვების შემდეგ?', 'What happens after launch?'],
         ['პირველი თვის მხარდაჭერა უფასოა. შემდეგ შეგიძლიათ აირჩიოთ ყოველთვიური მხარდაჭერის პაკეტი.',
          'The first month of support is free. After that you can choose a monthly support plan.']],
    ];

    /** პროცესის ეტაპები: [სათაური, ტექსტი] — [ka, en] */
    public const PROCESS = [
        [['ბრიფი და კვლევა', 'Brief & Research'],
         ['ვეცნობით თქვენს ბიზნესს, მომხმარებელს და მიზანს. ვთანხმდებით სტრუქტურაზე, ვადებსა და ბიუჯეტზე.',
          'We get to know your business, customers and goals, then agree on structure, timeline and budget.']],
        [['პროტოტიპი', 'Prototype'],
         ['ვაწყობთ გვერდების სქემას, რომ დიზაინამდე შევთანხმდეთ, რა სად იქნება.',
          'We map out the pages so we agree on what goes where before any design work.']],
        [['დიზაინი', 'Design'],
         ['ვქმნით დიზაინს კომპიუტერისა და მობილურისთვის და ვასწორებთ თქვენი შენიშვნებით.',
          'We design for desktop and mobile and refine it with your feedback.']],
        [['დეველოპმენტი', 'Development'],
         ['ვაწყობთ საიტს მართვის პანელით, ვამატებთ კონტენტს და ვამოწმებთ ყველა მოწყობილობაზე.',
          'We build the site with an admin panel, add the content and test it on every device.']],
        [['გაშვება და მხარდაჭერა', 'Launch & Support'],
         ['ვუშვებთ საიტს, ვაწყობთ ანალიტიკას და გასწავლით პანელის მართვას.',
          'We launch the site, set up analytics and show your team how to use the admin panel.']],
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
        $pricing = $this->page(['ფასები', 'Pricing'], ['fasebi', 'pricing'], 5);
        // slug = ბლოგის კოლექციის მისამართი (სტატიის გვერდზე „უკან“ აქ მოდის)
        $blog = $this->page(['ბლოგი', 'Blog'], ['blogi', 'blog'], 6);
        $contact = $this->page(['კონტაქტი', 'Contact'], ['kontakti', 'contact'], 7);

        // მთავარი
        $this->hero($home, $portfolio, $contact);
        $this->stats($home);
        $this->about($home, $portfolio, $contact, 'secondary');
        $this->collectionBlock($home, 'services', ['ჩვენი სერვისები', 'Our Services'], ['რას ვაკეთებთ', 'What We Do'], ['limit' => 4, 'layout' => 'grid']);
        $this->portfolio($home, 6, 'secondary');
        $this->process($home);
        $this->collectionBlock($home, 'testimonials', ['შეფასებები', 'Testimonials'], ['რას ამბობენ კლიენტები', 'What Clients Say'], ['background' => 'dark']);
        $this->latestPosts($home, $blog);
        $this->banner($home);

        // ჩვენ შესახებ
        $this->header($about);
        $this->block($about, 'rich_text', ['align' => 'left', 'background' => 'dark'], [
            'subtitle' => ['ჩვენი ისტორია', 'Our Story'],
            'title'    => ['ციფრული სტუდია თბილისიდან', 'A Digital Studio from Tbilisi'],
            'body'     => [
                '<p>KURSORI პატარა, მაგრამ გამოცდილი გუნდია: დიზაინერები, დეველოპერები და მარკეტოლოგები, რომლებიც ერთად ვქმნით საიტებს, ონლაინ მაღაზიებს და ბრენდებს.</p>'
                . '<p>ყოველი პროექტი იწყება თქვენი ბიზნესის გაგებით და მთავრდება შედეგით, რომელიც იზომება — მეტი ზარი, მეტი შეკვეთა, ძლიერი ბრენდი.</p>',
                '<p>KURSORI is a small but experienced team of designers, developers and marketers creating websites, online stores and brands together.</p>'
                . '<p>Every project starts with understanding your business and ends with measurable results — more calls, more orders, a stronger brand.</p>',
            ],
        ]);
        $this->collectionBlock($about, 'team_grid', ['გუნდი', 'Team'], ['ვინ ვართ', 'Who We Are'], ['limit' => 4]);
        $this->banner($about);

        // სერვისები
        $this->header($services);
        $this->collectionBlock($services, 'services', ['სერვისები', 'Services'], ['როგორ დაგეხმარებით', 'How We Help You'], ['layout' => 'grid']);
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

        $this->pricingPage($pricing, $contact);
        $this->blogPage($blog);

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
                'title'         => self::HERO_TITLE,
                'text'          => self::HERO_TEXT,
                'button_label'  => ['უფასო კონსულტაცია', 'Free Consultation'],
                'button2_label' => ['პორტფოლიო', 'Portfolio'],
            ]],
            [['image' => $this->img('carousel-2.jpg'), 'button_link' => $this->link($contact)], [
                'subtitle'     => ['ბრენდინგი', 'Branding'],
                'title'        => ['ბრენდი, რომელიც გამოირჩევა', 'A Brand That Stands Out'],
                'text'         => ['ლოგოდან სრულ ვიზუალურ იდენტობამდე — ერთი გუნდით.', 'From logo to a complete visual identity — with one team.'],
                'button_label' => ['დაგვიკავშირდით', 'Contact Us'],
            ]],
        ]);
        // სტატიკური: ჩანს მხოლოდ პირველი სლაიდი, მეორე ადმინში რჩება
        $this->block($pageId, 'hero_carousel', ['mode' => 'static', 'slides' => $slides], ['slides' => [$ka, $en]]);
    }

    /** ფასები: პაკეტები + ხშირი კითხვები + შეთავაზების ფორმა (public — არსებული საიტის ერთჯერადი განახლებისთვისაც) */
    public function pricingPage(int $pageId, int $contact): void
    {
        $this->pages ??= app(Page::class);
        $this->blocks ??= app(PageBlock::class);
        $this->header($pageId);
        [$packages, $ka, $en] = $this->items(array_map(fn (array $row) => [['button_link' => $this->link($contact)], $row], self::PACKAGES));
        $this->block($pageId, 'pricing', ['packages' => $packages, 'background' => 'dark'], [
            'subtitle' => ['ფასები', 'Pricing'],
            'title'    => ['პაკეტები', 'Packages'],
            'text'     => ['ფასები საორიენტაციოა. ზუსტ ღირებულებას ბრიფის შემდეგ, უფასო კონსულტაციაზე დაგისახელებთ.',
                           'Prices are a guide. We give you an exact quote after the brief, at a free consultation.'],
            'packages' => [$ka, $en],
        ]);
        [$faq, $ka, $en] = $this->items(array_map(fn (array $row) => [[], ['question' => $row[0], 'answer' => $row[1]]], self::FAQ));
        $this->block($pageId, 'faq', ['items' => $faq, 'background' => 'secondary'],
            ['subtitle' => ['კითხვები', 'Questions'], 'title' => ['ხშირად გვეკითხებიან', 'Frequently Asked'], 'items' => [$ka, $en]]);
        $this->block($pageId, 'application_form', ['background' => 'dark'], [
            'subtitle'      => ['ახალი პროექტი', 'New Project'],
            'title'         => ['მოითხოვეთ შეთავაზება', 'Request a Proposal'],
            'text'          => ['<p>მოგვიყევით იდეაზე — 1 სამუშაო დღეში დაგიკავშირდებით შეთავაზებით.</p>', '<p>Tell us about your idea — we will get back to you with a proposal within 1 business day.</p>'],
            'message_label' => ['მოგვიყევით პროექტზე', 'Tell us about the project'],
            'button_label'  => ['მოთხოვნის გაგზავნა', 'Send Request'],
        ]);
    }

    /** ბლოგის გვერდი: ყველა სტატია (public — ერთჯერადი განახლებისთვისაც) */
    public function blogPage(int $pageId): void
    {
        $this->blocks ??= app(PageBlock::class);
        $this->header($pageId);
        $this->block($pageId, 'blog_list', ['category' => null, 'limit' => 0, 'button_link' => $this->link(null), 'background' => 'dark'], [
            'subtitle' => ['ბლოგი', 'Blog'], 'title' => ['რჩევები და სიახლეები', 'Tips & News'], 'button_label' => ['', ''],
        ]);
    }

    /** მთავარზე — 3 ბოლო სტატია ბლოგის ბმულით (public — ერთჯერადი განახლებისთვისაც) */
    public function latestPosts(int $pageId, int $blog): void
    {
        $this->blocks ??= app(PageBlock::class);
        $this->block($pageId, 'blog_list', ['category' => null, 'limit' => 3, 'button_link' => $this->link($blog), 'background' => 'secondary'], [
            'subtitle' => ['ბლოგი', 'Blog'], 'title' => ['ბოლო სტატიები', 'Latest Articles'], 'button_label' => ['ყველა სტატია', 'All Articles'],
        ]);
    }

    /** თანამშრომლობის ეტაპები */
    private function process(int $pageId): void
    {
        [$steps, $ka, $en] = $this->items(array_map(fn (array $row) => [[], ['title' => $row[0], 'text' => $row[1]]], self::PROCESS));
        $this->block($pageId, 'process', ['steps' => $steps, 'background' => 'dark'],
            ['subtitle' => ['როგორ ვმუშაობთ', 'How We Work'], 'title' => ['იდეიდან გაშვებამდე', 'From Idea to Launch'], 'steps' => [$ka, $en]]);
    }

    /** ნდობის ზოლი ჰეროს ქვეშ */
    private function stats(int $pageId): void
    {
        [$items, $ka, $en] = $this->items(array_map(fn (array $row) => [[], ['value' => $row[0], 'label' => $row[1]]], self::STATS));
        $this->block($pageId, 'stats', ['items' => $items, 'background' => 'dark'], ['items' => [$ka, $en]]);
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
