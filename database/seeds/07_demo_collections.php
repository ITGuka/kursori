<?php
declare(strict_types=1);

use App\Models\Category;
use App\Models\Collection;
use Core\Database\DB;
use Core\Database\Seeder;

// დემო კოლექციები (Poseify-ის კონტენტი) და დემო გვერდების სერვისების/გუნდის/შეფასებების ბლოკები → „კოლექციიდან“.
// გაეშვება მხოლოდ მაშინ, როცა კოლექციებში ჯერ არაფერია.
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

        // სერვისები
        $services = [
            ['service-1.jpg', ['მოდის ჩვენებები', 'Fashion Shows'], ['moda', 'fashion-shows'],
                ['მოდელები ჩვენებებისთვის — კასტინგი, რეპეტიციები და ჩვენების დღის მხარდაჭერა.', 'Models for runway shows — casting, rehearsals and show-day support.']],
            ['service-2.jpg', ['კორპორატიული ღონისძიებები', 'Corporate Events'], ['korporatiuli', 'corporate-events'],
                ['პრეზენტაციები, გამოფენები და ბრენდის ღონისძიებები — პროფესიონალი წარმომადგენლებით.', 'Launches, exhibitions and brand events with professional hosts.']],
            ['service-3.jpg', ['კომერციული ფოტოგადაღება', 'Commercial Photo Shoots'], ['potogadagheba', 'photo-shoots'],
                ['სარეკლამო და კატალოგის გადაღებები სტუდიაში ან ლოკაციაზე.', 'Advertising and catalogue shoots in the studio or on location.']],
            ['service-4.jpg', ['პროფესიული მოდელინგი', 'Professional Modeling'], ['modelingi', 'modeling'],
                ['მოდელების მომზადება და პორტფოლიოს შექმნა.', 'Model training and portfolio development.']],
        ];
        foreach ($services as [$image, $title, $slug, $excerpt]) {
            $this->item('services', null, $image, $title, $slug, [], [
                'excerpt' => $excerpt,
                'body'    => ['<p>' . $excerpt[0] . '</p><p>დაგვიკავშირდით დეტალებისთვის — მოვამზადებთ შეთავაზებას თქვენს პროექტზე.</p>',
                              '<p>' . $excerpt[1] . '</p><p>Contact us for details — we will prepare an offer for your project.</p>'],
            ]);
        }

        // გუნდი
        $models = $this->category('team', ['მოდელები', 'Models'], ['modelebi', 'models']);
        $names = [['ნაომი ოლსენი', 'Naomy Olsen'], ['პამელა ტორნი', 'Pamela Torney'], ['ჯოან ირვინი', 'Joanne Irwin'], ['ჯილიან როუ', 'Gillian Rowe']];
        for ($i = 1; $i <= 8; $i++) {
            $this->item('team', $models, "team-$i.jpg", $names[($i - 1) % 4], ['', ''], ['link' => ['type' => '', 'page_id' => null, 'url' => '', 'new_tab' => false]], [
                'position'   => ['მოდელი', 'Model'],
                'attributes' => ["ასაკი: 22\nსიმაღლე: 185\nწონა: 55", "Age: 22\nHeight: 185\nWeight: 55"],
            ]);
        }

        // შეფასებები
        $quote = ['გუნდთან მუშაობა სიამოვნება იყო: ყველაფერი დროულად, პროფესიონალურად და ზუსტად ჩვენი იდეის მიხედვით გაკეთდა.',
                  'Working with the team was a pleasure: everything was on time, professional and exactly as we imagined.'];
        foreach ([['testimonial-1.jpg', ['ჯოან ირვინი', 'Joanne Irwin'], ['დიზაინერი', 'Designer']],
                  ['testimonial-2.jpg', ['ლანა ანდერსონი', 'Lana Anderson'], ['ბრენდ-მენეჯერი', 'Brand Manager']],
                  ['testimonial-3.jpg', ['პამელა ტორნი', 'Pamela Torney'], ['ფოტოგრაფი', 'Photographer']]] as [$image, $name, $profession]) {
            $this->item('testimonials', null, $image, $name, ['', ''], [], ['quote' => $quote, 'profession' => $profession]);
        }

        // პორტფოლიო (საბოლოო კონტენტისთვის — ვებ სტუდია)
        $web = $this->category('portfolio', ['ვებ დიზაინი', 'Web Design'], ['veb-dizaini', 'web-design']);
        $brand = $this->category('portfolio', ['ბრენდინგი', 'Branding'], ['brendingi', 'branding']);
        foreach ([[$web, 'carousel-1.jpg', ['მოდის სახლის საიტი', 'Fashion House Website'], ['modis-sakhlis-saiti', 'fashion-house-website'], 2025],
                  [$brand, 'carousel-2.jpg', ['კოლექციის ბრენდინგი', 'Collection Branding'], ['kolektsiis-brendingi', 'collection-branding'], 2026]] as [$category, $image, $title, $slug, $year]) {
            $this->item('portfolio', $category, $image, $title, $slug,
                ['year' => $year, 'website' => ['type' => 'url', 'page_id' => null, 'url' => 'https://example.com', 'new_tab' => true]], [
                'client'  => ['დემო კლიენტი', 'Demo Client'],
                'excerpt' => ['დემო პროექტი — ჩანაცვლდება სტუდიის რეალური ნამუშევრით.', 'Demo project — will be replaced with the studio’s real work.'],
                'body'    => ['<p>პროექტის აღწერა: ამოცანა, გადაწყვეტა და შედეგი.</p>', '<p>Project description: the task, the solution and the result.</p>'],
            ]);
        }

        // დემო გვერდების ბლოკები — კოლექციიდან (ხელით შეყვანილი ელემენტები ინახება, წყაროს დაბრუნებისთვის)
        $types = ['services' => ['source' => 'collection', 'category' => null, 'limit' => 0],
                  'team_grid' => ['source' => 'collection', 'category' => $models, 'limit' => 0],
                  'testimonials' => ['source' => 'collection', 'limit' => 0]];
        foreach ($db->select("SELECT id, type, settings FROM page_blocks WHERE type IN ('services', 'team_grid', 'testimonials')") as $block) {
            $settings = (array)json_decode((string)$block['settings'], true);
            $extra = $types[$block['type']];
            if ($block['type'] === 'team_grid') {
                // მთავარ/შესახებ გვერდზე ძველი ბლოკი 4 ან 8 წევრს აჩვენებდა
                $extra['limit'] = count($settings['members'] ?? []) <= 4 ? 4 : 0;
            }
            $db->update('page_blocks', ['settings' => json_encode($extra + $settings, JSON_UNESCAPED_UNICODE)], 'id = ?', [$block['id']]);
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
        static $counter = 0;
        $counter++;
        $content = ['ka' => [], 'en' => []];
        foreach ($translated as $field => [$ka, $en]) {
            $content['ka'][$field] = $ka;
            $content['en'][$field] = $en;
        }
        $this->items->save(null, $type, ['category_id' => $category, 'image_id' => $this->media[$image] ?? null, 'is_active' => true], $settings, [
            'ka' => ['title' => $title[0], 'slug' => $slug[0] ?: "item-$counter"],
            'en' => ['title' => $title[1], 'slug' => $slug[1] ?: "item-$counter"],
        ], $content, []);
    }
};
