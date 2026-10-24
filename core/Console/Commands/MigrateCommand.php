<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Database\DB;
use Core\Database\Migrator;

final class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Run pending database migrations';
    protected string $usage = '[--seed]';

    public function handle(array $args, array $options): int
    {
        $migrator = new Migrator($this->app->make(DB::class), $this->app->basePath('database/migrations'));
        $ran = $migrator->migrate(fn (string $name) => $this->output->line('  Migrated: ' . $name));

        $ran === [] ? $this->output->info('Nothing to migrate.') : $this->output->info(count($ran) . ' migration(s) done.');

        if (isset($options['seed'])) {
            return (new SeedCommand($this->app, $this->output))->handle([], []);
        }
        return 0;
    }
}
