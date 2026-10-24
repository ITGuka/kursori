<?php
declare(strict_types=1);

namespace Core\View;

use RuntimeException;

/**
 * PHP შაბლონები views/ საქაღალდიდან: render('front/home', [...]).
 */
final class View
{
    /** @var array<string, mixed> ყველა შაბლონისთვის ხელმისაწვდომი ცვლადები */
    private array $shared = [];

    public function __construct(private readonly string $path)
    {
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function render(string $name, array $data = []): string
    {
        return (new Template($this, $name, $data + $this->shared))->render();
    }

    public function exists(string $name): bool
    {
        return self::validName($name) && is_file($this->path . '/' . $name . '.php');
    }

    public function file(string $name): string
    {
        if (!$this->exists($name)) {
            throw new RuntimeException("View [$name] not found.");
        }
        return $this->path . '/' . $name . '.php';
    }

    private static function validName(string $name): bool
    {
        return (bool)preg_match('#^[\w\-]+(/[\w\-]+)*$#', $name);
    }
}
