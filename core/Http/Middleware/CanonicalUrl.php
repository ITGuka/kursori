<?php
declare(strict_types=1);

namespace Core\Http\Middleware;

use Closure;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;

/**
 * production-ში საიტს ერთი მისამართი აქვს — APP_URL: http → https და www ↔ non-www, 301-ით (მხოლოდ GET/HEAD,
 * POST-ის მონაცემი რომ არ დაიკარგოს). HTTPS-ზე ემატება HSTS. ლოკალურად (APP_ENV ≠ production) არაფერს აკეთებს.
 */
final class CanonicalUrl implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonical = parse_url((string)config('app.url'));
        $host = strtolower((string)($canonical['host'] ?? ''));
        if (config('app.env') !== 'production' || $host === '') {
            return $next($request);
        }

        $scheme = strtolower((string)($canonical['scheme'] ?? 'https'));
        $hostWithPort = $host . (isset($canonical['port']) ? ':' . $canonical['port'] : '');
        // პროქსის/CDN-ის უკან (Cloudflare და სხვ.) HTTPS-ს X-Forwarded-Proto აჩვენებს
        $secure = $request->isSecure() || strtolower((string)$request->header('X-Forwarded-Proto', '')) === 'https';

        if (in_array($request->method(), ['GET', 'HEAD'], true)
            && (strtolower((string)$request->header('Host', '')) !== $hostWithPort || ($scheme === 'https') !== $secure)) {
            return Response::redirect($scheme . '://' . $hostWithPort . (string)$request->server('REQUEST_URI', '/'), 301);
        }

        $response = $next($request);
        return $scheme === 'https' && $secure ? $response->header('Strict-Transport-Security', 'max-age=31536000') : $response;
    }
}
