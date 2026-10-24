<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;

final class MakeMigrationCommand extends Command
{
    protected string $name = 'make:migration';
    protected string $description = 'Create a new migration file';
    protected string $usage = '<name> (e.g. create_pages_table)';

    public function handle(array $args, array $options): int
    {
        $name = strtolower($args[0] ?? '');
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            $this->output->error('Usage: php bin/console make:migration create_pages_table');
            return 1;
        }

        $file = $this->app->basePath('database/migrations/' . date('Y_m_d_His') . '_' . $name . '.php');
        $table = preg_match('/^create_(\w+)_table$/', $name, $m) ? $m[1] : 'table_name';

        file_put_contents($file, <<<PHP
            <?php
            declare(strict_types=1);

            use Core\Database\DB;
            use Core\Database\Migration;

            return new class extends Migration {
                public function up(DB \$db): void
                {
                    \$db->statement('CREATE TABLE {$table} (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        ' . self::TIMESTAMPS . '
                    ) ' . self::TABLE_OPTIONS);
                }

                public function down(DB \$db): void
                {
                    \$db->statement('DROP TABLE IF EXISTS {$table}');
                }
            };

            PHP);

        $this->output->info('Created: database/migrations/' . basename($file));
        return 0;
    }
}
