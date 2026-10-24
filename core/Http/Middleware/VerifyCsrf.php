<?php
declare(strict_types=1);

namespace Core\Http\Middleware;

use Closure;
use Core\Http\HttpException;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Csrf;

/**
 * ყველა მდგომარეობის შემცვლელ მოთხოვნაზე ამოწმებს _token ველს ან X-CSRF-TOKEN header-ს.
 */
final class VerifyCsrf implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && !Csrf::verify($request->input('_token') ?? $request->header('X-CSRF-TOKEN'))) {
            throw new HttpException(419);
        }
        return $next($request);
    }
}
