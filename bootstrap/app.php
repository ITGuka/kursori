<?php
declare(strict_types=1);

use App\Middleware\Authenticate;
use App\Middleware\CheckMaintenance;
use App\Middleware\RedirectIfAuthenticated;
use App\Middleware\SetLocale;
use App\Models\Language;
use App\Models\Translation;
use Core\App;
use Core\Database\DB;
use Core\Http\Middleware\VerifyCsrf;
use Core\Http\Router;
use Core\I18n\Translator;
use Core\Settings\Settings;
use Core\Support\Cache;

$app = new App(dirname(__DIR__));
$app->boot();

// UI თარგმანები ბაზიდან; fallback — ძირითადი ენა
$app->singleton(Translator::class, fn (App $app) => new Translator(
    Closure::fromCallable([$app->make(Translation::class), 'forLocale']),
    $app->make(Language::class)->defaultCode(),
));

// საიტის პარამეტრები: განსაზღვრებები config/settings.php-ში
$app->singleton(Settings::class, fn (App $app) => new Settings(
    $app->make(DB::class),
    $app->make(Cache::class),
    (array)config('settings'),
    $app->make(Language::class)->defaultCode(),
));

$app->addMiddleware(VerifyCsrf::class);

$router = $app->make(Router::class);
$router->aliasMiddleware('locale', SetLocale::class);
$router->aliasMiddleware('maintenance', CheckMaintenance::class);
$router->aliasMiddleware('auth', Authenticate::class);
$router->aliasMiddleware('guest', RedirectIfAuthenticated::class);

require $app->basePath('app/routes/admin.php');
require $app->basePath('app/routes/web.php');

return $app;
