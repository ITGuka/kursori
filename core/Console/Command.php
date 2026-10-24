<?php
declare(strict_types=1);

namespace Core\Console;

use Core\App;

abstract class Command
{
    /** ბრძანების სახელი, მაგ. "migrate:status" */
    protected string $name = '';
    protected string $description = '';
    /** დახმარების ტექსტი: არგუმენტები და ოფციები */
    protected string $usage = '';

    public function __construct(protected readonly App $app, protected readonly Output $output)
    {
    }

    /**
     * @param list<string> $args პოზიციური არგუმენტები
     * @param array<string, string|true> $options --key=value / --flag
     * @return int გასვლის კოდი (0 — წარმატება)
     */
    abstract public function handle(array $args, array $options): int;

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function usage(): string
    {
        return $this->usage;
    }
}
