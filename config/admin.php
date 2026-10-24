<?php
declare(strict_types=1);

// ადმინის sidebar. route — route-ის სახელი (+ params), active — რომელ route-ებზე მოინიშნოს (is_route pattern),
// collection — კოლექციის პუნქტი აქტიურია, როცა view-ს $collectionType ემთხვევა.
// კოლექციები (config/collections.php) ავტომატურად ემატება გვერდების შემდეგ.
$collections = [];
foreach (require __DIR__ . '/collections.php' as $type => $definition) {
    $collections[] = [
        'label'      => $definition['label'],
        'icon'       => $definition['icon'] ?? 'fa fa-th-list',
        'route'      => 'admin.collections.index',
        'params'     => ['type' => $type],
        'collection' => $type,
    ];
}

return [
    'menu' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-tachometer-alt', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'გვერდები', 'icon' => 'fa fa-file-alt', 'route' => 'admin.pages.index', 'active' => ['admin.pages.*', 'admin.blocks.*']],
        ...$collections,
        ['label' => 'მენიუები', 'icon' => 'fa fa-bars', 'route' => 'admin.menus.index', 'active' => 'admin.menus.*'],
        ['label' => 'მედია', 'icon' => 'fa fa-images', 'route' => 'admin.media.index', 'active' => 'admin.media.*'],
        ['label' => 'თარგმანები', 'icon' => 'fa fa-language', 'route' => 'admin.translations.index', 'active' => 'admin.translations.*'],
        ['label' => 'ენები', 'icon' => 'fa fa-globe', 'route' => 'admin.languages.index', 'active' => 'admin.languages.*'],
        ['label' => 'პარამეტრები', 'icon' => 'fa fa-cog', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
        ['label' => 'მომხმარებლები', 'icon' => 'fa fa-users', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
    ],
];
