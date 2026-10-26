<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\AdminUser;
use Core\Auth\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\RateLimiter;
use Core\Support\Session;

final class AuthController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly RateLimiter $limiter,
        private readonly ActivityLog $log,
        private readonly AdminUser $users,
    ) {
    }

    public function showLogin(): Response
    {
        return view('admin/auth/login');
    }

    public function login(Request $request): Response
    {
        $email = AdminUser::normalizeEmail($request->string('email', ''));
        $password = $request->string('password', '');
        $throttle = config('auth.throttle');
        $keys = ['login:' . $email . '|' . $request->ip() => $throttle['max_attempts'], 'login-ip:' . $request->ip() => $throttle['ip_max_attempts']];

        foreach ($keys as $key => $max) {
            if ($this->limiter->tooManyAttempts($key, $max)) {
                $minutes = (int)ceil($this->limiter->availableIn($key) / 60);
                return back(route('admin.login'))
                    ->withErrors(['email' => "ძალიან ბევრი მცდელობა. სცადეთ $minutes წუთში."])
                    ->withInput(['email' => $email, 'remember' => $request->string('remember')]);
            }
        }

        if ($email === '' || $password === '' || ($user = $this->auth->attempt($email, $password, $request->string('remember') === '1')) === null) {
            foreach ($keys as $key => $max) {
                $this->limiter->hit($key, $throttle['decay']);
            }
            $this->log->log('login_failed', data: ['email' => $email]);
            return back(route('admin.login'))
                ->withErrors(['email' => 'ელ-ფოსტა ან პაროლი არასწორია.'])
                ->withInput(['email' => $email, 'remember' => $request->string('remember')]);
        }

        $this->limiter->clear(array_key_first($keys));
        $this->users->recordLogin((int)$user['id'], $request->ip());
        $this->log->log('login');

        $intended = Session::pull('url.intended');
        return redirect(is_string($intended) && str_starts_with($intended, '/admin') ? url($intended) : route('admin.dashboard'));
    }

    public function logout(): Response
    {
        $this->log->log('logout');
        $this->auth->logout();
        return redirect(route('admin.login'));
    }
}
