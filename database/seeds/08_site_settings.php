<?php
declare(strict_types=1);

use App\Models\Page;
use Core\Database\DB;
use Core\Database\Seeder;
use Core\Settings\Settings;

// სტუდიის საწყისი პარამეტრები (რეალისტური დემო კონტაქტები — გაშვებამდე ჩაანაცვლეთ). უკვე შევსებულ მნიშვნელობებს არ ეხება.
return new class extends Seeder {
    private const VALUES = [
        'general.tagline'          => ['ka' => 'ციფრული სტუდია — საიტები, ონლაინ მაღაზიები, ბრენდინგი', 'en' => 'Digital studio — websites, online stores, branding'],
        'contact.phone'            => '+995 32 247 18 90',
        'contact.email'            => 'hello@kursori.dev',
        'contact.address'          => ['ka' => 'ვაჟა-ფშაველას გამზ. 71, თბილისი 0186', 'en' => '71 Vazha-Pshavela Ave, Tbilisi 0186'],
        'contact.working_hours'    => ['ka' => 'ორშ–პარ, 10:00–19:00', 'en' => 'Mon–Fri, 10:00–19:00'],
        'contact.working_hours_ai' => ['ka' => '24/7, დღე და ღამე', 'en' => '24/7, day and night'],
        'seo.meta_title'           => ['ka' => 'KURSORI — ვებ დიზაინ სტუდია', 'en' => 'KURSORI — Web Design Studio'],
        'seo.meta_description'     => ['ka' => 'ვებ დიზაინი, საიტების დამზადება, ონლაინ მაღაზიები, ბრენდინგი და SEO თბილისში.',
                                       'en' => 'Web design, website development, online stores, branding and SEO in Tbilisi.'],
    ];

    /** სურათის პარამეტრები — 04_demo_media-ს ფაილები (resources/stock). favicon არ ივსება: ცარიელზე KURSORI-ის ხატულა ჩანს (layouts/main.php) */
    private const IMAGES = [
        'seo.og_image'    => 'carousel-1.jpg',
    ];

    public function run(DB $db): void
    {
        $settings = app(Settings::class);
        $values = [];
        foreach (self::IMAGES as $key => $file) {
            $id = $db->value("SELECT id FROM media WHERE folder = 'demo' AND original_name = ?", [$file]);
            if ($id !== null && $settings->raw($key) === '') {
                $values[$key] = (string)$id;
            }
        }
        foreach (self::VALUES as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $lang => $text) {
                    if ($settings->raw($key, $lang) === '') {
                        $values[$key][$lang] = $text;
                    }
                }
            } elseif ($settings->raw($key) === '') {
                $values[$key] = $value;
            }
        }
        // მენიუს ღილაკი → კონტაქტის გვერდი (05_demo_pages)
        $contact = $db->value("SELECT page_id FROM page_translations WHERE lang = 'en' AND slug = 'contact'");
        $current = (int)$settings->raw('general.cta_page');
        if ($contact !== null && ($current === 0 || app(Page::class)->find($current) === null)) {
            $values['general.cta_page'] = (string)$contact;
        }
        if ($values !== []) {
            $settings->save($values);
        }
    }
};
