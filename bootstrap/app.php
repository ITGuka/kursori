<?php
declare(strict_types=1);

use App\Middleware\Authenticate;
use App\Middleware\RedirectIfAuthenticated;
use App\Middleware\SetLocale;
use Core\App;
use Core\Http\Middleware\VerifyCsrf;
use Core\Http\Router;

$app = new App(dirname(__DIR__));
$app->boot();

$app->addMiddleware(VerifyCsrf::class);

$router = $app->make(Router::class);
$router->aliasMiddleware('locale', SetLocale::class);
$router->aliasMiddleware('auth', Authenticate::class);
$router->aliasMiddleware('guest', RedirectIfAuthenticated::class);

require $app->basePath('app/routes/admin.php');
require $app->basePath('app/routes/web.php');

return $app;
