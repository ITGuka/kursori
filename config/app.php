<?php
declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'WEBDEV'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool)env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),

    // საიტის დრო — თბილისი; საქართველოში ზაფხულის დრო არ არის (MySQL-ისთვის იხ. database.timezone)
    'timezone' => 'Asia/Tbilisi',

    // ფაზა 2-მდე ენები აქედან იკითხება, შემდეგ — languages ცხრილიდან
    'locales'        => ['ka', 'en'],
    'default_locale' => 'ka',

    'session' => [
        'name' => 'webdev_session',
    ],
];
