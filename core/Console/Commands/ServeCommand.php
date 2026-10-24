<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;

final class ServeCommand extends Command
{
    protected string $name = 'serve';
    protected string $description = 'Start PHP built-in server for public/ (when Apache is not available)';
    protected string $usage = '[--host=127.0.0.1] [--port=8000]';

    public function handle(array $args, array $options): int
    {
        $host = is_string($options['host'] ?? null) ? $options['host'] : '127.0.0.1';
        $port = (int)($options['port'] ?? 8000);
        $public = $this->app->basePath('public');

        $this->output->info("Serving on http://$host:$port (Ctrl+C to stop)");
        passthru(sprintf(
            '%s -S %s -t %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg("$host:$port"),
            escapeshellarg($public),
            escapeshellarg($public . DIRECTORY_SEPARATOR . 'index.php'),
        ), $code);
        return (int)$code;
    }
}
