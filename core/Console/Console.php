<?php
declare(strict_types=1);

namespace Core\Console;

use Core\App;
use Throwable;

/**
 * CLI: php bin/console <command> [args] [--option=value]
 */
final class Console
{
    /** @var array<string, Command> */
    private array $commands = [];
    private Output $output;

    public function __construct(private readonly App $app)
    {
        $this->output = new Output();
    }

    /** @param class-string<Command> ...$classes */
    public function register(string ...$classes): void
    {
        foreach ($classes as $class) {
            $command = new $class($this->app, $this->output);
            $this->commands[$command->name()] = $command;
        }
    }

    /** @param list<string> $argv */
    public function run(array $argv): int
    {
        $name = $argv[1] ?? 'list';
        [$args, $options] = self::parse(array_slice($argv, 2));

        if ($name === 'list' || $name === '--help' || $name === '-h') {
            $this->list();
            return 0;
        }

        $command = $this->commands[$name] ?? null;
        if ($command === null) {
            $this->output->error("Command [$name] not found. Run: php bin/console list");
            return 1;
        }
        if (isset($options['help'])) {
            $this->output->line($command->description());
            $this->output->line('Usage: php bin/console ' . $name . ' ' . $command->usage());
            return 0;
        }

        try {
            return $command->handle($args, $options);
        } catch (Throwable $e) {
            $this->output->error(get_class($e) . ': ' . $e->getMessage());
            $this->output->line($this->output->muted($e->getFile() . ':' . $e->getLine()));
            return 1;
        }
    }

    private function list(): void
    {
        $this->output->line('Usage: php bin/console <command> [arguments] [--options]');
        $this->output->line();

        $commands = $this->commands;
        ksort($commands);
        $width = max(array_map('strlen', array_keys($commands)) ?: [0]);
        foreach ($commands as $name => $command) {
            $this->output->line('  ' . $this->output->highlight(str_pad($name, $width)) . '  ' . $command->description());
        }
    }

    /** @return array{0: list<string>, 1: array<string, string|true>} */
    private static function parse(array $tokens): array
    {
        $args = [];
        $options = [];
        foreach ($tokens as $token) {
            if (str_starts_with($token, '--')) {
                [$key, $value] = array_pad(explode('=', substr($token, 2), 2), 2, true);
                $options[$key] = $value;
            } else {
                $args[] = $token;
            }
        }
        return [$args, $options];
    }
}
