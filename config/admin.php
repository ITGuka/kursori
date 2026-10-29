<?php
declare(strict_types=1);

// ადმინის sidebar. header — ჯგუფის სათაური; route — route-ის სახელი (+ params), active — რომელ route-ებზე მოინიშნოს
// (is_route pattern), collection — კოლექციის პუნქტი აქტიურია, როცა view-ს $collectionType ემთხვევა;
// badge — მრიცხველი (inbox — წაუკითხავი). icon — Boxicons (`bx bx-*`).
// კოლექციები (config/collections.php) ავტომატურად ემატება გვერდების შემდეგ.
$collections = [];
foreach (require __DIR__ . '/collections.php' as $type => $definition) {
    $collections[] = [
        'label'      => $definition['label'],
        'icon'       => $definition['icon'] ?? 'bx bx-collection',
        'route'      => 'admin.collections.index',
        'params'     => ['type' => $type],
        'collection' => $type,
    ];
}

return [
    'menu' => [
        ['label' => 'Dashboard', 'icon' => 'bx bx-home-smile', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],

        ['header' => 'კონტენტი'],
        ['label' => 'გვერდები', 'icon' => 'bx bx-file', 'route' => 'admin.pages.index', 'active' => ['admin.pages.*', 'admin.blocks.*']],
        ...$collections,
        // სიახლეები dev.to-დან (D57)
        ['label' => 'სიახლეები', 'icon' => 'bx bx-rss', 'route' => 'admin.news.index', 'active' => 'admin.news.*'],
        // დიზაინები (შეკვეთის ფორმა); collection — აქტიურია დიზაინებისა და მათი კატეგორიების გვერდებზე
        ['label' => 'დიზაინები', 'icon' => 'bx bx-palette', 'route' => 'admin.designs.index', 'collection' => 'designs'],
        // ფასების პაკეტები = დიზაინების ზედა დონის კატეგორიები (ფასი, „რა შედის“)
        ['label' => 'პაკეტები', 'icon' => 'bx bx-package', 'route' => 'admin.packages.index', 'active' => 'admin.packages.*'],
        ['label' => 'მენიუები', 'icon' => 'bx bx-menu', 'route' => 'admin.menus.index', 'active' => 'admin.menus.*'],
        ['label' => 'მედია', 'icon' => 'bx bx-images', 'route' => 'admin.media.index', 'active' => 'admin.media.*'],

        ['header' => 'კომუნიკაცია'],
        ['label' => 'Inbox', 'icon' => 'bx bx-envelope', 'route' => 'admin.inbox.index', 'active' => 'admin.inbox.*', 'badge' => 'inbox'],
        ['label' => 'Newsletter', 'icon' => 'bx bx-paper-plane', 'route' => 'admin.newsletter.campaigns', 'active' => 'admin.newsletter.*'],

        ['header' => 'სისტემა'],
        ['label' => 'თარგმანები', 'icon' => 'bx bx-font', 'route' => 'admin.translations.index', 'active' => 'admin.translations.*'],
        ['label' => 'ენები', 'icon' => 'bx bx-globe', 'route' => 'admin.languages.index', 'active' => 'admin.languages.*'],
        ['label' => 'გადამისამართებები', 'icon' => 'bx bx-shuffle', 'route' => 'admin.redirects.index', 'active' => 'admin.redirects.*'],
        ['label' => 'გრაფიკი (cron)', 'icon' => 'bx bx-time-five', 'route' => 'admin.jobs.index', 'active' => 'admin.jobs.*'],
        ['label' => 'პარამეტრები', 'icon' => 'bx bx-cog', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
        ['label' => 'მომხმარებლები', 'icon' => 'bx bx-user', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
    ],
];
