<?php
declare(strict_types=1);

namespace Core\Http;

use Closure;

final class Route
{
    private ?string $name = null;
    private ?string $regex = null;

    /**
     * @param list<string> $methods
     * @param Closure|array{0: class-string, 1: string} $action
     * @param list<string> $middleware
     * @param array<string, string> $wheres პარამეტრების regex შეზღუდვები
     */
    public function __construct(
        public readonly array $methods,
        public readonly string $uri,
        public readonly Closure|array $action,
        private array $middleware = [],
        private array $wheres = [],
        private readonly string $namePrefix = '',
    ) {
    }

    public function name(string $name): self
    {
        $this->name = $this->namePrefix . $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function middleware(string ...$middleware): self
    {
        array_push($this->middleware, ...$middleware);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function where(string $param, string $pattern): self
    {
        $this->wheres[$param] = $pattern;
        $this->regex = null;
        return $this;
    }

    public function allows(string $method): bool
    {
        return in_array($method, $this->methods, true) || ($method === 'HEAD' && in_array('GET', $this->methods, true));
    }

    /** @return array<string, string>|null პარამეტრები ან null, თუ ბილიკი არ ემთხვევა */
    public function match(string $path): ?array
    {
        if (!preg_match($this->regex ??= $this->compile(), $path, $matches)) {
            return null;
        }
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /** "/{lang}/{slug}" + პარამეტრები → "/ka/about"; ზედმეტი პარამეტრები query string-ში გადადის */
    public function url(array $params = []): string
    {
        $path = preg_replace_callback('#\{(\w+)\}#', function (array $m) use (&$params): string {
            $value = rawurlencode((string)($params[$m[1]] ?? ''));
            unset($params[$m[1]]);
            return $value;
        }, $this->uri);

        return $params === [] ? $path : $path . '?' . http_build_query($params);
    }

    private function compile(): string
    {
        $parts = preg_split('#(\{\w+\})#', $this->uri, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $regex = '';
        foreach ($parts as $part) {
            if (preg_match('#^\{(\w+)\}$#', $part, $m)) {
                $regex .= '(?P<' . $m[1] . '>' . ($this->wheres[$m[1]] ?? '[^/]+') . ')';
            } else {
                $regex .= preg_quote($part, '#');
            }
        }
        return '#^' . $regex . '$#u';
    }
}
