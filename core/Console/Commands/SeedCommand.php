<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Database\DB;
use Core\Database\Seeder;
use RuntimeException;

final class SeedCommand extends Command
{
    protected string $name = 'db:seed';
    protected string $description = 'Run database seeders (all, or the ones whose name contains the argument)';
    protected string $usage = '[name]';

    public function handle(array $args, array $options): int
    {
        $filter = $args[0] ?? null;
        $files = glob($this->app->basePath('database/seeds') . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files);

        $db = $this->app->make(DB::class);
        $count = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if ($filter !== null && !str_contains($name, $filter)) {
                continue;
            }

            $seeder = require $file;
            if (!$seeder instanceof Seeder) {
                throw new RuntimeException("[$name] must return an instance of " . Seeder::class . '.');
            }
            $seeder->run($db);
            $this->output->line('  Seeded: ' . $name);
            $count++;
        }

        $count === 0 ? $this->output->warn('No seeders matched.') : $this->output->info("$count seeder(s) done.");
        return 0;
    }
}
