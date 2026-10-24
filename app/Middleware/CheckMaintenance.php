<?php
declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Core\Auth\Auth;
use Core\Http\Middleware;
use Core\Http\Request;
use Core\Http\Response;
use Core\Settings\Settings;

/**
 * ტექნიკური სამუშაოების რეჟიმი: სტუმრები ხედავენ 503 გვერდს, შესული ადმინი — საიტს.
 */
final class CheckMaintenance implements Middleware
{
    public function __construct(private readonly Settings $settings, private readonly Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->settings->get('general.maintenance_mode') === true && !$this->auth->check()) {
            return view('errors/maintenance', ['message' => setting('general.maintenance_text')], 503)
                ->header('Retry-After', '3600');
        }
        return $next($request);
    }
}
