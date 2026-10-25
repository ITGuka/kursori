<?php
declare(strict_types=1);

use App\Models\Category;
use App\Models\Collection;
use Core\Database\DB;
use Core\Database\Seeder;

// ვებ სტუდიის კოლექციები: სერვისები, გუნდი, შეფასებები, პორტფოლიო (კატეგორიებით).
// სურათები — 04_demo_media (resources/stock), ჩაანაცვლეთ რეალურით. გაეშვება მხოლოდ მაშინ, როცა კოლექციებში ჯერ არაფერია.
// ქეისები და შეფასებები რეალისტური, მაგრამ გამოგონილია (კლიენტები, ციფრები) — გაშვებამდე ჩაანაცვლეთ ნამდვილით.
return new class extends Seeder {
    /** პორტფოლიოს ქეისები — ინგლისური slug => client, excerpt, body [ka, en] */
    public const CASES = [
        'fashion-house-website' => [
            'client'  => ['„თეთრი ქსოვილი“', 'Tetri Ksovili'],
            'excerpt' => ['ქართული მოდის სახლის საიტი კოლექციების ლუკბუქით, შოურუმში ჩაწერით და ორენოვანი კატალოგით.',
                          'A website for a Georgian fashion house with a collection lookbook, showroom booking and a bilingual catalogue.'],
            'body'    => ['<h3>ამოცანა</h3><p>მოდის სახლი კოლექციებს მხოლოდ Instagram-ზე აჩვენებდა, შოურუმში ვიზიტებს კი ტელეფონით წერდა. უცხოელ მყიდველებს ინფორმაცია ინგლისურად არ ჰქონდათ.</p>'
                          . '<h3>გადაწყვეტა</h3><ul><li>ლუკბუქი სეზონური კოლექციებით და დიდი ფოტოებით</li><li>შოურუმში ვიზიტის ონლაინ ჩაწერა თავისუფალი საათებით</li><li>ქართული და ინგლისური ვერსია, კოლექციებს გუნდი ადმინიდან ამატებს</li></ul>'
                          . '<h3>შედეგი</h3><ul><li>შოურუმის ვიზიტების 60% ახლა საიტიდან იწერება</li><li>უცხოური შეკვეთები გაშვებიდან 6 თვეში 3-ჯერ გაიზარდა</li><li>გაშვება — 7 კვირაში</li></ul>',
                          '<h3>Challenge</h3><p>The fashion house showed its collections only on Instagram and booked showroom visits by phone. International buyers had no information in English.</p>'
                          . '<h3>Solution</h3><ul><li>A lookbook of seasonal collections with large photography</li><li>Online showroom booking with available time slots</li><li>Georgian and English versions; the team adds collections from the admin panel</li></ul>'
                          . '<h3>Result</h3><ul><li>60% of showroom visits are now booked through the site</li><li>International orders tripled within 6 months of launch</li><li>Launched in 7 weeks</li></ul>'],
        ],
        'apparel-store' => [
            'client'  => ['„ვერე სთრით“', 'Vere Street'],
            'excerpt' => ['ტანსაცმლის ონლაინ მაღაზია ზომების ფილტრით, ბარათით გადახდით და მარაგის მართვით ერთ პანელში.',
                          'An apparel online store with size filters, card payments and stock management in one panel.'],
            'body'    => ['<h3>ამოცანა</h3><p>შეკვეთები Facebook-ის მესიჯებით მოდიოდა: პასუხი საათობით ყოვნდებოდა, მარაგი Excel-ში იწერებოდა და ხშირად ერთი ნივთი ორჯერ იყიდებოდა.</p>'
                          . '<h3>გადაწყვეტა</h3><ul><li>კატალოგი ზომის, ფერისა და ფასის ფილტრებით</li><li>ბარათით გადახდა და მიწოდების ღირებულების ავტომატური გამოთვლა</li><li>მარაგი ადმინში — გაყიდული ზომა ავტომატურად ქრება</li></ul>'
                          . '<h3>შედეგი</h3><ul><li>ონლაინ შეკვეთები 3 თვეში გაორმაგდა</li><li>შეკვეთის დამუშავების დრო 2 საათიდან 10 წუთამდე შემცირდა</li><li>ორმაგი გაყიდვები აღარ ხდება</li></ul>',
                          '<h3>Challenge</h3><p>Orders came in through Facebook messages: replies took hours, stock was tracked in Excel and the same item was often sold twice.</p>'
                          . '<h3>Solution</h3><ul><li>A catalogue with size, colour and price filters</li><li>Card payments and automatic delivery cost calculation</li><li>Stock in the admin panel — a sold-out size disappears automatically</li></ul>'
                          . '<h3>Result</h3><ul><li>Online orders doubled within 3 months</li><li>Order handling time dropped from 2 hours to 10 minutes</li><li>No more double sales</li></ul>'],
        ],
        'studio-branding' => [
            'client'  => ['ფოტოსტუდია „კადრი 35“', 'Kadri 35 Photo Studio'],
            'excerpt' => ['ფოტოსტუდიის სრული იდენტობა: ლოგო, ფერები, შრიფტები, ბრენდბუქი და სოციალური ქსელების შაბლონები.',
                          'A complete identity for a photo studio: logo, colours, typography, a brand book and social media templates.'],
            'body'    => ['<h3>ამოცანა</h3><p>სტუდია ახალ სივრცეში გადადიოდა და უნდოდა, კომერციული კლიენტებისთვის უფრო სერიოზულად გამოჩენილიყო. ძველი ლოგო მცირე ზომაზე არ იკითხებოდა.</p>'
                          . '<h3>გადაწყვეტა</h3><ul><li>ლოგო ფირის კადრის მოტივით, მარტივი ვერსია ავატარისა და ბეჭდისთვის</li><li>მონოქრომული პალიტრა ერთი აქცენტით</li><li>ბრენდბუქი და 12 შაბლონი Instagram-ისთვის</li></ul>'
                          . '<h3>შედეგი</h3><ul><li>ერთიანი სტილი საიტზე, სოციალურ ქსელებსა და სავიზიტო ბარათებზე</li><li>კომერციული გადაღებების მოთხოვნები პირველ კვარტალში 40%-ით გაიზარდა</li></ul>',
                          '<h3>Challenge</h3><p>The studio was moving to a new space and wanted to look more professional to commercial clients. The old logo was unreadable at small sizes.</p>'
                          . '<h3>Solution</h3><ul><li>A logo based on a film frame, with a simple version for avatars and stamps</li><li>A monochrome palette with a single accent</li><li>A brand book and 12 Instagram templates</li></ul>'
                          . '<h3>Result</h3><ul><li>One consistent style across the website, social media and business cards</li><li>Commercial shoot enquiries grew 40% in the first quarter</li></ul>'],
        ],
        'event-agency' => [
            'client'  => ['„ზეიმი ლაბი“', 'Zeimi Lab'],
            'excerpt' => ['ღონისძიებების სააგენტოს საიტი ქეისების გალერეით, პაკეტებით და ღონისძიების მოთხოვნის ფორმით.',
                          'A website for an event agency with a case gallery, packages and an event request form.'],
            'body'    => ['<h3>ამოცანა</h3><p>სააგენტოს კლიენტები ერთსა და იმავე კითხვებს სვამდნენ: რა ღირს, რა შედის და რა გაუკეთებიათ მანამდე. მენეჯერები დღეში რამდენიმე საათს ერთი და იმავე ინფორმაციის გაგზავნას ახმარდნენ.</p>'
                          . '<h3>გადაწყვეტა</h3><ul><li>ჩატარებული ღონისძიებების გალერეა ტიპების მიხედვით</li><li>სამი პაკეტი ფასით „-დან“ და შედარების ცხრილით</li><li>მოთხოვნის ფორმა თარიღით, სტუმრების რაოდენობითა და ბიუჯეტით</li></ul>'
                          . '<h3>შედეგი</h3><ul><li>საიტიდან შემოსული მოთხოვნები თვეში 15-დან 48-მდე გაიზარდა</li><li>პირველ ზარამდე კლიენტმა ფასი და პაკეტი უკვე იცის</li></ul>',
                          '<h3>Challenge</h3><p>Clients kept asking the same questions: what it costs, what is included and what the agency has done before. Managers spent hours a day sending the same information.</p>'
                          . '<h3>Solution</h3><ul><li>A gallery of past events grouped by type</li><li>Three packages with "from" prices and a comparison table</li><li>A request form with date, guest count and budget</li></ul>'
                          . '<h3>Result</h3><ul><li>Requests from the site grew from 15 to 48 a month</li><li>Clients know the price and package before the first call</li></ul>'],
        ],
        'cosmetics-brand' => [
            'client'  => ['„მთის ბალახი“', 'Mtis Balakhi'],
            'excerpt' => ['ნატურალური კოსმეტიკის ბრენდის იდენტობა და შეფუთვის დიზაინი 9 პროდუქტისთვის.',
                          'Identity and packaging design for a natural cosmetics brand across 9 products.'],
            'body'    => ['<h3>ამოცანა</h3><p>მცირე მწარმოებელი ქართული მთის მცენარეებისგან ამზადებდა კოსმეტიკას, მაგრამ თაროზე სხვა ბრენდებში იკარგებოდა და სუპერმარკეტების ქსელში შესვლა უნდოდა.</p>'
                          . '<h3>გადაწყვეტა</h3><ul><li>ლოგო და ილუსტრაციები მცენარეების ბოტანიკური ნახატებით</li><li>შეფუთვის სისტემა: ერთი შაბლონი, ფერი — პროდუქტის ხაზის მიხედვით</li><li>ეტიკეტები ქართულად და ინგლისურად</li></ul>'
                          . '<h3>შედეგი</h3><ul><li>ბრენდი ორ სუპერმარკეტის ქსელში შევიდა</li><li>საცალო გაყიდვები 6 თვეში 70%-ით გაიზარდა</li></ul>',
                          '<h3>Challenge</h3><p>A small producer made cosmetics from Georgian mountain herbs but got lost among other brands on the shelf and wanted to get into supermarket chains.</p>'
                          . '<h3>Solution</h3><ul><li>A logo and illustrations based on botanical drawings</li><li>A packaging system: one template, a colour per product line</li><li>Labels in Georgian and English</li></ul>'
                          . '<h3>Result</h3><ul><li>The brand was listed in two supermarket chains</li><li>Retail sales grew 70% in 6 months</li></ul>'],
        ],
        'accessories-store' => [
            'client'  => ['„ბეჭედი და ძაფი“', 'Bechedi & Dzapi'],
            'excerpt' => ['ხელნაკეთი აქსესუარების ონლაინ მაღაზია საჩუქრის შეფუთვით, მიწოდების არჩევით და SEO-თი.',
                          'An online store for handmade accessories with gift wrapping, delivery options and SEO.'],
            'body'    => ['<h3>ამოცანა</h3><p>მაღაზიას მხოლოდ ერთი ფიზიკური წერტილი ჰქონდა და Google-ში მისი პროდუქცია არ ჩანდა. დღესასწაულებზე საჩუქრის შეკვეთები ტელეფონით ვერ ეტეოდა.</p>'
                          . '<h3>გადაწყვეტა</h3><ul><li>მაღაზია კატეგორიებით: ბეჭდები, საყურეები, სამაჯურები</li><li>საჩუქრის შეფუთვა და ბარათი შეკვეთისას</li><li>ტექნიკური SEO და პროდუქტების აღწერები საძიებო ფრაზებით</li></ul>'
                          . '<h3>შედეგი</h3><ul><li>ორგანული ტრაფიკი წელიწადში 4-ჯერ გაიზარდა</li><li>ახალი წლის სეზონზე გაყიდვების 55% ონლაინ მოვიდა</li></ul>',
                          '<h3>Challenge</h3><p>The shop had a single physical location and its products did not show up on Google. Holiday gift orders were more than the phone line could handle.</p>'
                          . '<h3>Solution</h3><ul><li>A store with categories: rings, earrings, bracelets</li><li>Gift wrapping and a card option at checkout</li><li>Technical SEO and product descriptions built around search phrases</li></ul>'
                          . '<h3>Result</h3><ul><li>Organic traffic grew fourfold in a year</li><li>55% of New Year season sales came online</li></ul>'],
        ],
    ];

    /** შეფასებები — slug => ავტორი, პროფესია, ტექსტი [ka, en]; კლიენტები ქეისებს ემთხვევა */
    public const REVIEWS = [
        'review-1' => [
            'title'      => ['გიორგი ლომიძე', 'Giorgi Lomidze'],
            'profession' => ['დამფუძნებელი, „ვერე სთრით“', 'Founder, Vere Street'],
            'quote'      => ['ახალი საიტის გაშვებიდან სამ თვეში ონლაინ შეკვეთები გაორმაგდა. გუნდი ყოველთვის პასუხობს და ესმის, რა გვჭირდება.',
                             'Online orders doubled within three months of launching the new site. The team always responds and understands what we need.'],
        ],
        'review-2' => [
            'title'      => ['ნატა ხურციძე', 'Nata Khurtsidze'],
            'profession' => ['მარკეტინგის მენეჯერი, „მთის ბალახი“', 'Marketing Manager, Mtis Balakhi'],
            'quote'      => ['ბრენდინგი და შეფუთვა ერთ გუნდთან გავაკეთეთ, ამიტომ ყველაფერი ერთ სტილშია. ახალი შეფუთვით სუპერმარკეტების ქსელში შესვლა გაცილებით გაგვიმარტივდა.',
                             'We did the branding and packaging with one team, so everything shares one style. The new packaging made it much easier to get into supermarket chains.'],
        ],
        'review-3' => [
            'title'      => ['ეკა გელაშვილი', 'Eka Gelashvili'],
            'profession' => ['დირექტორი, „თეთრი ქსოვილი“', 'Director, Tetri Ksovili'],
            'quote'      => ['მართვის პანელი იმდენად მარტივია, რომ ახალ კოლექციებს თავად ვამატებთ, დეველოპერის გარეშე.',
                             'The admin panel is so simple that we add new collections ourselves, without a developer.'],
        ],
    ];

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
        $i = 0;
        foreach (self::REVIEWS as $slug => $review) {
            $this->item('testimonials', null, 'testimonial-' . ++$i . '.jpg', $review['title'], [$slug, $slug], [],
                ['quote' => $review['quote'], 'profession' => $review['profession']]);
        }

        // ---- პორტფოლიო ----
        $web = $this->category('portfolio', ['ვებ დიზაინი', 'Web Design'], ['veb-dizaini', 'web-design']);
        $brand = $this->category('portfolio', ['ბრენდინგი', 'Branding'], ['brendingi', 'branding']);
        $shop = $this->category('portfolio', ['ონლაინ მაღაზია', 'E-commerce'], ['onlain-maghazia', 'e-commerce']);
        $projects = [
            [$web, 'portfolio-1.jpg', ['მოდის სახლის საიტი', 'Fashion House Website'], ['modis-sakhlis-saiti', 'fashion-house-website'], 2026],
            [$shop, 'portfolio-2.jpg', ['ტანსაცმლის ონლაინ მაღაზია', 'Apparel Online Store'], ['tansatsmlis-maghazia', 'apparel-store'], 2025],
            [$brand, 'portfolio-3.jpg', ['სტუდიის ბრენდინგი', 'Studio Branding'], ['studiis-brendingi', 'studio-branding'], 2025],
            [$web, 'portfolio-4.jpg', ['ღონისძიებების სააგენტო', 'Event Agency'], ['ghonisdziebebis-saagento', 'event-agency'], 2024],
            [$brand, 'portfolio-5.jpg', ['კოსმეტიკის ბრენდი', 'Cosmetics Brand'], ['kosmetikis-brendi', 'cosmetics-brand'], 2024],
            [$shop, 'portfolio-6.jpg', ['აქსესუარების მაღაზია', 'Accessories Store'], ['aksesuarebis-maghazia', 'accessories-store'], 2023],
        ];
        foreach ($projects as [$category, $image, $title, $slug, $year]) {
            $this->item('portfolio', $category, $image, $title, $slug,
                ['year' => $year, 'website' => ['type' => '', 'page_id' => null, 'url' => '', 'new_tab' => true]], self::CASES[$slug[1]]);
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
