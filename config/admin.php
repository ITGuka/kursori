<?php
declare(strict_types=1);

// ადმინის sidebar. route — route-ის სახელი, active — რომელ route-ებზე მოინიშნოს (is_route pattern).
// ყოველი ახალი მოდული (ფაზა 2+) აქ ემატება.
return [
    'menu' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-tachometer-alt', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'მომხმარებლები', 'icon' => 'fa fa-users', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
    ],
];
