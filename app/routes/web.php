<?php
declare(strict_types=1);

use App\Controllers\Front\CollectionController;
use App\Controllers\Front\FormController;
use App\Controllers\Front\NewsletterController;
use App\Controllers\Front\OrderController;
use App\Controllers\Front\PageController;
use App\Controllers\Front\SeoController;
use App\Models\Language;
use Core\Http\Router;

/** @var Router $router */

$router->get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
$router->get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// "/" → ძირითადი ენა (ბაზიდან)
$router->get('/', fn (Language $languages) => redirect(url($languages->defaultCode())))->name('root');

// ენის კოდი URL-ში; აქტიურობას SetLocale ამოწმებს languages ცხრილით
$router->group([
    'prefix'     => '/{lang}',
    'where'      => ['lang' => '[a-z]{2}', 'slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
    'middleware' => ['locale', 'maintenance'],
], function (Router $router): void {
    // page_cache — მხოლოდ კონტენტის გვერდებზე (newsletter-ის token-იანი გვერდები არ იქეშება)
    $router->get('/', [PageController::class, 'home'])->middleware('page_cache')->name('home');

    // ფორმები და newsletter (კონკრეტული ბილიკები — /{slug}-ზე ადრე)
    $router->post('/forms/{form}', [FormController::class, 'submit'])->where('form', '[a-z_]+')->name('form.submit');
    $router->post('/order', [OrderController::class, 'submit'])->name('order.submit');
    $router->post('/newsletter', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    $router->get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->where('token', '[a-f0-9]{64}')->name('newsletter.confirm');
    $router->get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribePage'])->where('token', '[a-f0-9]{64}')->name('newsletter.unsubscribe');
    $router->post('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->where('token', '[a-f0-9]{64}')->name('newsletter.unsubscribe.confirm');

    $router->get('/{slug}', [PageController::class, 'show'])->middleware('page_cache')->name('page');
    // კოლექციის ჩანაწერი: /ka/servisebi/web-dizaini (კოლექცია — config/collections.php → route)
    $router->get('/{collection}/{slug}', [CollectionController::class, 'show'])->where('collection', '[a-z0-9]+(?:-[a-z0-9]+)*')
        ->middleware('page_cache')->name('collection.item');
});
