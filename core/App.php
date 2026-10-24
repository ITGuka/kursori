<?php
declare(strict_types=1);

namespace Core;

use Closure;
use Core\Auth\Auth;
use Core\Database\DB;
use Core\Debug\ErrorHandler;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;
use Core\Support\Config;
use Core\Support\Env;
use Core\Support\RateLimiter;
use Core\Support\Session;
use Core\View\View;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;
use Throwable;

/**
 * აპლიკაციის ბირთვი: სერვისების კონტეინერი, ჩატვირთვა და HTTP მოთხოვნის დამუშავება.
 */
final class App
{
    private static ?App $instance = null;

    /** @var array<string, Closure> */
    private array $factories = [];
    /** @var array<string, object> */
    private array $instances = [];
    /** @var list<class-string> ყველა route-ზე მომუშავე middleware */
    private array $middleware = [];

    private ?Request $request = null;
    private string $locale = '';

    public function __construct(private readonly string $basePath)
    {
        self::$instance = $this;
        $this->instance(self::class, $this);
    }

    public static function getInstance(): self
    {
        return self::$instance ?? throw new RuntimeException('App has not been created.');
    }

    public function boot(): void
    {
        Env::load($this->basePath('.env'));

        $config = new Config($this->basePath('config'));
        $this->instance(Config::class, $config);
        date_default_timezone_set($config->get('app.timezone', 'UTC'));
        $this->locale = (string)$config->get('app.default_locale', 'en');

        $errors = new ErrorHandler($this);
        $errors->register();
        $this->instance(ErrorHandler::class, $errors);

        $this->singleton(DB::class, fn () => new DB($config->get('database', [])));
        $this->singleton(Router::class, fn (App $app) => new Router($app));
        $this->singleton(View::class, fn (App $app) => new View($app->basePath('views')));
        $this->singleton(Auth::class, fn (App $app) => new Auth($app->make(DB::class), $config->get('auth', [])));
        $this->singleton(RateLimiter::class, fn (App $app) => new RateLimiter($app->make(DB::class)));
    }

    // ---- კონტეინერი ----

    public function singleton(string $id, Closure $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->instances[$id]);
    }

    public function instance(string $id, object $object): void
    {
        $this->instances[$id] = $object;
    }

    /**
     * @template T of object
     * @param class-string<T>|string $id
     * @return T|object
     */
    public function make(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (isset($this->factories[$id])) {
            return $this->instances[$id] = ($this->factories[$id])($this);
        }
        if (class_exists($id)) {
            return $this->build($id);
        }
        throw new RuntimeException("Unknown service [$id].");
    }

    /** ახალი ობიექტი; კონსტრუქტორის კლასის ტიპის პარამეტრები კონტეინერიდან ივსება */
    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException("Cannot resolve \${$param->getName()} for [$class].");
            }
        }
        return $reflection->newInstanceArgs($args);
    }

    // ---- HTTP ----

    public function addMiddleware(string $class): void
    {
        $this->middleware[] = $class;
    }

    public function run(): void
    {
        Session::start($this->config('app.session', []));
        $this->handle(Request::capture())->send();
    }

    public function handle(Request $request): Response
    {
        $this->request = $request;
        $this->instance(Request::class, $request);

        try {
            return $this->make(Router::class)->dispatch($request, $this->middleware);
        } catch (Throwable $e) {
            return $this->make(ErrorHandler::class)->render($e, $request);
        }
    }

    public function request(): ?Request
    {
        return $this->request;
    }

    /** საიტის base path (მაგ. "/WEBDEV" ან ""), რომ URL-ები ქვესაქაღალდეშიც მუშაობდეს */
    public function baseUrlPath(): string
    {
        if ($this->request !== null) {
            return $this->request->basePath();
        }
        return rtrim((string)parse_url((string)$this->config('app.url', ''), PHP_URL_PATH), '/');
    }

    // ---- პარამეტრები ----

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->make(Config::class)->get($key, $default);
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function basePath(string $path = ''): string
    {
        return $path === '' ? $this->basePath : $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}
