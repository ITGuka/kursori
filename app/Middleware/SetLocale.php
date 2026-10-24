<?php
declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Core\App;
use Core\Http\HttpException;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * URL-ის პრეფიქსიდან (/ka/, /en/) აყენებს მიმდინარე ენას.
 */
final class SetLocale implements Middleware
{
    public function __construct(private readonly App $app, private readonly View $view)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $lang = (string)$request->param('lang', '');
        if (!in_array($lang, config('app.locales'), true)) {
            throw new HttpException(404);
        }

        $this->app->setLocale($lang);
        $this->view->share('lang', $lang);
        return $next($request);
    }
}
