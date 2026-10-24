<?php
declare(strict_types=1);

use App\Controllers\Front\PageController;
use App\Models\Language;
use Core\Http\Router;

/** @var Router $router */

// "/" → ძირითადი ენა (ბაზიდან)
$router->get('/', fn (Language $languages) => redirect(url($languages->defaultCode())))->name('root');

// ენის კოდი URL-ში; აქტიურობას SetLocale ამოწმებს languages ცხრილით
$router->group([
    'prefix'     => '/{lang}',
    'where'      => ['lang' => '[a-z]{2}', 'slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
    'middleware' => ['locale', 'maintenance'],
], function (Router $router): void {
    $router->get('/', [PageController::class, 'home'])->name('home');
    $router->get('/{slug}', [PageController::class, 'show'])->name('page');
});
