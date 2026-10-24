<?php
declare(strict_types=1);

namespace App\Blocks;

/**
 * ბლოკების ტიპები config/blocks.php-დან.
 */
final class BlockRegistry
{
    /** @var array<string, array> */
    private array $types;

    public function __construct()
    {
        $this->types = (array)config('blocks', []);
    }

    /** @return array<string, array> */
    public function all(): array
    {
        return $this->types;
    }

    public function has(string $type): bool
    {
        return isset($this->types[$type]);
    }

    public function get(string $type): ?array
    {
        return $this->types[$type] ?? null;
    }

    public function label(string $type): string
    {
        return (string)($this->types[$type]['label'] ?? $type);
    }

    public function isHeader(string $type): bool
    {
        return !empty($this->types[$type]['header']);
    }
}
