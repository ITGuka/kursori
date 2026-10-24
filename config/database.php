<?php
declare(strict_types=1);

return [
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => (int)env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', ''),
    'username' => env('DB_USERNAME', 'root'),
    'password' => (string)env('DB_PASSWORD', ''),
    'charset'  => 'utf8mb4',
    'timezone' => '+04:00',
];
