<?php
declare(strict_types=1);

use App\Controllers\Front\HomeController;
use Core\Http\Router;

/** @var Router $router */

// "/" → ძირითადი ენა
$router->get('/', fn () => redirect(url(config('app.default_locale'))))->name('root');

$router->group([
    'prefix'     => '/{lang}',
    'where'      => ['lang' => implode('|', config('app.locales'))],
    'middleware' => ['locale'],
], function (Router $router): void {
    $router->get('/', [HomeController::class, 'index'])->name('home');
});
