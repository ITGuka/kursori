<?php
declare(strict_types=1);

// ადმინის sidebar. route — route-ის სახელი, active — რომელ route-ებზე მოინიშნოს (is_route pattern).
// ყოველი ახალი მოდული (ფაზა 2+) აქ ემატება.
return [
    'menu' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-tachometer-alt', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'გვერდები', 'icon' => 'fa fa-file-alt', 'route' => 'admin.pages.index', 'active' => ['admin.pages.*', 'admin.blocks.*']],
        ['label' => 'მედია', 'icon' => 'fa fa-images', 'route' => 'admin.media.index', 'active' => 'admin.media.*'],
        ['label' => 'თარგმანები', 'icon' => 'fa fa-language', 'route' => 'admin.translations.index', 'active' => 'admin.translations.*'],
        ['label' => 'ენები', 'icon' => 'fa fa-globe', 'route' => 'admin.languages.index', 'active' => 'admin.languages.*'],
        ['label' => 'პარამეტრები', 'icon' => 'fa fa-cog', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
        ['label' => 'მომხმარებლები', 'icon' => 'fa fa-users', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
    ],
];
