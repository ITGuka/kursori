<?php
declare(strict_types=1);

namespace Core\Http;

use Closure;
use Core\App;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];
    /** @var array<string, class-string<Middleware>> */
    private array $aliases = [];
    /** @var list<array{prefix: string, middleware: list<string>, where: array<string, string>, name: string}> */
    private array $groups = [];

    public function __construct(private readonly App $app)
    {
    }

    // ---- რეგისტრაცია ----

    public function get(string $uri, Closure|array $action): Route
    {
        return $this->add(['GET'], $uri, $action);
    }

    public function post(string $uri, Closure|array $action): Route
    {
        return $this->add(['POST'], $uri, $action);
    }

    public function put(string $uri, Closure|array $action): Route
    {
        return $this->add(['PUT'], $uri, $action);
    }

    public function patch(string $uri, Closure|array $action): Route
    {
        return $this->add(['PATCH'], $uri, $action);
    }

    public function delete(string $uri, Closure|array $action): Route
    {
        return $this->add(['DELETE'], $uri, $action);
    }

    public function match(array $methods, string $uri, Closure|array $action): Route
    {
        return $this->add(array_map('strtoupper', $methods), $uri, $action);
    }

    /**
     * @param array{prefix?: string, middleware?: list<string>, where?: array<string, string>, name?: string} $attributes
     */
    public function group(array $attributes, Closure $routes): void
    {
        $parent = end($this->groups) ?: ['prefix' => '', 'middleware' => [], 'where' => [], 'name' => ''];
        $this->groups[] = [
            'prefix'     => self::join($parent['prefix'], $attributes['prefix'] ?? ''),
            'middleware' => array_merge($parent['middleware'], $attributes['middleware'] ?? []),
            'where'      => array_merge($parent['where'], $attributes['where'] ?? []),
            'name'       => $parent['name'] . ($attributes['name'] ?? ''),
        ];
        try {
            $routes($this);
        } finally {
            array_pop($this->groups);
        }
    }

    /** @param class-string<Middleware> $class */
    public function aliasMiddleware(string $alias, string $class): void
    {
        $this->aliases[$alias] = $class;
    }

    private function add(array $methods, string $uri, Closure|array $action): Route
    {
        $group = end($this->groups) ?: ['prefix' => '', 'middleware' => [], 'where' => [], 'name' => ''];
        $route = new Route($methods, self::join($group['prefix'], $uri), $action, $group['middleware'], $group['where'], $group['name']);
        $this->routes[] = $route;
        return $route;
    }

    private static function join(string $prefix, string $uri): string
    {
        $path = trim(trim($prefix, '/') . '/' . trim($uri, '/'), '/');
        return '/' . $path;
    }

    // ---- URL-ები ----

    public function url(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route->url($params);
            }
        }
        throw new RuntimeException("Route [$name] is not defined.");
    }

    public function has(string $name): bool
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return true;
            }
        }
        return false;
    }

    // ---- დამუშავება ----

    /** @param list<string> $globalMiddleware */
    public function dispatch(Request $request, array $globalMiddleware = []): Response
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            $params = $route->match($request->path());
            if ($params === null) {
                continue;
            }
            if (!$route->allows($request->method())) {
                array_push($allowed, ...$route->methods);
                continue;
            }

            $request->setRoute($route->getName(), $params);
            $middleware = array_merge($globalMiddleware, $route->getMiddleware());

            return $this->pipeline($middleware, $request, fn (Request $r) => $this->toResponse($this->call($route->action, $r)));
        }

        if ($allowed !== []) {
            throw new HttpException(405, '', ['Allow' => implode(', ', array_unique($allowed))]);
        }
        throw new HttpException(404);
    }

    /** @param list<string> $middleware */
    private function pipeline(array $middleware, Request $request, Closure $core): Response
    {
        $next = $core;
        foreach (array_reverse($middleware) as $entry) {
            $class = $this->aliases[$entry] ?? $entry;
            $inner = $next;
            $next = function (Request $r) use ($class, $inner): Response {
                $instance = $this->app->make($class);
                if (!$instance instanceof Middleware) {
                    throw new RuntimeException("[$class] must implement " . Middleware::class . '.');
                }
                return $instance->handle($r, $inner);
            };
        }
        return $next($request);
    }

    private function call(Closure|array $action, Request $request): mixed
    {
        if ($action instanceof Closure) {
            return $action(...$this->resolveArgs(new ReflectionFunction($action), $request));
        }

        [$class, $method] = $action;
        $controller = $this->app->make($class);
        return $controller->$method(...$this->resolveArgs(new ReflectionMethod($controller, $method), $request));
    }

    /** პარამეტრები: კლასის ტიპი → კონტეინერიდან, დანარჩენი → route პარამეტრიდან სახელით */
    private function resolveArgs(ReflectionFunctionAbstract $function, Request $request): array
    {
        $params = $request->params();
        $args = [];

        foreach ($function->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->app->make($type->getName());
            } elseif (array_key_exists($param->getName(), $params)) {
                $value = $params[$param->getName()];
                $args[] = $type instanceof ReflectionNamedType && $type->getName() === 'int' ? (int)$value : $value;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException("Cannot resolve route argument \${$param->getName()}.");
            }
        }
        return $args;
    }

    private function toResponse(mixed $result): Response
    {
        return match (true) {
            $result instanceof Response => $result,
            is_array($result) => Response::json($result),
            $result === null => new Response('', 204),
            default => Response::html((string)$result),
        };
    }
}
