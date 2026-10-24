<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Models\Language;
use Closure;
use Core\App;
use Core\Http\HttpException;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * URL-ის პრეფიქსიდან (/ka/, /en/) აყენებს მიმდინარე ენას; არააქტიური ან უცნობი ენა → 404.
 */
final class SetLocale implements Middleware
{
    public function __construct(
        private readonly App $app,
        private readonly View $view,
        private readonly Language $languages,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $lang = (string)$request->param('lang', '');
        if (!in_array($lang, $this->languages->activeCodes(), true)) {
            throw new HttpException(404);
        }

        $this->app->setLocale($lang);
        $this->view->share('lang', $lang);
        $this->view->share('languages', $this->languages->active());
        return $next($request);
    }
}
