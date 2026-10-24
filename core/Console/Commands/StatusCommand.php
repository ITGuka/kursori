<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Database\DB;
use Core\Database\Migrator;

final class StatusCommand extends Command
{
    protected string $name = 'migrate:status';
    protected string $description = 'Show which migrations have run';

    public function handle(array $args, array $options): int
    {
        $migrator = new Migrator($this->app->make(DB::class), $this->app->basePath('database/migrations'));
        $rows = [];
        foreach ($migrator->status() as $name => $batch) {
            $rows[] = [$batch === null ? 'Pending' : 'Ran', $batch === null ? '' : (string)$batch, $name];
        }

        $rows === [] ? $this->output->info('No migrations found.') : $this->output->table(['Status', 'Batch', 'Migration'], $rows);
        return 0;
    }
}
