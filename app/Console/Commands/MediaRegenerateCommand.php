<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Middleware\PageCache;
use App\Models\Media;
use Core\Console\Command;
use Core\Database\DB;
use Core\Support\Cache;

/**
 * სურათების ზომის ვარიანტებისა და WebP ასლების თავიდან შექმნა — config/media.php-ის შეცვლის შემდეგ
 * ან ძველი ატვირთვებისთვის, რომლებსაც WebP ჯერ არ აქვთ.
 */
final class MediaRegenerateCommand extends Command
{
    protected string $name = 'media:regenerate';
    protected string $description = 'Recreate image size variants and WebP copies';
    protected string $usage = '[--missing] (only images without WebP copies)';

    public function handle(array $args, array $options): int
    {
        $media = $this->app->make(Media::class);
        // WebP ორიგინალს ასლი არ სჭირდება — --missing მას ყოველ ჯერზე თავიდან არ დაამუშავებს
        $where = isset($options['missing']) ? " AND webp = 0 AND mime <> 'image/webp'" : '';
        $ids = $this->app->make(DB::class)->column("SELECT id FROM media WHERE mime IN ('image/jpeg', 'image/png', 'image/webp')$where ORDER BY id");

        foreach ($ids as $id) {
            $row = $media->regenerate((int)$id);
            $this->output->line(sprintf('#%d %s%s', $id, $row['original_name'] ?? '', !empty($row['webp']) ? ' + webp' : ''));
        }
        // ქეშირებულ გვერდებში ძველი srcset დარჩებოდა
        $this->app->make(Cache::class)->flush(PageCache::PREFIX);
        $this->output->info(count($ids) . ' image(s) processed.');
        return 0;
    }
}
