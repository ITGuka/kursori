<?php
declare(strict_types=1);

namespace Core\Support;

/**
 * config/*.php ფაილების წამკითხველი: get('app.name') → config/app.php ['name'].
 */
final class Config
{
    /** @var array<string, array> */
    private array $items = [];

    public function __construct(private readonly string $path)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->file(array_shift($segments));

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);
        $this->file($file);

        $target = &$this->items[$file];
        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }
        $target = $value;
    }

    private function file(string $name): ?array
    {
        if (!array_key_exists($name, $this->items)) {
            $file = $this->path . DIRECTORY_SEPARATOR . $name . '.php';
            $this->items[$name] = preg_match('/^\w+$/', $name) && is_file($file) ? (array)require $file : [];
        }
        return $this->items[$name];
    }
}
