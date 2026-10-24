<?php
declare(strict_types=1);

use App\Middleware\Authenticate;
use App\Middleware\CheckMaintenance;
use App\Middleware\FlushPageCache;
use App\Middleware\PageCache;
use App\Middleware\RedirectIfAuthenticated;
use App\Middleware\SetLocale;
use App\Models\Language;
use App\Models\Redirect;
use App\Models\Translation;
use Core\App;
use Core\Database\DB;
use Core\Http\Middleware\VerifyCsrf;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;
use Core\I18n\Translator;
use Core\Mail\Mailer;
use Core\Media\ImageResizer;
use Core\Media\Uploader;
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

// მედია: ფაილის შემოწმება/შენახვა და ზომის ვარიანტები (config/media.php)
$app->singleton(Uploader::class, fn (App $app) => new Uploader(
    public_path((string)config('media.directory')),
    (array)config('media.allowed'),
    (int)config('media.max_size'),
    (int)config('media.max_pixels'),
    (array)config('media.messages', []),
));
$app->singleton(ImageResizer::class, fn () => new ImageResizer((array)config('media.variants'), (int)config('media.quality', 85)));

// ელ-ფოსტა: SMTP პარამეტრები ადმინიდან (პარამეტრები → ელ-ფოსტა)
$app->singleton(Mailer::class, function (App $app): Mailer {
    $settings = $app->make(Settings::class);
    return new Mailer([
        'host'       => (string)$settings->get('mail.host'),
        'port'       => (int)$settings->get('mail.port'),
        'encryption' => (string)$settings->get('mail.encryption'),
        'username'   => (string)$settings->get('mail.username'),
        'password'   => (string)$settings->get('mail.password'),
        'from_email' => (string)$settings->get('mail.from_email'),
        'from_name'  => (string)($settings->get('mail.from_name') ?: $settings->get('general.site_name')),
    ]);
});

// 404 → ჯერ გადამისამართება (ადმინი → გადამისამართებები), არ არის → ჟურნალში ჩაწერა და 404 გვერდი
$app->onNotFound(function (Request $request) use ($app): ?Response {
    if (!in_array($request->method(), ['GET', 'HEAD'], true) || str_starts_with($request->path(), '/admin')) {
        return null;
    }
    $redirects = $app->make(Redirect::class);
    $target = $redirects->resolve($request->path());
    if ($target !== null) {
        $query = (string)$request->server('QUERY_STRING', '');
        return redirect($target['url'] . ($query !== '' && !str_contains($target['url'], '?') ? "?$query" : ''), $target['code']);
    }
    $redirects->logNotFound($request->path(), (string)$request->header('Referer', ''));
    return null;
});

$app->addMiddleware(VerifyCsrf::class);

$router = $app->make(Router::class);
$router->aliasMiddleware('locale', SetLocale::class);
$router->aliasMiddleware('maintenance', CheckMaintenance::class);
$router->aliasMiddleware('auth', Authenticate::class);
$router->aliasMiddleware('guest', RedirectIfAuthenticated::class);
$router->aliasMiddleware('page_cache', PageCache::class);
$router->aliasMiddleware('flush_page_cache', FlushPageCache::class);

require $app->basePath('app/routes/admin.php');
require $app->basePath('app/routes/web.php');

return $app;
