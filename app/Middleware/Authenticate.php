<?php
declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Core\Auth\Auth;
use Core\Http\HttpException;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Session;

/**
 * ადმინის გვერდები მხოლოდ შესულ მომხმარებელს; სტუმარი → login (შემდეგ ბრუნდება იმავე გვერდზე).
 */
final class Authenticate implements Middleware
{
    public function __construct(private readonly Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->auth->check()) {
            if ($request->wantsJson()) {
                throw new HttpException(401);
            }
            if ($request->isMethod('GET')) {
                Session::set('url.intended', $request->path());
            }
            return redirect(route('admin.login'));
        }

        // ადმინის გვერდები არ ინდექსირდება და ბრაუზერის ქეშში არ რჩება
        return $next($request)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
