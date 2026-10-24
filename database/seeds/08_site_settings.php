<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Seeder;
use Core\Settings\Settings;

// სტუდიის საწყისი პარამეტრები (დემო კონტაქტები). უკვე შევსებულ მნიშვნელობებს არ ეხება.
return new class extends Seeder {
    private const VALUES = [
        'general.tagline'          => ['ka' => 'ციფრული სტუდია — საიტები, ონლაინ მაღაზიები, ბრენდინგი', 'en' => 'Digital studio — websites, online stores, branding'],
        'contact.phone'            => '+995 555 00 00 00',
        'contact.email'            => 'hello@webdev.test',
        'contact.address'          => ['ka' => 'თბილისი, საქართველო', 'en' => 'Tbilisi, Georgia'],
        'contact.working_hours'    => ['ka' => 'ორშ–პარ, 10:00–19:00', 'en' => 'Mon–Fri, 10:00–19:00'],
        'seo.meta_title'           => ['ka' => 'WEBDEV — ვებ დიზაინ სტუდია', 'en' => 'WEBDEV — Web Design Studio'],
        'seo.meta_description'     => ['ka' => 'ვებ დიზაინი, საიტების დამზადება, ონლაინ მაღაზიები, ბრენდინგი და SEO თბილისში.',
                                       'en' => 'Web design, website development, online stores, branding and SEO in Tbilisi.'],
    ];

    public function run(DB $db): void
    {
        $settings = app(Settings::class);
        $values = [];
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
        if ($values !== []) {
            $settings->save($values);
        }
    }
};
