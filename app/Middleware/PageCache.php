<?php
declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Core\Auth\Auth;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Cache;
use Core\Support\Csrf;
use Core\Support\Session;

/**
 * ფრონტის გვერდების სრული HTML ქეში სტუმრებისთვის (storage/cache/data/pages.*).
 *
 * არ იქეშება: შესული ადმინი (წინასწარი ხედი/მონახაზები), GET-ის გარდა, query string (გარდა მხოლოდ ?page=N), ფორმის შედეგის გვერდი (flash),
 * 200-ის გარდა ნებისმიერი პასუხი. CSRF token ქეშში ჩანაცვლებულია და ყოველ მოთხოვნაზე ვიზიტორის token ჩაისმება.
 * ქეში იშლება ადმინში ნებისმიერი ცვლილებისას (FlushPageCache) და `php bin/console cache:clear`-ით.
 */
final class PageCache implements Middleware
{
    public const PREFIX = 'pages.';
    private const TOKEN = '%%CSRF_TOKEN%%';

    public function __construct(private readonly Cache $cache, private readonly Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // query string-იდან იქეშება მხოლოდ სიის გვერდი (?page=2…) — სხვა პარამეტრები ქეშს ავსებდა
        $query = $request->query();
        $listPage = count($query) === 1 && preg_match('/^[2-9]\d{0,3}$|^1\d{1,3}$/', (string)($query['page'] ?? '')) ? (string)$query['page'] : '';
        if (!config('app.page_cache.enabled', true)
            || !$request->isMethod('GET')
            || ($query !== [] && $listPage === '')
            || Session::has('_form') || Session::has('_errors')
            || $this->auth->check()) {
            return $next($request)->header('X-Page-Cache', 'BYPASS');
        }

        // base path-ი გასაღებშია — localhost/KURSORI და kursori.test სხვადასხვა ბმულებს შეიცავს
        $key = self::PREFIX . md5(app()->baseUrlPath() . '|' . $request->path() . ($listPage !== '' ? '?page=' . $listPage : ''));
        $cached = $this->cache->get($key);
        if (is_array($cached) && isset($cached['html'])) {
            return Response::html(str_replace(self::TOKEN, Csrf::token(), $cached['html']))->header('X-Page-Cache', 'HIT');
        }

        $response = $next($request);
        if ($response->status() === 200 && str_starts_with((string)($response->headers()['Content-Type'] ?? ''), 'text/html')) {
            $html = str_replace(Csrf::token(), self::TOKEN, $response->content());
            $this->cache->put($key, ['html' => $html], (int)config('app.page_cache.ttl', 86400));
        }
        return $response->header('X-Page-Cache', 'MISS');
    }
}
