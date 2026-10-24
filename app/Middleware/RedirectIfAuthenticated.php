<?php
declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Core\Auth\Auth;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;

/** login გვერდი შესულ მომხმარებელს Dashboard-ზე გადაამისამართებს */
final class RedirectIfAuthenticated implements Middleware
{
    public function __construct(private readonly Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->check()) {
            return redirect(route('admin.dashboard'));
        }
        return $next($request)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
