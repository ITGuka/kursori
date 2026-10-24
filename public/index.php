<?php
declare(strict_types=1);

// php bin/console serve: არსებულ სტატიკურ ფაილებს ჩაშენებული სერვერი თავად აბრუნებს
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->run();
