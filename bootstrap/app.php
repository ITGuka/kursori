<?php
declare(strict_types=1);

use App\Middleware\SetLocale;
use Core\App;
use Core\Http\Middleware\VerifyCsrf;
use Core\Http\Router;

$app = new App(dirname(__DIR__));
$app->boot();

$app->addMiddleware(VerifyCsrf::class);

$router = $app->make(Router::class);
$router->aliasMiddleware('locale', SetLocale::class);

require $app->basePath('app/routes/web.php');

return $app;
