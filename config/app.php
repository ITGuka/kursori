<?php
declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'KURSORI'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool)env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),

    // საიტის დრო — თბილისი; საქართველოში ზაფხულის დრო არ არის (MySQL-ისთვის იხ. database.timezone)
    'timezone' => 'Asia/Tbilisi',

    // ენები იმართება ადმინიდან (languages ცხრილი); ეს მხოლოდ სარეზერვოა — ენის route-ის გარეთ
    // (მაგ. შეცდომის გვერდზე /admin-ში) და სანამ ბაზაში ენა არ არის
    'default_locale' => 'ka',

    // CSRF-ის გარეშე: ფოსტის კლიენტის „ერთი დაწკაპებით გაუქმება“ (RFC 8058) — დაცვა URL-ის საიდუმლო token-ით
    'csrf_except' => ['newsletter.unsubscribe.confirm'],

    // ფრონტის გვერდების HTML ქეში სტუმრებისთვის (App\Middleware\PageCache); ttl — წამები
    'page_cache' => [
        'enabled' => (bool)env('PAGE_CACHE', true),
        'ttl'     => 86400,
    ],

    'session' => [
        'name' => 'kursori_session',
    ],
];
