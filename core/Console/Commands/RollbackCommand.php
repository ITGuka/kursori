<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Database\DB;
use Core\Database\Migrator;

final class RollbackCommand extends Command
{
    protected string $name = 'migrate:rollback';
    protected string $description = 'Roll back the last migration batch';
    protected string $usage = '[--step=1]';

    public function handle(array $args, array $options): int
    {
        $steps = max(1, (int)($options['step'] ?? 1));
        $migrator = new Migrator($this->app->make(DB::class), $this->app->basePath('database/migrations'));
        $done = $migrator->rollback($steps, fn (string $name) => $this->output->line('  Rolled back: ' . $name));

        $done === [] ? $this->output->info('Nothing to roll back.') : $this->output->info(count($done) . ' migration(s) rolled back.');
        return 0;
    }
}
