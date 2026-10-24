<?php
declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Cache;

/**
 * ადმინში ნებისმიერი წარმატებული ცვლილება (POST/PUT/PATCH/DELETE, სტატუსი < 400) ფრონტის გვერდების ქეშს შლის.
 * გვერდი, მენიუ, პარამეტრი, თარგმანი, მედია... ერთმანეთზეა დამოკიდებული — ზუსტ ინვალიდაციას სრული გასუფთავება სჯობს.
 */
final class FlushPageCache implements Middleware
{
    public function __construct(private readonly Cache $cache)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (!$request->isMethod('GET') && $response->status() < 400) {
            $this->cache->flush(PageCache::PREFIX);
        }
        return $response;
    }
}
