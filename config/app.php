<?php
declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'WEBDEV'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool)env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),

    // საიტის დრო — თბილისი; საქართველოში ზაფხულის დრო არ არის (MySQL-ისთვის იხ. database.timezone)
    'timezone' => 'Asia/Tbilisi',

    // ენები იმართება ადმინიდან (languages ცხრილი); ეს მხოლოდ სარეზერვოა — ენის route-ის გარეთ
    // (მაგ. შეცდომის გვერდზე /admin-ში) და სანამ ბაზაში ენა არ არის
    'default_locale' => 'ka',

    'session' => [
        'name' => 'webdev_session',
    ],
];
