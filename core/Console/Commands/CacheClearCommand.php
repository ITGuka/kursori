<?php
declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class CacheClearCommand extends Command
{
    protected string $name = 'cache:clear';
    protected string $description = 'Delete everything in storage/cache';

    public function handle(array $args, array $options): int
    {
        $dir = $this->app->basePath('storage/cache');
        if (!is_dir($dir)) {
            $this->output->info('Cache is empty.');
            return 0;
        }

        $removed = 0;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            if ($item->getFilename() === '.gitkeep') {
                continue;
            }
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
                $removed++;
            }
        }

        $this->output->info("Cache cleared ($removed file(s)).");
        return 0;
    }
}
