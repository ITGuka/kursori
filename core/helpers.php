<?php
declare(strict_types=1);

use Core\App;
use Core\Auth\Auth;
use Core\Http\HttpException;
use Core\Http\Response;
use Core\Http\Router;
use Core\Support\Csrf;
use Core\Support\Env;
use Core\Support\Session;
use Core\View\View;

function app(?string $id = null): mixed
{
    $app = App::getInstance();
    return $id === null ? $app : $app->make($id);
}

function config(string $key, mixed $default = null): mixed
{
    return App::getInstance()->config($key, $default);
}

function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

/** HTML-ში უსაფრთხო გამოტანა */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(string $path = ''): string
{
    return App::getInstance()->basePath($path);
}

function storage_path(string $path = ''): string
{
    return base_path('storage' . ($path === '' ? '' : '/' . ltrim($path, '/')));
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path === '' ? '' : '/' . ltrim($path, '/')));
}

/** საიტის შიდა URL base path-ით: url('ka/about') → "/WEBDEV/ka/about" */
function url(string $path = ''): string
{
    return App::getInstance()->baseUrlPath() . '/' . ltrim($path, '/');
}

function route(string $name, array $params = []): string
{
    return url(app(Router::class)->url($name, $params));
}

/** public/assets-ის ფაილი; ?v=ცვლილების დრო — ბრაუზერის ქეშის გასაახლებლად */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = public_path('assets/' . $path);
    return url('assets/' . $path) . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function view(string $name, array $data = [], int $status = 200): Response
{
    return Response::html(app(View::class)->render($name, $data), $status);
}

function redirect(string $url, int $status = 302): Response
{
    return Response::redirect($url, $status);
}

function abort(int $status, string $message = ''): never
{
    throw new HttpException($status, $message);
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function locale(): string
{
    return App::getInstance()->locale();
}

function auth(): Auth
{
    return app(Auth::class);
}

/** წინა გვერდზე დაბრუნება (მხოლოდ იმავე host-ზე), სხვაგვარად $fallback */
function back(string $fallback = ''): Response
{
    $referer = (string)(app()->request()?->header('Referer') ?? '');
    $host = (string)(app()->request()?->header('Host') ?? '');
    $sameHost = $referer !== '' && parse_url($referer, PHP_URL_HOST) . (($p = parse_url($referer, PHP_URL_PORT)) ? ":$p" : '') === $host;

    return Response::redirect($sameHost ? $referer : ($fallback !== '' ? $fallback : url()));
}

/** წინა მოთხოვნის ფორმის მნიშვნელობა (Response::withInput) */
function old(string $key, mixed $default = ''): mixed
{
    $old = Session::get('_old', []);
    return is_array($old) && array_key_exists($key, $old) ? $old[$key] : $default;
}

/**
 * ვალიდაციის შეცდომები (Response::withErrors)
 * @return ($key is null ? array<string, string> : string|null)
 */
function errors(?string $key = null): array|string|null
{
    $errors = Session::get('_errors', []);
    $errors = is_array($errors) ? $errors : [];
    return $key === null ? $errors : ($errors[$key] ?? null);
}

/** მიმდინარე route-ის სახელის შემოწმება: is_route('admin.users.*') */
function is_route(string ...$patterns): bool
{
    $name = app()->request()?->routeName();
    if ($name === null) {
        return false;
    }
    foreach ($patterns as $pattern) {
        if (fnmatch($pattern, $name)) {
            return true;
        }
    }
    return false;
}
