<?php
declare(strict_types=1);

return [
    'table'        => 'admin_users',
    'session_key'  => '_admin_id',
    // უმოქმედობის შემდეგ ავტომატური გამოსვლა (წამები)
    'idle_timeout' => 2 * 60 * 60,

    'throttle' => [
        'max_attempts'    => 5,    // ერთი email + IP
        'ip_max_attempts' => 20,   // ერთი IP, ნებისმიერი email
        'decay'           => 15 * 60,
    ],
];
