<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Support\Crypt;

final class KeyGenerateCommand extends Command
{
    protected string $name = 'key:generate';
    protected string $description = 'Set APP_KEY in .env (encryption key for stored secrets)';
    protected string $usage = '[--force]  (changing the key makes already encrypted values unreadable)';

    public function handle(array $args, array $options): int
    {
        $file = $this->app->basePath('.env');
        $content = is_file($file) ? (string)file_get_contents($file) : '';

        if (preg_match('/^APP_KEY=(.+)$/m', $content, $m) && trim($m[1]) !== '' && !isset($options['force'])) {
            $this->output->warn('APP_KEY already set. Use --force to replace it (encrypted settings will need to be re-entered).');
            return 1;
        }

        $line = 'APP_KEY=' . Crypt::generateKey();
        $content = preg_match('/^APP_KEY=.*$/m', $content)
            ? (string)preg_replace('/^APP_KEY=.*$/m', $line, $content)
            : rtrim($content) . PHP_EOL . $line . PHP_EOL;
        file_put_contents($file, $content);

        $this->output->info('APP_KEY set in .env.');
        return 0;
    }
}
