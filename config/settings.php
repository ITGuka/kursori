<?php
declare(strict_types=1);

/*
 * საიტის პარამეტრების განსაზღვრებები. ადმინის ფორმა (ჩანართები ჯგუფებად) აქედან გენერირდება,
 * მნიშვნელობები ინახება `settings` ცხრილში. წაკითხვა: setting('contact.phone').
 *
 * ველი: type     — text | textarea | email | url | tel | number | toggle | select | image (მედიის id) | code
 *       label    — წარწერა ადმინში
 *       translatable — თითო ენაზე ცალკე მნიშვნელობა
 *       default  — სტრიქონი ან ['ka' => ..., 'en' => ...]
 *       help     — მინიშნება ველის ქვეშ
 *       options  — select-ისთვის [value => label]
 *       max      — მაქსიმალური სიგრძე (default: 255; textarea/code — 65000)
 */
return [
    'general' => [
        'label'  => 'ზოგადი',
        'fields' => [
            'site_name'        => ['label' => 'საიტის სახელი', 'translatable' => true, 'default' => 'WEBDEV', 'max' => 100],
            'tagline'          => ['label' => 'სლოგანი', 'translatable' => true, 'max' => 150],
            'logo'             => ['label' => 'ლოგო', 'type' => 'image', 'help' => 'ცარიელის შემთხვევაში ჩანს საიტის სახელი.'],
            'favicon'          => ['label' => 'Favicon', 'type' => 'image', 'help' => 'კვადრატული PNG, მინიმუმ 48×48.'],
            'copyright'        => ['label' => 'Copyright ტექსტი', 'translatable' => true, 'help' => ':year ჩანაცვლდება მიმდინარე წლით.',
                                   'default' => ['ka' => '© :year WEBDEV. ყველა უფლება დაცულია.', 'en' => '© :year WEBDEV. All rights reserved.']],
            'maintenance_mode' => ['label' => 'ტექნიკური სამუშაოების რეჟიმი', 'type' => 'toggle', 'default' => false,
                                   'help' => 'ჩართვისას საიტი ჩანს მხოლოდ ადმინში შესულ მომხმარებლებს.'],
            'maintenance_text' => ['label' => 'ტექნიკური სამუშაოების ტექსტი', 'type' => 'textarea', 'translatable' => true,
                                   'default' => ['ka' => 'საიტზე მიმდინარეობს ტექნიკური სამუშაოები. გთხოვთ, შემოგვიაროთ მოგვიანებით.',
                                                 'en' => 'The site is under maintenance. Please check back soon.']],
        ],
    ],

    'contact' => [
        'label'  => 'კონტაქტი',
        'fields' => [
            'phone'         => ['label' => 'ტელეფონი', 'type' => 'tel', 'max' => 50],
            'email'         => ['label' => 'ელ-ფოსტა', 'type' => 'email', 'max' => 190],
            'address'       => ['label' => 'მისამართი', 'translatable' => true],
            'working_hours' => ['label' => 'სამუშაო საათები', 'translatable' => true],
            'map_embed_url' => ['label' => 'Google Maps embed ბმული', 'type' => 'url', 'max' => 2000,
                                'help' => 'Google Maps → Share → Embed a map → iframe-ის src მისამართი.'],
        ],
    ],

    'social' => [
        'label'  => 'სოციალური ქსელები',
        'fields' => [
            'facebook'  => ['label' => 'Facebook', 'type' => 'url'],
            'instagram' => ['label' => 'Instagram', 'type' => 'url'],
            'x'         => ['label' => 'X (Twitter)', 'type' => 'url'],
            'linkedin'  => ['label' => 'LinkedIn', 'type' => 'url'],
            'youtube'   => ['label' => 'YouTube', 'type' => 'url'],
            'behance'   => ['label' => 'Behance', 'type' => 'url'],
            'dribbble'  => ['label' => 'Dribbble', 'type' => 'url'],
        ],
    ],

    'seo' => [
        'label'  => 'SEO',
        'fields' => [
            'meta_title'       => ['label' => 'Meta title (default)', 'translatable' => true, 'max' => 70,
                                   'help' => 'გამოიყენება, თუ გვერდს საკუთარი არ აქვს.'],
            'meta_description' => ['label' => 'Meta description (default)', 'type' => 'textarea', 'translatable' => true, 'max' => 300],
            'og_image'         => ['label' => 'Open Graph სურათი (default)', 'type' => 'image'],
            'indexing'         => ['label' => 'საძიებო სისტემებისთვის ღია', 'type' => 'toggle', 'default' => true,
                                   'help' => 'გამორთვისას ყველა გვერდს ემატება noindex.'],
        ],
    ],

    'scripts' => [
        'label'  => 'კოდი',
        'fields' => [
            'head_code' => ['label' => '<head>-ში ჩასასმელი კოდი', 'type' => 'code',
                            'help' => 'მაგ. Google Analytics. ჩაისმება ისე, როგორც არის — მხოლოდ სანდო კოდი.'],
            'body_code' => ['label' => '</body>-მდე ჩასასმელი კოდი', 'type' => 'code'],
        ],
    ],
];
