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
    /** route-ები, რომლებიც საიტზე არაფერს ცვლის (Inbox, newsletter, მომხმარებლები, დავალებები) */
    private const SKIP = ['admin.inbox.', 'admin.newsletter.', 'admin.users.', 'admin.todos.', 'admin.logout'];

    public function __construct(private readonly Cache $cache)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $route = (string)$request->routeName();
        $affectsSite = array_filter(self::SKIP, fn (string $prefix) => str_starts_with($route, $prefix)) === [];
        if ($affectsSite && !$request->isMethod('GET') && $response->status() < 400) {
            $this->cache->flush(PageCache::PREFIX);
        }
        return $response;
    }
}
