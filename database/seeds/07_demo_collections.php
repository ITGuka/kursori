<?php
declare(strict_types=1);

use App\Models\Category;
use App\Models\Collection;
use Core\Database\DB;
use Core\Database\Seeder;

// ვებ სტუდიის კოლექციები: სერვისები, გუნდი, შეფასებები, პორტფოლიო (კატეგორიებით).
// სურათები — 04_demo_media (resources/stock), ჩაანაცვლეთ რეალურით. გაეშვება მხოლოდ მაშინ, როცა კოლექციებში ჯერ არაფერია.
return new class extends Seeder {
    private Collection $items;
    private Category $categories;
    /** @var array<string, int> */
    private array $media = [];

    public function run(DB $db): void
    {
        if ((int)$db->value('SELECT COUNT(*) FROM collection_items') > 0) {
            return;
        }
        $this->items = app(Collection::class);
        $this->categories = app(Category::class);
        foreach ($db->select("SELECT id, original_name FROM media WHERE folder = 'demo'") as $row) {
            $this->media[$row['original_name']] = (int)$row['id'];
        }

        // ---- სერვისები ----
        $services = [
            ['service-1.jpg', ['ვებ დიზაინი', 'Web Design'], ['veb-dizaini', 'web-design'],
                ['უნიკალური, თანამედროვე დიზაინი, რომელიც თქვენს ბრენდს ასახავს და ვიზიტორს მარტივად მიიყვანს მიზნამდე.',
                 'Unique, modern design that reflects your brand and guides visitors straight to their goal.'],
                ['<p>ვიწყებთ კვლევით: ვინ არის თქვენი მომხმარებელი და რა სურს. შემდეგ — სტრუქტურა, პროტოტიპი და დიზაინი ყველა ეკრანისთვის.</p><ul><li>UX კვლევა და სტრუქტურა</li><li>პროტოტიპი</li><li>დიზაინი კომპიუტერისა და მობილურისთვის</li></ul>',
                 '<p>We start with research: who your customer is and what they need. Then structure, prototype and design for every screen.</p><ul><li>UX research and structure</li><li>Prototype</li><li>Design for desktop and mobile</li></ul>']],
            ['service-2.jpg', ['ვებ დეველოპმენტი', 'Web Development'], ['veb-developmenti', 'web-development'],
                ['სწრაფი, უსაფრთხო და მართვადი საიტები და ონლაინ მაღაზიები — მართვის პანელით.',
                 'Fast, secure and manageable websites and online stores — with an admin panel.'],
                ['<p>ვაწყობთ საიტს, რომელსაც თავად მართავთ: გვერდები, მენიუ, თარგმანები, სიახლეები და ფორმები — ერთ პანელში.</p><ul><li>მრავალენოვანი საიტები</li><li>ონლაინ მაღაზია</li><li>ინტეგრაციები</li></ul>',
                 '<p>We build a site you manage yourself: pages, menus, translations, news and forms — in one panel.</p><ul><li>Multilingual websites</li><li>Online stores</li><li>Integrations</li></ul>']],
            ['service-3.jpg', ['ბრენდინგი', 'Branding'], ['brendingi', 'branding'],
                ['ლოგო, ფერები, შრიფტები და ბრენდბუქი — იდენტობა, რომელიც ახსოვთ.',
                 'Logo, colours, typography and a brand book — an identity people remember.'],
                ['<p>ვქმნით ვიზუალურ ენას, რომელიც ერთნაირად კარგად მუშაობს საიტზე, სოციალურ ქსელებსა და ბეჭდურ მასალაზე.</p>',
                 '<p>We create a visual language that works equally well on the website, social media and print.</p>']],
            ['service-4.jpg', ['SEO და მარკეტინგი', 'SEO & Marketing'], ['seo-marketingi', 'seo-marketing'],
                ['საიტი, რომელსაც პოულობენ: ტექნიკური SEO, კონტენტი და ანალიტიკა.',
                 'A website people find: technical SEO, content and analytics.'],
                ['<p>ვაწყობთ ანალიტიკას, ვასწორებთ ტექნიკურ SEO-ს და ვგეგმავთ კონტენტს, რომ საიტმა ახალი კლიენტები მოიყვანოს.</p>',
                 '<p>We set up analytics, fix technical SEO and plan content so the site brings new customers.</p>']],
        ];
        foreach ($services as [$image, $title, $slug, $excerpt, $body]) {
            $this->item('services', null, $image, $title, $slug, [], ['excerpt' => $excerpt, 'body' => $body]);
        }

        // ---- გუნდი ----
        $studio = $this->category('team', ['სტუდია', 'Studio'], ['studia', 'studio']);
        $team = [
            ['team-1.jpg', ['ნინო ბერიძე', 'Nino Beridze'], ['არტ-დირექტორი', 'Art Director'], ["გამოცდილება: 12 წელი\nპროექტები: 150+", "Experience: 12 years\nProjects: 150+"]],
            ['team-2.jpg', ['ანა მელაძე', 'Ana Meladze'], ['UI/UX დიზაინერი', 'UI/UX Designer'], ["გამოცდილება: 7 წელი\nპროექტები: 90+", "Experience: 7 years\nProjects: 90+"]],
            ['team-3.jpg', ['მარიამ კაპანაძე', 'Mariam Kapanadze'], ['ფრონტენდ დეველოპერი', 'Frontend Developer'], ["გამოცდილება: 6 წელი\nპროექტები: 70+", "Experience: 6 years\nProjects: 70+"]],
            ['team-4.jpg', ['თამარ ჯაფარიძე', 'Tamar Japaridze'], ['პროექტის მენეჯერი', 'Project Manager'], ["გამოცდილება: 9 წელი\nპროექტები: 120+", "Experience: 9 years\nProjects: 120+"]],
        ];
        foreach ($team as $i => [$image, $name, $position, $attributes]) {
            $this->item('team', $studio, $image, $name, ['member-' . ($i + 1), 'member-' . ($i + 1)],
                ['link' => ['type' => '', 'page_id' => null, 'url' => '', 'new_tab' => false]], ['position' => $position, 'attributes' => $attributes]);
        }

        // ---- შეფასებები ----
        $testimonials = [
            ['testimonial-1.jpg', ['გიორგი ლომიძე', 'Giorgi Lomidze'], ['დამფუძნებელი, „ფერმა“', 'Founder, Ferma'],
                ['ახალი საიტის გაშვებიდან სამ თვეში ონლაინ შეკვეთები გაორმაგდა. გუნდი ყოველთვის პასუხობს და ესმის, რა გვჭირდება.',
                 'Online orders doubled within three months of launching the new site. The team always responds and understands what we need.']],
            ['testimonial-2.jpg', ['ლანა ანდერსონი', 'Lana Anderson'], ['მარკეტინგის მენეჯერი, Northwind', 'Marketing Manager, Northwind'],
                ['ბრენდინგი და საიტი ერთ გუნდთან — ეს დაზოგილი დროა და ერთიანი შედეგი. გირჩევთ!',
                 'Branding and website with one team — that means saved time and a consistent result. Highly recommended!']],
            ['testimonial-3.jpg', ['ეკა გელაშვილი', 'Eka Gelashvili'], ['დირექტორი, „არქი ჰაუსი“', 'Director, Arqi House'],
                ['მართვის პანელი იმდენად მარტივია, რომ კონტენტს თავად ვცვლით — დეველოპერის გარეშე.',
                 'The admin panel is so simple that we update the content ourselves — no developer needed.']],
        ];
        foreach ($testimonials as $i => [$image, $name, $profession, $quote]) {
            $this->item('testimonials', null, $image, $name, ['review-' . ($i + 1), 'review-' . ($i + 1)], [], ['quote' => $quote, 'profession' => $profession]);
        }

        // ---- პორტფოლიო ----
        $web = $this->category('portfolio', ['ვებ დიზაინი', 'Web Design'], ['veb-dizaini', 'web-design']);
        $brand = $this->category('portfolio', ['ბრენდინგი', 'Branding'], ['brendingi', 'branding']);
        $shop = $this->category('portfolio', ['ონლაინ მაღაზია', 'E-commerce'], ['onlain-maghazia', 'e-commerce']);
        $projects = [
            [$web, 'portfolio-1.jpg', ['მოდის სახლის საიტი', 'Fashion House Website'], ['modis-sakhlis-saiti', 'fashion-house-website'], 2026, ['მოდის სახლი', 'Fashion House']],
            [$shop, 'portfolio-2.jpg', ['ტანსაცმლის ონლაინ მაღაზია', 'Apparel Online Store'], ['tansatsmlis-maghazia', 'apparel-store'], 2025, ['Style Market', 'Style Market']],
            [$brand, 'portfolio-3.jpg', ['სტუდიის ბრენდინგი', 'Studio Branding'], ['studiis-brendingi', 'studio-branding'], 2025, ['Photo Lab', 'Photo Lab']],
            [$web, 'portfolio-4.jpg', ['ღონისძიებების სააგენტო', 'Event Agency'], ['ghonisdziebebis-saagento', 'event-agency'], 2024, ['Event Pro', 'Event Pro']],
            [$brand, 'portfolio-5.jpg', ['კოსმეტიკის ბრენდი', 'Cosmetics Brand'], ['kosmetikis-brendi', 'cosmetics-brand'], 2024, ['Glow', 'Glow']],
            [$shop, 'portfolio-6.jpg', ['აქსესუარების მაღაზია', 'Accessories Store'], ['aksesuarebis-maghazia', 'accessories-store'], 2023, ['Accent', 'Accent']],
        ];
        foreach ($projects as [$category, $image, $title, $slug, $year, $client]) {
            $this->item('portfolio', $category, $image, $title, $slug,
                ['year' => $year, 'website' => ['type' => '', 'page_id' => null, 'url' => '', 'new_tab' => true]], [
                    'client'  => $client,
                    'excerpt' => ['დემო პროექტი — ჩაანაცვლეთ სტუდიის რეალური ნამუშევრით.', 'Demo project — replace it with the studio’s real work.'],
                    'body'    => ['<h3>ამოცანა</h3><p>რა პრობლემა ჰქონდა კლიენტს.</p><h3>გადაწყვეტა</h3><p>რა გავაკეთეთ.</p><h3>შედეგი</h3><p>რა შეიცვალა — ციფრებით.</p>',
                                  '<h3>Challenge</h3><p>What problem the client had.</p><h3>Solution</h3><p>What we did.</p><h3>Result</h3><p>What changed — in numbers.</p>'],
                ]);
        }
    }

    /** @param array{0: string, 1: string} $names @param array{0: string, 1: string} $slugs */
    private function category(string $type, array $names, array $slugs): int
    {
        return $this->categories->save(null, $type, ['parent_id' => null, 'sort' => 0, 'is_active' => true], [
            'ka' => ['name' => $names[0], 'slug' => $slugs[0]],
            'en' => ['name' => $names[1], 'slug' => $slugs[1]],
        ]);
    }

    /** @param array<string, array{0: mixed, 1: mixed}> $translated */
    private function item(string $type, ?int $category, string $image, array $title, array $slug, array $settings, array $translated): void
    {
        $content = ['ka' => [], 'en' => []];
        foreach ($translated as $field => [$ka, $en]) {
            $content['ka'][$field] = $ka;
            $content['en'][$field] = $en;
        }
        $this->items->save(null, $type, ['category_id' => $category, 'image_id' => $this->media[$image] ?? null, 'is_active' => true], $settings, [
            'ka' => ['title' => $title[0], 'slug' => $slug[0]],
            'en' => ['title' => $title[1], 'slug' => $slug[1]],
        ], $content, []);
    }
};
