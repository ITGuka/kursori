<?php
declare(strict_types=1);

use App\Models\Category;
use App\Models\Collection;
use App\Models\Menu;
use Core\Database\DB;
use Core\Database\Seeder;

// ვებ სტუდიის კოლექციები: სერვისები, გუნდი, შეფასებები, პორტფოლიო (კატეგორიებით).
// სურათები — 04_demo_media (resources/stock), ჩაანაცვლეთ რეალურით. გაეშვება მხოლოდ მაშინ, როცა კოლექციებში ჯერ არაფერია.
// ქეისები და შეფასებები რეალისტური, მაგრამ გამოგონილია (კლიენტები, ციფრები) — გაშვებამდე ჩაანაცვლეთ ნამდვილით.
return new class extends Seeder {
    /** AI გუნდის განყოფილებები (გუნდის კატეგორიები): გასაღები => [სახელი [ka, en], slug [ka, en]] */
    public const DEPARTMENTS = [
        'management'   => [['მართვა', 'Management'], ['martva', 'management']],
        'architecture' => [['არქიტექტურა და ანალიზი', 'Architecture & Analysis'], ['arqiteqtura', 'architecture']],
        'development'  => [['დეველოპმენტი', 'Development'], ['developmenti', 'development']],
        'quality'      => [['ხარისხის კონტროლი', 'Quality Assurance'], ['khariskhis-kontroli', 'quality-assurance']],
    ];

    /** AI გუნდი: [განყოფილება, slug, აიკონი, აგენტი, როლი [ka, en], პოზიცია [ka, en], მახასიათებლები [ka, en]] */
    public const AI_TEAM = [
        ['management', 'product-owner', 'fa-solid fa-bullseye', 'gemini',
            ['პროდუქტის მფლობელი', 'Product Owner'], ['კვლევა და პრიორიტეტები', 'Research & priorities'],
            ["ძლიერი მხარე: დიდი კონტექსტი\nამოცანა: მოთხოვნები", "Strength: Large context\nTask: Requirements"]],
        ['management', 'tech-lead', 'fa-solid fa-sitemap', 'claude',
            ['ტექ-ლიდი / ორკესტრატორი', 'Tech Lead / Orchestrator'], ['დაგეგმვა და კოორდინაცია', 'Planning & coordination'],
            ["ძლიერი მხარე: ღრმა მსჯელობა\nამოცანა: სამუშაოს განაწილება", "Strength: Deep reasoning\nTask: Assigning work"]],
        ['architecture', 'solution-architect', 'fa-solid fa-compass-drafting', 'claude',
            ['არქიტექტორი', 'Solution Architect'], ['ტექნიკური გეგმა და ADR', 'Technical plan & ADRs'],
            ["ძლიერი მხარე: სისტემური ხედვა\nამოცანა: ამოცანის დაშლა", "Strength: Systems thinking\nTask: Breaking down work"]],
        ['architecture', 'business-analyst', 'fa-solid fa-magnifying-glass-chart', 'gemini',
            ['ბიზნეს-ანალიტიკოსი', 'Business Analyst'], ['კვლევა და სპეციფიკაცია', 'Research & specs'],
            ["ძლიერი მხარე: Google ძიება\nამოცანა: ბაზრის კვლევა", "Strength: Search grounding\nTask: Market research"]],
        ['architecture', 'tech-writer', 'fa-solid fa-book-open', 'gemini',
            ['ტექნიკური მწერალი', 'Tech Writer'], ['დოკუმენტაცია', 'Documentation'],
            ["ძლიერი მხარე: მთელი კოდი კონტექსტში\nამოცანა: README და API", "Strength: Whole codebase in context\nTask: README & API docs"]],
        ['development', 'backend-lead', 'fa-solid fa-server', 'claude',
            ['ბექენდ ლიდი', 'Backend Lead'], ['ლოგიკა, API, ბაზა', 'Logic, API, database'],
            ["ძლიერი მხარე: მრავალფაილიანი ცვლილებები\nამოცანა: მიგრაციები", "Strength: Multi-file changes\nTask: Migrations"]],
        ['development', 'frontend-lead', 'fa-solid fa-code', 'claude',
            ['ფრონტენდ ლიდი', 'Frontend Lead'], ['კომპონენტები და ინტეგრაცია', 'Components & integration'],
            ["ძლიერი მხარე: რეფაქტორინგი\nამოცანა: UI არქიტექტურა", "Strength: Refactoring\nTask: UI architecture"]],
        ['development', 'ui-prototyper', 'fa-solid fa-pen-ruler', 'gemini',
            ['UI პროტოტიპერი', 'UI Prototyper'], ['მაკეტიდან კომპონენტამდე', 'From mockup to component'],
            ["ძლიერი მხარე: მულტიმოდალურობა\nამოცანა: სკრინშოტიდან კოდი", "Strength: Multimodal\nTask: Screenshot to code"]],
        ['development', 'feature-developers', 'fa-solid fa-layer-group', 'codex',
            ['ფიჩერ-დეველოპერები', 'Feature Developers'], ['პარალელური ტიკეტები', 'Parallel tickets'],
            ["ძლიერი მხარე: ღრუბლოვანი sandbox\nამოცანა: ბაგფიქსი და CRUD", "Strength: Async cloud sandbox\nTask: Bug fixes & CRUD"]],
        ['development', 'devops', 'fa-solid fa-gears', 'claude',
            ['DevOps ინჟინერი', 'DevOps Engineer'], ['CI/CD და დეპლოი', 'CI/CD & deploys'],
            ["ძლიერი მხარე: ტერმინალი\nამოცანა: Docker და pipelines", "Strength: Terminal-native\nTask: Docker & pipelines"]],
        ['quality', 'code-reviewer', 'fa-solid fa-code-compare', 'codex',
            ['კოდის რევიუერი', 'Code Reviewer'], ['ჯვარედინი რევიუ', 'Cross-review'],
            ["წესი: ავტორი ≠ რევიუერი\nამოცანა: Claude-ის PR-ები", "Rule: Author ≠ reviewer\nTask: Reviewing Claude PRs"]],
        ['quality', 'qa-unit', 'fa-solid fa-vial', 'codex',
            ['QA: Unit ტესტები', 'QA: Unit Tests'], ['Unit და ინტეგრაციული ტესტები', 'Unit & integration tests'],
            ["ძლიერი მხარე: პარალელური სამუშაო\nამოცანა: ტესტებით დაფარვა", "Strength: Parallel work\nTask: Test coverage"]],
        ['quality', 'qa-e2e', 'fa-solid fa-computer-mouse', 'claude',
            ['QA: E2E ტესტები', 'QA: E2E Tests'], ['მომხმარებლის სცენარები', 'User journeys'],
            ["ძლიერი მხარე: ბრაუზერის მართვა\nამოცანა: რეალური სცენარები", "Strength: Browser automation\nTask: Real user flows"]],
        ['quality', 'visual-qa', 'fa-solid fa-eye', 'gemini',
            ['ვიზუალური QA', 'Visual QA'], ['UI რეგრესიები', 'UI regressions'],
            ["ძლიერი მხარე: სურათის ანალიზი\nამოცანა: მაკეტთან შედარება", "Strength: Image analysis\nTask: Matching the mockup"]],
        ['quality', 'security-auditor', 'fa-solid fa-shield-halved', 'claude',
            ['უსაფრთხოების აუდიტორი', 'Security Auditor'], ['უსაფრთხოების რევიუ', 'Security review'],
            ["ძლიერი მხარე: ღრმა ანალიზი\nამოცანა: მოწყვლადობები", "Strength: Deep analysis\nTask: Vulnerabilities"]],
    ];

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

    /**
     * სერვისის გვერდი — ინგლისური slug => body, ფასი, ვადა, რა შედის, ქეისების კატეგორია (პორტფოლიოს ინგლისური slug), FAQ [ka, en].
     * ფასები და ვადები სადემონსტრაციოა — გაშვებამდე ჩაანაცვლეთ სტუდიის რეალურით.
     */
    public const SERVICE_DETAILS = [
        'web-design' => [
            'body'         => ['<h3>რას გიშველით</h3><p>საიტი ლამაზია, მაგრამ ზარებსა და შეკვეთებს ვერ მოაქვს? ხშირად მიზეზი სტრუქტურაა: ვიზიტორი ვერ პოულობს, რაც სჭირდება, ან არ იცის, შემდეგ რა გააკეთოს. დიზაინს ვიწყებთ იმით, რა უნდა გააკეთოს ვიზიტორმა, და ყოველ ეკრანს ამ მიზანს ვუქვემდებარებთ.</p><h3>როგორ ვმუშაობთ</h3><ol><li>ვსწავლობთ თქვენს მომხმარებელს და კონკურენტებს</li><li>ვაწყობთ გვერდების სტრუქტურას და პროტოტიპს</li><li>ვქმნით დიზაინს კომპიუტერისა და მობილურისთვის</li><li>ვასწორებთ თქვენი შენიშვნებით და გადავცემთ დეველოპმენტს</li></ol>',
                               '<h3>What it solves</h3><p>Is your site good-looking but not bringing calls or orders? Often the cause is structure: visitors can’t find what they need or don’t know what to do next. We start design from what the visitor should do and shape every screen around that goal.</p><h3>How we work</h3><ol><li>We study your customers and competitors</li><li>We build the page structure and a prototype</li><li>We design for desktop and mobile</li><li>We refine it with your feedback and hand it over to development</li></ol>'],
            'price_from'   => ['1 500 ₾-დან', 'From 1,500 GEL'],
            'duration'     => ['3–6 კვირა', '3–6 weeks'],
            'deliverables' => ["UX კვლევა და კონკურენტების ანალიზი\nგვერდების სტრუქტურა და პროტოტიპი\nდიზაინი კომპიუტერისა და მობილურისთვის\nUI კომპონენტების ნაკრები\n2 რაუნდი შესწორება\nდიზაინის ფაილები Figma-ში",
                               "UX research and competitor analysis\nPage structure and prototype\nDesign for desktop and mobile\nUI component kit\n2 rounds of revisions\nDesign files in Figma"],
            'cases'        => 'web-design',
            'faq'          => [
                [['შემიძლია მხოლოდ დიზაინის შეკვეთა, დეველოპმენტის გარეშე?', 'Can I order only the design, without development?'],
                 ['დიახ. მიიღებთ Figma-ს ფაილებს და კომპონენტების ნაკრებს, რომლითაც ნებისმიერი დეველოპერი იმუშავებს.', 'Yes. You get the Figma files and a component kit any developer can work with.']],
                [['რამდენი შესწორება შედის ფასში?', 'How many revisions are included?'],
                 ['ორი რაუნდი თითო ეტაპზე. დამატებით შესწორებებს წინასწარ შევათანხმებთ.', 'Two rounds per stage. Any extra revisions are agreed in advance.']],
                [['რა მჭირდება დასაწყებად?', 'What do I need to get started?'],
                 ['მოკლე ბრიფი: ვინ არის თქვენი მომხმარებელი, რა გინდათ საიტმა გააკეთოს და რომელი საიტები მოგწონთ. დანარჩენს პირველ შეხვედრაზე გავარკვევთ.', 'A short brief: who your customer is, what the site should do and which sites you like. We work out the rest at the first meeting.']],
            ],
        ],
        'web-development' => [
            'body'         => ['<h3>რას გიშველით</h3><p>საიტი, რომლის შესაცვლელად ყოველ ჯერზე დეველოპერი გჭირდებათ, ძვირი და ნელია. ვაწყობთ სწრაფ და უსაფრთხო საიტს მართვის პანელით, რომელშიც გვერდებს, მენიუს, თარგმანებსა და პროდუქტებს თავად ცვლით.</p><h3>როგორ ვმუშაობთ</h3><ol><li>ვთანხმდებით ფუნქციებზე და ინტეგრაციებზე</li><li>ვაწყობთ საიტს დამტკიცებული დიზაინით</li><li>ვამატებთ კონტენტს და ვამოწმებთ ყველა მოწყობილობაზე</li><li>ვუშვებთ, ვაწყობთ ანალიტიკას და გასწავლით პანელის მართვას</li></ol>',
                               '<h3>What it solves</h3><p>A site that needs a developer for every change is slow and expensive. We build a fast, secure site with an admin panel where you edit pages, menus, translations and products yourself.</p><h3>How we work</h3><ol><li>We agree on features and integrations</li><li>We build the site from the approved design</li><li>We add the content and test it on every device</li><li>We launch, set up analytics and show you how to use the panel</li></ol>'],
            'price_from'   => ['2 500 ₾-დან', 'From 2,500 GEL'],
            'duration'     => ['4–10 კვირა', '4–10 weeks'],
            'deliverables' => ["მართვის პანელი ქართულ ენაზე\nმრავალენოვანი საიტი\nმობილურზე მორგება და სწრაფი ჩატვირთვა\nტექნიკური SEO და ანალიტიკა\nონლაინ გადახდა (მაღაზიისთვის)\n1 თვე უფასო მხარდაჭერა",
                               "Admin panel\nMultilingual website\nMobile-ready and fast loading\nTechnical SEO and analytics\nOnline payments (for stores)\n1 month of free support"],
            'cases'        => 'e-commerce',
            'faq'          => [
                [['რა ტექნოლოგიაზე აწყობთ საიტს?', 'What technology do you build on?'],
                 ['საკუთარ PHP ფრეიმვორკზე, რომელიც სწრაფია და ზედმეტ დანამატებს არ საჭიროებს. მართვის პანელი ქართულ ენაზეა.', 'On our own PHP framework, which is fast and needs no extra plugins. The admin panel comes in Georgian.']],
                [['ჰოსტინგი და დომენი ფასში შედის?', 'Are hosting and the domain included?'],
                 ['არა, ისინი თქვენს სახელზე იყიდება. შერჩევასა და გამართვაში დაგეხმარებით.', 'No, they are bought in your name. We help you choose and set them up.']],
                [['რა ხდება გაშვების შემდეგ?', 'What happens after launch?'],
                 ['პირველი თვის მხარდაჭერა უფასოა. შემდეგ შეგიძლიათ აირჩიოთ ყოველთვიური მხარდაჭერის პაკეტი.', 'The first month of support is free. After that you can choose a monthly support plan.']],
            ],
        ],
        'branding' => [
            'body'         => ['<h3>რას გიშველით</h3><p>როცა საიტი, სოციალური ქსელები და შეფუთვა სხვადასხვა სტილშია, ბრენდი არ გამახსოვრდებათ. ვქმნით ვიზუალურ ენას, რომელიც ყველგან ერთნაირად მუშაობს და გუნდს ზუსტ წესებს აძლევს.</p><h3>როგორ ვმუშაობთ</h3><ol><li>ვსაუბრობთ ბრენდის ხასიათზე და აუდიტორიაზე</li><li>ვთავაზობთ 2–3 მიმართულებას</li><li>ვამუშავებთ არჩეულს: ლოგო, ფერები, შრიფტები</li><li>ვაწყობთ ბრენდბუქს და შაბლონებს</li></ol>',
                               '<h3>What it solves</h3><p>When your website, social media and packaging all look different, nobody remembers the brand. We create a visual language that works the same everywhere and gives your team clear rules.</p><h3>How we work</h3><ol><li>We talk about the brand’s personality and audience</li><li>We propose 2–3 directions</li><li>We develop the chosen one: logo, colours, typography</li><li>We put together the brand book and templates</li></ol>'],
            'price_from'   => ['2 000 ₾-დან', 'From 2,000 GEL'],
            'duration'     => ['3–5 კვირა', '3–5 weeks'],
            'deliverables' => ["ლოგო და მისი ვერსიები\nფერების პალიტრა და შრიფტები\nბრენდბუქი (PDF)\nსავიზიტო ბარათი და ბლანკი\nსოციალური ქსელების შაბლონები\nყველა ფაილი ბეჭდვისა და ვებისთვის",
                               "Logo and its variations\nColour palette and typography\nBrand book (PDF)\nBusiness card and letterhead\nSocial media templates\nAll files for print and web"],
            'cases'        => 'branding',
            'faq'          => [
                [['რამდენ ვარიანტს მივიღებ?', 'How many options will I get?'],
                 ['2–3 მიმართულებას. არჩეულს შემდეგ ერთად ვამუშავებთ.', '2–3 directions. We then develop the one you choose together.']],
                [['ლოგოს უფლებები ვის ეკუთვნის?', 'Who owns the rights to the logo?'],
                 ['საბოლოო ანგარიშსწორების შემდეგ ყველა უფლება თქვენ გადმოგეცემათ.', 'All rights pass to you once the final payment is made.']],
                [['შეფუთვის დიზაინსაც აკეთებთ?', 'Do you design packaging too?'],
                 ['დიახ, ცალკე ან ბრენდინგთან ერთად. ფასი პროდუქტების რაოდენობაზეა დამოკიდებული.', 'Yes, separately or together with branding. The price depends on the number of products.']],
            ],
        ],
        'seo-marketing' => [
            'body'         => ['<h3>რას გიშველით</h3><p>კარგ საიტსაც არ მოაქვს კლიენტები, თუ Google-ში არ ჩანს. ვასწორებთ ტექნიკურ შეცდომებს, ვარჩევთ საძიებო ფრაზებს და ვგეგმავთ კონტენტს, რომ ორგანული ტრაფიკი თვიდან თვემდე იზრდებოდეს.</p><h3>როგორ ვმუშაობთ</h3><ol><li>ვატარებთ საიტის ტექნიკურ აუდიტს</li><li>ვარჩევთ საძიებო ფრაზებს და კონკურენტებს</li><li>ვასწორებთ გვერდებს და ვგეგმავთ ახალ კონტენტს</li><li>ყოველთვიურად ვაგზავნით ანგარიშს შედეგებით</li></ol>',
                               '<h3>What it solves</h3><p>Even a good site brings no customers if it doesn’t show up on Google. We fix technical errors, pick the right search phrases and plan content so organic traffic grows month after month.</p><h3>How we work</h3><ol><li>We run a technical audit of the site</li><li>We research search phrases and competitors</li><li>We improve pages and plan new content</li><li>We send a monthly report with results</li></ol>'],
            'price_from'   => ['800 ₾ / თვე-დან', 'From 800 GEL / month'],
            'duration'     => ['მინიმუმ 3 თვე', '3 months minimum'],
            'deliverables' => ["ტექნიკური SEO აუდიტი\nსაძიებო ფრაზების კვლევა\nგვერდების ოპტიმიზაცია\nGoogle Analytics და Search Console\nკონტენტის გეგმა\nყოველთვიური ანგარიში",
                               "Technical SEO audit\nKeyword research\nOn-page optimisation\nGoogle Analytics and Search Console\nContent plan\nMonthly report"],
            'cases'        => 'e-commerce',
            'faq'          => [
                [['როდის გამოჩნდება შედეგი?', 'When will I see results?'],
                 ['პირველი ცვლილებები ჩვეულებრივ 2–3 თვეში ჩანს. სტაბილური ზრდისთვის 6 თვე და მეტი სჭირდება.', 'First changes usually show in 2–3 months. Steady growth takes 6 months or more.']],
                [['გარანტიას იძლევით Google-ის პირველ ადგილზე?', 'Do you guarantee first place on Google?'],
                 ['არა, ამას პატიოსნად ვერავინ დაგპირდებათ. ვპირდებით გამჭვირვალე სამუშაოს და ყოველთვიურ ანგარიშს.', 'No, nobody can honestly promise that. We promise transparent work and a monthly report.']],
                [['სხვის მიერ გაკეთებულ საიტზეც მუშაობთ?', 'Do you work on sites built by others?'],
                 ['დიახ. ვიწყებთ აუდიტით და გეტყვით, რა შეიძლება გამოსწორდეს.', 'Yes. We start with an audit and tell you what can be improved.']],
            ],
        ],
    ];

    /** ბლოგის კატეგორიები: გასაღები => [სახელი [ka, en], slug [ka, en]] */
    public const BLOG_CATEGORIES = [
        'tips'     => [['რჩევები', 'Tips'], ['rchevebi', 'tips']],
        'business' => [['ონლაინ ბიზნესი', 'Online Business'], ['onlain-biznesi', 'online-business']],
    ];

    /** ბლოგის სტატიები — ინგლისური slug => კატეგორია, სურათი, თარიღი, სათაური/slug/excerpt/body/author [ka, en] */
    public const BLOG_POSTS = [
        'what-determines-website-cost' => [
            'category' => 'tips', 'image' => 'service-1.jpg', 'date' => '2026-09-15 10:00:00',
            'title'    => ['რა განსაზღვრავს საიტის დამზადების ფასს', 'What Determines the Cost of a Website'],
            'slug'     => ['ra-gansazghvravs-saitis-pass', 'what-determines-website-cost'],
            'excerpt'  => ['ერთი საიტი 1 500 ₾ ღირს, მეორე — 15 000. ვხსნით, საიდან მოდის სხვაობა და როგორ დაგეგმოთ ბიუჯეტი ზედმეტი ხარჯის გარეშე.',
                           'One website costs 1,500 GEL, another 15,000. We explain where the difference comes from and how to plan a budget without overspending.'],
            'author'   => ['თამარ ჯაფარიძე', 'Tamar Japaridze'],
            'body'     => ['<p>„რა ღირს საიტი?“ — ყველაზე ხშირი კითხვაა, რომელსაც ვიღებთ. პასუხი ყოველთვის ერთია: დამოკიდებულია იმაზე, რა უნდა გააკეთოს საიტმა. ფასს ოთხი რამ განსაზღვრავს.</p>'
                           . '<h2>1. გვერდების რაოდენობა და სტრუქტურა</h2><p>სავიზიტო საიტი 5 გვერდით და კორპორატიული საიტი 30 გვერდით სხვადასხვა მოცულობის სამუშაოა: ყოველ გვერდს სჭირდება სტრუქტურა, დიზაინი და კონტენტი.</p>'
                           . '<h2>2. დიზაინი: შაბლონი თუ უნიკალური</h2><p>მზა შაბლონი იაფია, მაგრამ თქვენი ბრენდი სხვას დაემსგავსება. უნიკალური დიზაინი მეტ დროს მოითხოვს, სამაგიეროდ ზუსტად თქვენს მომხმარებელზეა მორგებული.</p>'
                           . '<h2>3. ფუნქციები</h2><p>ონლაინ გადახდა, მარაგის მართვა, ჯავშნების სისტემა, რამდენიმე ენა — ყოველი ფუნქცია დამატებითი დეველოპმენტია. ჩამოწერეთ, რა გჭირდებათ ახლა და რა შეიძლება დაელოდოს მეორე ეტაპს.</p>'
                           . '<h2>4. კონტენტი</h2><p>ტექსტები და ფოტოები ხშირად ყველაზე დიდი დამაყოვნებელია. თუ კონტენტს თავად მოამზადებთ, ღირებულებაც და ვადაც მცირდება.</p>'
                           . '<h2>როგორ დაგეგმოთ ბიუჯეტი</h2><p>დაიწყეთ მიზნით: რამდენი შეკვეთა ან ზარი გინდათ საიტიდან. ამის მიხედვით განსაზღვრეთ მინიმალური ვერსია, გაუშვით და შემდეგ გააფართოვეთ რეალური მონაცემებით.</p>',
                           '<p>"How much does a website cost?" is the question we hear most. The answer is always the same: it depends on what the site needs to do. Four things set the price.</p>'
                           . '<h2>1. Number of pages and structure</h2><p>A five-page business card site and a thirty-page corporate site are different amounts of work: every page needs structure, design and content.</p>'
                           . '<h2>2. Design: template or custom</h2><p>A ready-made template is cheap, but your brand will look like someone else’s. Custom design takes longer but is built around your customers.</p>'
                           . '<h2>3. Features</h2><p>Online payments, stock management, bookings, several languages — every feature is extra development. List what you need now and what can wait for a second phase.</p>'
                           . '<h2>4. Content</h2><p>Texts and photos are often the biggest source of delay. If you prepare the content yourself, both cost and timeline go down.</p>'
                           . '<h2>How to plan the budget</h2><p>Start with the goal: how many orders or calls you want from the site. Define the smallest version that reaches it, launch it, then grow it with real data.</p>'],
        ],
        'online-store-or-instagram' => [
            'category' => 'business', 'image' => 'portfolio-2.jpg', 'date' => '2026-10-02 10:00:00',
            'title'    => ['ონლაინ მაღაზია თუ Instagram: როდის ღირს საკუთარ საიტზე გადასვლა', 'Online Store or Instagram: When to Move to Your Own Site'],
            'slug'     => ['onlain-maghazia-tu-instagram', 'online-store-or-instagram'],
            'excerpt'  => ['Instagram კარგი დასაწყისია, მაგრამ ზრდასთან ერთად შეკვეთები DM-ში იკარგება. ხუთი ნიშანი, რომ საკუთარი მაღაზიის დროა.',
                           'Instagram is a great start, but as you grow, orders get lost in DMs. Five signs it’s time for your own store.'],
            'author'   => ['ნინო ბერიძე', 'Nino Beridze'],
            'body'     => ['<p>ბევრი ქართული ბრენდი Instagram-ით იწყებს — და სწორადაც. მაგრამ არის მომენტი, როცა სოციალური ქსელი ზრდას აფერხებს. აი, ხუთი ნიშანი, რომ ეს მომენტი დადგა.</p>'
                           . '<h2>1. შეკვეთებს პასუხის მოლოდინში კარგავთ</h2><p>თუ მომხმარებელი ფასსა და ზომას DM-ში კითხულობს და პასუხს საათობით ელოდება, ნაწილი უბრალოდ სხვაგან იყიდის.</p>'
                           . '<h2>2. მარაგს ხელით ითვლით</h2><p>როცა ერთი ნივთი ორჯერ იყიდება ან ცხრილი დღეში რამდენჯერმე უნდა განაახლოთ, დროა სისტემის, რომელიც ამას თავად აკეთებს.</p>'
                           . '<h2>3. Google-ში არ ჩანხართ</h2><p>Instagram-ის პოსტები საძიებო სისტემაში თითქმის არ ჩანს. საკუთარი საიტი ახალ მყიდველებს მოიყვანს, რომლებიც თქვენ ჯერ არ გიცნობენ.</p>'
                           . '<h2>4. ბარათით გადახდა გჭირდებათ</h2><p>გადარიცხვის ქვითრის გამოგზავნა მყიდველისთვის დამატებითი ნაბიჯია. ონლაინ გადახდა შეკვეთას რამდენიმე წამში ასრულებს.</p>'
                           . '<h2>5. ალგორითმზე ხართ დამოკიდებული</h2><p>ერთი ცვლილება ალგორითმში და გაყიდვები ეცემა. საიტი და მომხმარებლების ელ-ფოსტების სია თქვენ გეკუთვნით.</p>'
                           . '<p>Instagram-ის დატოვება საჭირო არ არის: საუკეთესო შედეგს ორივე ერთად იძლევა — სოციალური ქსელი აცნობს ბრენდს, მაღაზია კი ყიდის.</p>',
                           '<p>Many Georgian brands start on Instagram — and rightly so. But there comes a point when social media holds growth back. Here are five signs that point has arrived.</p>'
                           . '<h2>1. You lose orders while customers wait</h2><p>If customers ask about prices and sizes in DMs and wait hours for a reply, some will simply buy elsewhere.</p>'
                           . '<h2>2. You count stock by hand</h2><p>When the same item is sold twice or a spreadsheet needs updating several times a day, it’s time for a system that does it for you.</p>'
                           . '<h2>3. You don’t show up on Google</h2><p>Instagram posts barely appear in search. Your own site brings new buyers who don’t know you yet.</p>'
                           . '<h2>4. You need card payments</h2><p>Sending a bank transfer receipt is an extra step for the buyer. Online payment completes the order in seconds.</p>'
                           . '<h2>5. You depend on the algorithm</h2><p>One algorithm change and sales drop. Your site and your customers’ email list belong to you.</p>'
                           . '<p>You don’t need to leave Instagram: the best results come from both together — social media introduces the brand, the store sells.</p>'],
        ],
        'website-launch-checklist' => [
            'category' => 'tips', 'image' => 'service-2.jpg', 'date' => '2026-10-20 10:00:00',
            'title'    => ['8 რამ, რაც საიტის გაშვებამდე უნდა შეამოწმოთ', '8 Things to Check Before Launching Your Website'],
            'slug'     => ['saitis-gashvebamde-shemotsmeba', 'website-launch-checklist'],
            'excerpt'  => ['მოკლე ჩეკლისტი, რომ გაშვების დღეს არაფერი გამოგრჩეთ: ფორმებიდან და მობილური ვერსიიდან ანალიტიკამდე.',
                           'A short checklist so nothing slips on launch day: from forms and the mobile version to analytics.'],
            'author'   => ['მარიამ კაპანაძე', 'Mariam Kapanadze'],
            'body'     => ['<p>გაშვების დღეს ყველაზე ხშირად წვრილმანები ავიწყდებათ. ეს სია ჩვენს პროექტებზე ვიყენებთ — გამოიყენეთ თქვენც.</p>'
                           . '<ol><li><strong>ფორმები.</strong> გააგზავნეთ თითოეული ფორმა და დარწმუნდით, რომ შეტყობინება სწორ მისამართზე მოდის.</li>'
                           . '<li><strong>მობილური ვერსია.</strong> გახსენით საიტი ტელეფონზე: იკითხება თუ არა ტექსტი, ეტევა თუ არა ღილაკები.</li>'
                           . '<li><strong>სიჩქარე.</strong> შეამოწმეთ PageSpeed Insights-ში; დიდი ფოტოები შეკუმშეთ.</li>'
                           . '<li><strong>სათაურები და აღწერები.</strong> ყოველ გვერდს უნდა ჰქონდეს საკუთარი title და meta description.</li>'
                           . '<li><strong>ბმულები.</strong> გადაამოწმეთ, რომ მენიუ და ღილაკები სწორ გვერდზე მიდის და 404 არსად ჩნდება.</li>'
                           . '<li><strong>ანალიტიკა.</strong> დააყენეთ Google Analytics და Search Console გაშვებამდე, რომ პირველი დღიდან ნახოთ მონაცემები.</li>'
                           . '<li><strong>კონფიდენციალურობის პოლიტიკა.</strong> თუ ფორმებით მონაცემებს აგროვებთ, პოლიტიკა სავალდებულოა.</li>'
                           . '<li><strong>სარეზერვო ასლი.</strong> დარწმუნდით, რომ ჰოსტინგზე ავტომატური backup ჩართულია.</li></ol>'
                           . '<p>ყველაფერი რიგზეა? გაუშვით და პირველი კვირა ყოველდღე ნახეთ ანალიტიკა — ის გეტყვით, რა გასაუმჯობესებელია.</p>',
                           '<p>On launch day it’s the small things that get forgotten. We use this list on our own projects — feel free to use it too.</p>'
                           . '<ol><li><strong>Forms.</strong> Submit every form and make sure the message reaches the right address.</li>'
                           . '<li><strong>Mobile version.</strong> Open the site on a phone: is the text readable, do the buttons fit?</li>'
                           . '<li><strong>Speed.</strong> Check it in PageSpeed Insights and compress large photos.</li>'
                           . '<li><strong>Titles and descriptions.</strong> Every page needs its own title and meta description.</li>'
                           . '<li><strong>Links.</strong> Check that menus and buttons go to the right pages and no 404s appear.</li>'
                           . '<li><strong>Analytics.</strong> Set up Google Analytics and Search Console before launch so you have data from day one.</li>'
                           . '<li><strong>Privacy policy.</strong> If your forms collect personal data, a policy is required.</li>'
                           . '<li><strong>Backups.</strong> Make sure automatic backups are switched on at your host.</li></ol>'
                           . '<p>All good? Launch, and check analytics every day for the first week — it will tell you what to improve.</p>'],
        ],
    ];

    /** ბლოგის კატეგორიები და სტატიები (public — არსებული საიტის ერთჯერადი განახლებისთვისაც); სტატია უკვე არის → არ ეხება */
    public function blog(DB $db): void
    {
        $this->items ??= app(Collection::class);
        $this->categories ??= app(Category::class);
        if ($this->media === []) {
            foreach ($db->select("SELECT id, original_name FROM media WHERE folder = 'demo'") as $row) {
                $this->media[$row['original_name']] = (int)$row['id'];
            }
        }
        if ((int)$db->value("SELECT COUNT(*) FROM collection_items WHERE collection = 'blog'") > 0) {
            return;
        }
        $categories = array_map(fn ($c) => $this->category('blog', $c[0], $c[1]), self::BLOG_CATEGORIES);
        foreach (array_reverse(self::BLOG_POSTS) as $post) {
            $id = $this->item('blog', $categories[$post['category']], $post['image'], $post['title'], $post['slug'], [],
                ['excerpt' => $post['excerpt'], 'body' => $post['body'], 'author' => $post['author']]);
            // სადემონსტრაციო თარიღი (სიაში უახლესი პირველია — შექმნის თარიღით)
            $db->update('collection_items', ['created_at' => $post['date']], 'id = ?', [$id]);
        }
    }

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
                 'Unique, modern design that reflects your brand and guides visitors straight to their goal.']],
            ['service-2.jpg', ['ვებ დეველოპმენტი', 'Web Development'], ['veb-developmenti', 'web-development'],
                ['სწრაფი, უსაფრთხო და მართვადი საიტები და ონლაინ მაღაზიები — მართვის პანელით.',
                 'Fast, secure and manageable websites and online stores — with an admin panel.']],
            ['service-3.jpg', ['ბრენდინგი', 'Branding'], ['brendingi', 'branding'],
                ['ლოგო, ფერები, შრიფტები და ბრენდბუქი — იდენტობა, რომელიც ახსოვთ.',
                 'Logo, colours, typography and a brand book — an identity people remember.']],
            ['service-4.jpg', ['SEO და მარკეტინგი', 'SEO & Marketing'], ['seo-marketingi', 'seo-marketing'],
                ['საიტი, რომელსაც პოულობენ: ტექნიკური SEO, კონტენტი და ანალიტიკა.',
                 'A website people find: technical SEO, content and analytics.']],
        ];
        // ქეისების კატეგორიები სერვისებს სჭირდება, ამიტომ პორტფოლიოს კატეგორიები აქვე იქმნება
        $web = $this->category('portfolio', ['ვებ დიზაინი', 'Web Design'], ['veb-dizaini', 'web-design']);
        $brand = $this->category('portfolio', ['ბრენდინგი', 'Branding'], ['brendingi', 'branding']);
        $shop = $this->category('portfolio', ['ონლაინ მაღაზია', 'E-commerce'], ['onlain-maghazia', 'e-commerce']);
        $caseCategories = ['web-design' => $web, 'branding' => $brand, 'e-commerce' => $shop];
        foreach ($services as [$image, $title, $slug, $excerpt]) {
            $details = self::SERVICE_DETAILS[$slug[1]];
            [$faqSettings, $faqKa, $faqEn] = self::repeater(array_map(fn ($row) => ['question' => $row[0], 'answer' => $row[1]], $details['faq']));
            $this->item('services', null, $image, $title, $slug, ['cases' => $caseCategories[$details['cases']] ?? null, 'faq' => $faqSettings], [
                'excerpt'      => $excerpt,
                'body'         => $details['body'],
                'price_from'   => $details['price_from'],
                'duration'     => $details['duration'],
                'deliverables' => $details['deliverables'],
                'faq'          => [$faqKa, $faqEn],
            ]);
        }

        // ---- გუნდი (AI აგენტები) + გუნდის გვერდის ბლოკები (05_demo_pages ჩანაწერებამდე ეშვება) ----
        (require __DIR__ . '/05_demo_pages.php')->teamPage($db, $this->aiTeam());

        // ---- შეფასებები ----
        $i = 0;
        foreach (self::REVIEWS as $slug => $review) {
            $this->item('testimonials', null, 'testimonial-' . ++$i . '.jpg', $review['title'], [$slug, $slug], [],
                ['quote' => $review['quote'], 'profession' => $review['profession']]);
        }

        // ---- პორტფოლიო (კატეგორიები — სერვისებთან) ----
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

        $this->blog($db);

        // header-ის „სერვისები ▾“ ქვემენიუ (06_menus სერვისებამდე ეშვება)
        (require __DIR__ . '/06_menus.php')::serviceChildren($db, app(Menu::class));
    }

    /**
     * AI გუნდი: განყოფილებები (კატეგორიები) და როლები (public — არსებული საიტის მიგრაციისთვისაც).
     * @return array<string, int> განყოფილების გასაღები => კატეგორიის id
     */
    public function aiTeam(): array
    {
        $this->items ??= app(Collection::class);
        $this->categories ??= app(Category::class);
        $departments = [];
        foreach (self::DEPARTMENTS as $key => [$names, $slugs]) {
            $departments[$key] = $this->category('team', $names, $slugs);
        }
        foreach (self::AI_TEAM as [$department, $slug, $icon, $agent, $name, $position, $attributes]) {
            $this->item('team', $departments[$department], '', $name, [$slug, $slug],
                ['agent' => $agent, 'icon' => $icon, 'link' => ['type' => '', 'page_id' => null, 'url' => '', 'new_tab' => false]],
                ['position' => $position, 'attributes' => $attributes]);
        }
        return $departments;
    }

    /**
     * repeater-ის მონაცემები: [[ველი => [ka, en]], ...] → [settings, ka content, en content]
     * @return array{0: list<array>, 1: array, 2: array}
     */
    public static function repeater(array $rows): array
    {
        $settings = $ka = $en = [];
        foreach ($rows as $row) {
            $id = bin2hex(random_bytes(5));
            $settings[] = ['_id' => $id];
            foreach ($row as $field => [$k, $e]) {
                $ka[$id][$field] = $k;
                $en[$id][$field] = $e;
            }
        }
        return [$settings, $ka, $en];
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
    private function item(string $type, ?int $category, string $image, array $title, array $slug, array $settings, array $translated): int
    {
        $content = ['ka' => [], 'en' => []];
        foreach ($translated as $field => [$ka, $en]) {
            $content['ka'][$field] = $ka;
            $content['en'][$field] = $en;
        }
        return $this->items->save(null, $type, ['category_id' => $category, 'image_id' => $this->media[$image] ?? null, 'is_active' => true], $settings, [
            'ka' => ['title' => $title[0], 'slug' => $slug[0]],
            'en' => ['title' => $title[1], 'slug' => $slug[1]],
        ], $content, []);
    }
};
