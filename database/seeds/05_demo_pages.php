<?php
declare(strict_types=1);

use App\Models\Page;
use App\Models\PageBlock;
use Core\Database\DB;
use Core\Database\Seeder;

// Poseify-ის 6 გვერდი ბლოკებად (ka + en) დემო სურათებით (04_demo_media).
// გაეშვება მხოლოდ მაშინ, როცა გვერდები ჯერ არ არის. საბოლოო კონტენტი (ვებ სტუდია) — ფაზა 9.
return new class extends Seeder {
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

        $home = $this->page(['მთავარი', 'Home'], ['', ''], 1, true);
        $about = $this->page(['ჩვენ შესახებ', 'About'], ['chven-shesakheb', 'about'], 2);
        $services = $this->page(['სერვისები', 'Services'], ['servisebi', 'services'], 3);
        $team = $this->page(['გუნდი', 'Our Team'], ['gundi', 'team'], 1, false, $about);
        $reviews = $this->page(['შეფასებები', 'Testimonials'], ['shepasebebi', 'testimonials'], 2, false, $about);
        $contact = $this->page(['კონტაქტი', 'Contact'], ['kontakti', 'contact'], 4);

        // მთავარი
        $this->hero($home, $about);
        $this->about($home, $team, $contact);
        $this->services($home, $services);
        $this->banner($home);
        $this->team($home, 8);
        $this->testimonials($home);

        // ჩვენ შესახებ
        $this->header($about);
        $this->about($about, $team, $contact, 'dark');
        $this->banner($about);
        $this->team($about, 4);

        $this->header($services);
        $this->services($services, $contact);
        $this->testimonials($services);

        $this->header($team);
        $this->team($team, 8);

        $this->header($reviews);
        $this->testimonials($reviews, 'dark');

        $this->header($contact);
        $this->block($contact, 'contact', ['show_form' => true], [
            'subtitle' => ['კონტაქტი', 'Contact'],
            'title'    => ['დაგვიკავშირდით', 'Please Contact Us'],
            'text'     => ['<p>გაქვთ კითხვა ან იდეა? მოგვწერეთ ან დაგვირეკეთ — სიამოვნებით გიპასუხებთ.</p>', '<p>Have a question or an idea? Write or call us — we are happy to help.</p>'],
        ]);
        $this->block($contact, 'map', ['embed_url' => self::MAP, 'height' => 450], []);
    }

    private function page(array $titles, array $slugs, int $sort, bool $home = false, ?int $parent = null): int
    {
        return $this->pages->save(null, ['parent_id' => $parent, 'status' => 'published', 'is_home' => $home, 'sort' => $sort, 'og_image_id' => null], [
            'ka' => ['title' => $titles[0], 'slug' => $slugs[0] ?: 'mtavari', 'meta_title' => '', 'meta_description' => ''],
            'en' => ['title' => $titles[1], 'slug' => $slugs[1] ?: 'home', 'meta_title' => '', 'meta_description' => ''],
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
            if (in_array($key, ['image'], true) && is_int($value)) {
                $media[] = $value;
            }
        });
        $this->blocks->save(null, $pageId, $type, $settings, $content, array_values(array_unique($media)));
    }

    /**
     * repeater: $items — [[settings], [field => [ka, en]]]; აბრუნებს [settings სია, ka map, en map]
     * @return array{0: list<array>, 1: array, 2: array}
     */
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

    private function hero(int $pageId, int $linkPage): void
    {
        $slide = fn (string $image) => [['image' => $this->img($image), 'button_link' => $this->link($linkPage)], [
            'subtitle'     => ['მოგესალმებით', 'Welcome'],
            'title'        => ['მოდელების სააგენტო', 'Modeling Agency'],
            'text'         => ['ვქმნით სახეებს, რომლებიც გამოირჩევა — მოდა, რეკლამა, ღონისძიებები.', 'We create faces that stand out — fashion, advertising and events.'],
            'button_label' => ['გაიგე მეტი', 'Explore More'],
        ]];
        [$settings, $ka, $en] = $this->items([$slide('carousel-1.jpg'), $slide('carousel-2.jpg')]);
        $this->block($pageId, 'hero_carousel', ['slides' => $settings], ['slides' => [$ka, $en]]);
    }

    private function about(int $pageId, int $team, int $contact, string $background = 'secondary'): void
    {
        [$items, $ka, $en] = $this->items([
            [[], ['text' => ['პროფესიონალი მოდელები და გამოცდილი გუნდი.', 'Professional models and an experienced team.']]],
            [[], ['text' => ['სრული მომსახურება კასტინგიდან გადაღებამდე.', 'Full service from casting to the final shoot.']]],
            [[], ['text' => ['საერთაშორისო პროექტები და ბრენდები.', 'International projects and brands.']]],
        ]);
        $this->block($pageId, 'about', [
            'items' => $items, 'image' => $this->img('about.png'), 'background' => $background,
            'button1_link' => $this->link($team), 'button2_link' => $this->link($contact),
        ], [
            'subtitle'      => ['ისტორია', 'History'],
            'title'         => ['ჩვენი სააგენტოს შესახებ', 'About Our Agency'],
            'text'          => ['<p>ათ წელზე მეტია ვეხმარებით ბრენდებს და დიზაინერებს, იპოვონ სწორი სახე თავიანთი იდეისთვის. ვმუშაობთ მოდის ჩვენებებზე, სარეკლამო კამპანიებსა და კორპორატიულ ღონისძიებებზე.</p>',
                                '<p>For more than ten years we have helped brands and designers find the right face for their idea. We work on fashion shows, advertising campaigns and corporate events.</p>'],
            'items'         => [$ka, $en],
            'button1_label' => ['გაიცანი გუნდი', 'Meet the Team'],
            'button2_label' => ['დაგვიკავშირდი', 'Contact Us'],
        ]);
    }

    private function services(int $pageId, int $linkPage): void
    {
        $service = fn (string $image, array $title, array $text) => [['image' => $this->img($image), 'button_link' => $this->link($linkPage)],
            ['title' => $title, 'text' => $text, 'button_label' => ['ვრცლად', 'Read More']]];
        [$items, $ka, $en] = $this->items([
            $service('service-1.jpg', ['მოდის ჩვენებები', 'Fashion Shows'], ['მოდელები ჩვენებებისთვის — კასტინგი, რეპეტიციები და ჩვენების დღის მხარდაჭერა.', 'Models for runway shows — casting, rehearsals and show-day support.']),
            $service('service-2.jpg', ['კორპორატიული ღონისძიებები', 'Corporate Events'], ['პრეზენტაციები, გამოფენები და ბრენდის ღონისძიებები — პროფესიონალი წარმომადგენლებით.', 'Launches, exhibitions and brand events with professional hosts.']),
            $service('service-3.jpg', ['კომერციული ფოტოგადაღება', 'Commercial Photo Shoots'], ['სარეკლამო და კატალოგის გადაღებები სტუდიაში ან ლოკაციაზე.', 'Advertising and catalogue shoots in the studio or on location.']),
            $service('service-4.jpg', ['პროფესიული მოდელინგი', 'Professional Modeling'], ['მოდელების მომზადება და პორტფოლიოს შექმნა.', 'Model training and portfolio development.']),
        ]);
        $this->block($pageId, 'services', ['items' => $items], [
            'subtitle' => ['სერვისები', 'Services'],
            'title'    => ['როგორ დაგეხმარებით', 'How We Help You'],
            'items'    => [$ka, $en],
        ]);
    }

    private function banner(int $pageId): void
    {
        $this->block($pageId, 'banner_signup', ['background' => 'secondary'], [
            'subtitle' => ['კასტინგი', 'Casting'],
            'title'    => ['გსურს გახდე მოდელი?', 'Want to be a Model?'],
            'text'     => ['გამოიწერე სიახლეები და პირველმა შეიტყვე ახალი კასტინგების შესახებ.', 'Subscribe and be the first to hear about new castings.'],
        ]);
    }

    private function team(int $pageId, int $count): void
    {
        $names = [['ნაომი ოლსენი', 'Naomy Olsen'], ['პამელა ტორნი', 'Pamela Torney'], ['ჯოან ირვინი', 'Joanne Irwin'], ['ჯილიან როუ', 'Gillian Rowe']];
        $members = [];
        for ($i = 1; $i <= $count; $i++) {
            $members[] = [['image' => $this->img("team-$i.jpg"), 'link' => $this->link(null)], [
                'name'       => $names[($i - 1) % 4],
                'attributes' => ["ასაკი: 22\nსიმაღლე: 185\nწონა: 55\nმკერდი: 79\nწელი: 59\nთეძო: 89", "Age: 22\nHeight: 185\nWeight: 55\nBust: 79\nWaist: 59\nHips: 89"],
            ]];
        }
        [$items, $ka, $en] = $this->items($members);
        $this->block($pageId, 'team_grid', ['members' => $items], [
            'subtitle' => ['მოდელები', 'Models'],
            'title'    => ['გაიცანი ჩვენი მოდელები', 'Meet Our Models'],
            'members'  => [$ka, $en],
        ]);
    }

    private function testimonials(int $pageId, string $background = 'secondary'): void
    {
        $quote = ['გუნდთან მუშაობა სიამოვნება იყო: ყველაფერი დროულად, პროფესიონალურად და ზუსტად ჩვენი იდეის მიხედვით გაკეთდა.',
                  'Working with the team was a pleasure: everything was on time, professional and exactly as we imagined.'];
        [$items, $ka, $en] = $this->items([
            [['image' => $this->img('testimonial-1.jpg')], ['quote' => $quote, 'name' => ['ჯოან ირვინი', 'Joanne Irwin'], 'profession' => ['დიზაინერი', 'Designer']]],
            [['image' => $this->img('testimonial-2.jpg')], ['quote' => $quote, 'name' => ['ლანა ანდერსონი', 'Lana Anderson'], 'profession' => ['ბრენდ-მენეჯერი', 'Brand Manager']]],
            [['image' => $this->img('testimonial-3.jpg')], ['quote' => $quote, 'name' => ['პამელა ტორნი', 'Pamela Torney'], 'profession' => ['ფოტოგრაფი', 'Photographer']]],
        ]);
        $this->block($pageId, 'testimonials', ['items' => $items, 'background' => $background], [
            'subtitle' => ['შეფასებები', 'Testimonial'],
            'title'    => ['რას ამბობენ კლიენტები', 'Our Clients Say'],
            'items'    => [$ka, $en],
        ]);
    }
};
