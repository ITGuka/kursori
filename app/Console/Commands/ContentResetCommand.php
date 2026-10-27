<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Design;
use Core\Console\Command;
use Core\Database\DB;
use Core\Database\Seeder;
use Core\Support\Cache;

/**
 * საიტის კონტენტის დაბრუნება საწყის (დემო) მდგომარეობაში: გვერდები, ბლოკები, მენიუს პუნქტები, კოლექციები, კატეგორიები,
 * ავტომატური გადამისამართებები — იშლება და seed-ები (05–10) თავიდან ეშვება.
 * არ ეხება: მედიას, ენებს, UI თარგმანებს, პარამეტრებს, მომხმარებლებს, Inbox-ს, newsletter-ს, დიზაინებს და მათ კატეგორიებს.
 */
final class ContentResetCommand extends Command
{
    protected string $name = 'content:reset';
    protected string $description = 'Replace pages, menus and collections with the starter content (destructive)';
    protected string $usage = '--force';

    private const SEEDS = ['05_demo_pages', '06_menus', '07_demo_collections', '08_site_settings', '09_privacy_page', '10_order_page'];

    public function handle(array $args, array $options): int
    {
        $db = $this->app->make(DB::class);
        $counts = [
            'pages'            => (int)$db->value('SELECT COUNT(*) FROM pages'),
            'collection items' => (int)$db->value('SELECT COUNT(*) FROM collection_items'),
            'categories'       => (int)$db->value('SELECT COUNT(*) FROM categories'),
            'menu items'       => (int)$db->value('SELECT COUNT(*) FROM menu_items'),
        ];
        if (!isset($options['force'])) {
            $this->output->warn('This deletes: ' . implode(', ', array_map(fn ($k, $v) => "$v $k", array_keys($counts), $counts)) . '.');
            $this->output->line('Media, languages, translations, settings, users, inbox and newsletter are kept. Run again with --force.');
            return 1;
        }

        $db->transaction(function (DB $db): void {
            $db->statement('DELETE FROM menu_items');
            $db->statement('DELETE FROM pages');
            $db->statement('DELETE FROM collection_items');
            // დიზაინები (შეკვეთის ფორმა) და მათი კატეგორიები რჩება
            $db->query('DELETE FROM categories WHERE collection <> ?', [Design::CATEGORIES]);
            $db->statement('DELETE FROM redirects WHERE is_auto = 1');
        });

        foreach (self::SEEDS as $name) {
            $seeder = require $this->app->basePath("database/seeds/$name.php");
            if ($seeder instanceof Seeder) {
                $seeder->run($db);
                $this->output->line("  Seeded: $name");
            }
        }
        $this->app->make(Cache::class)->flush();
        $this->output->info('Content reset done.');
        return 0;
    }
}
