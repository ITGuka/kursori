<?php
declare(strict_types=1);

/*
 * საიტის პარამეტრების განსაზღვრებები. ადმინის ფორმა (ჩანართები ჯგუფებად) აქედან გენერირდება,
 * მნიშვნელობები ინახება `settings` ცხრილში. წაკითხვა: setting('contact.phone').
 *
 * ველი: type     — text | textarea | email | url | tel | number | toggle | select | image (მედიის id) | page (გვერდის id) | code
 *                   | password (ინახება დაშიფრულად — Core\Support\Crypt, ფორმაში არ ჩანს)
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
            'site_name'        => ['label' => 'საიტის სახელი', 'translatable' => true, 'default' => 'KURSORI', 'max' => 100],
            'tagline'          => ['label' => 'სლოგანი', 'translatable' => true, 'max' => 150],
            'cta_label'        => ['label' => 'ჰედერის ღილაკის ტექსტი („მოითხოვეთ შეთავაზება“)', 'translatable' => true, 'max' => 40,
                                   'help' => 'ჰედერში, მენიუს მარჯვნივ ვარდისფერი ღილაკის წარწერა — თითო ენაზე აქ იცვლება (თარგმანებში არ არის).',
                                   'default' => ['ka' => 'მოითხოვეთ შეთავაზება', 'en' => 'Request a Proposal']],
            'cta_page'         => ['label' => 'მენიუს ღილაკის გვერდი', 'type' => 'page',
                                   'help' => 'მენიუს მარჯვნივ გამოკვეთილი ღილაკი (მაგ. კონტაქტი). ცარიელი — ღილაკი არ ჩანს.'],
            'logo'             => ['label' => 'ლოგო', 'type' => 'image', 'help' => 'ცარიელის შემთხვევაში ჩანს საიტის სახელი.'],
            'favicon'          => ['label' => 'Favicon', 'type' => 'image', 'help' => 'კვადრატული PNG, მინიმუმ 48×48.'],
            'copyright'        => ['label' => 'Copyright ტექსტი', 'translatable' => true, 'help' => ':year ჩანაცვლდება მიმდინარე წლით.',
                                   'default' => ['ka' => '© :year KURSORI. ყველა უფლება დაცულია.', 'en' => '© :year KURSORI. All rights reserved.']],
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
                                   'help' => 'გამორთვისას ყველა გვერდს ემატება noindex, robots.txt — Disallow: /, sitemap ცარიელია.'],
            'google_verification' => ['label' => 'Google Search Console — ვერიფიკაციის კოდი', 'max' => 100,
                                      'help' => 'Search Console → HTML tag მეთოდი: მხოლოდ content="..." მნიშვნელობა.'],
            'robots_extra'     => ['label' => 'robots.txt — დამატებითი წესები', 'type' => 'code', 'max' => 5000,
                                   'help' => 'მაგ. „Disallow: /ka/drafts“. Disallow: /admin და Sitemap ავტომატურად ემატება.'],
        ],
    ],

    'legal' => [
        'label'  => 'სამართლებრივი',
        'fields' => [
            'privacy_page' => ['label' => 'კონფიდენციალურობის პოლიტიკის გვერდი', 'type' => 'page',
                               'help' => 'ფორმებისა და გამოწერის ქვეშ ჩნდება თანხმობის ტექსტი ამ გვერდის ბმულით. ცარიელი — ტექსტი არ ჩანს.'],
        ],
    ],

    'orders' => [
        'label'  => 'შეკვეთები',
        'fields' => [
            'notify_email' => ['label' => 'ახალი შეკვეთის შეტყობინების ელ-ფოსტა', 'type' => 'email', 'max' => 190,
                               'help' => 'საიტის შეკვეთის ფორმიდან ყოველი შეკვეთა Inbox-ში ინახება და ამ მისამართზეც მოდის. ცარიელი — კონტაქტის ელ-ფოსტა.'],
        ],
    ],

    'mail' => [
        'label'  => 'ელ-ფოსტა (SMTP)',
        'fields' => [
            'host'       => ['label' => 'SMTP სერვერი', 'default' => '127.0.0.1', 'max' => 190, 'help' => 'ლოკალურად — Mailpit: 127.0.0.1, პორტი 1025, დაშიფვრის გარეშე. წერილები: http://127.0.0.1:8025'],
            'port'       => ['label' => 'პორტი', 'type' => 'number', 'default' => '1025'],
            'encryption' => ['label' => 'დაშიფვრა', 'type' => 'select', 'default' => '', 'options' => ['' => 'არ არის', 'tls' => 'TLS (STARTTLS, 587)', 'ssl' => 'SSL (465)']],
            'username'   => ['label' => 'მომხმარებელი', 'max' => 190],
            'password'   => ['label' => 'პაროლი', 'type' => 'password', 'help' => 'ინახება დაშიფრულად. ცარიელი ველი — ძველი პაროლი რჩება.'],
            'from_email' => ['label' => 'გამგზავნის ელ-ფოსტა', 'type' => 'email', 'default' => 'no-reply@kursori.test', 'max' => 190],
            'from_name'  => ['label' => 'გამგზავნის სახელი', 'default' => 'KURSORI', 'max' => 100],
        ],
    ],

    'news' => [
        'label'  => 'სიახლეები (dev.to)',
        'fields' => [
            'devto_api_key' => ['label' => 'dev.to API გასაღები', 'type' => 'password',
                                'help' => 'არასავალდებულო — გასაღებით მოთხოვნების ლიმიტი მაღალია. dev.to → Settings → Extensions → DEV Community API Keys. ინახება დაშიფრულად.'],
            'daily_limit'   => ['label' => 'დღეში მაქსიმუმ გამოქვეყნება', 'type' => 'number', 'default' => '10',
                                'help' => 'ავტომატური და დამტკიცებული სიახლეები ბლოგში დღეში არაუმეტეს ამდენი. 0 — შეზღუდვის გარეშე.'],
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
