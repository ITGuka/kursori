<?php
declare(strict_types=1);

use App\Controllers\Front\HomeController;
use App\Models\Language;
use Core\Http\Router;

/** @var Router $router */

// "/" → ძირითადი ენა (ბაზიდან)
$router->get('/', fn (Language $languages) => redirect(url($languages->defaultCode())))->name('root');

// ენის კოდი URL-ში; აქტიურობას SetLocale ამოწმებს languages ცხრილით
$router->group([
    'prefix'     => '/{lang}',
    'where'      => ['lang' => '[a-z]{2}'],
    'middleware' => ['locale', 'maintenance'],
], function (Router $router): void {
    $router->get('/', [HomeController::class, 'index'])->name('home');
});
