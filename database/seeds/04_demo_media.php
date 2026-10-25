<?php
declare(strict_types=1);

use App\Models\Media;
use Core\Database\DB;
use Core\Database\Seeder;

// ვებ სტუდიის საწყისი სურათები მედია ბიბლიოთეკაში (საქაღალდე "demo"): resources/stock — Unsplash (Unsplash License).
// განმეორებით გაშვებისას უკვე იმპორტირებულს გამოტოვებს. საბოლოო კონტენტის შემდეგ შეიძლება წაიშალოს.
return new class extends Seeder {
    private const FOLDER = 'demo';

    public function run(DB $db): void
    {
        $media = app(Media::class);
        $existing = $db->column('SELECT original_name FROM media WHERE folder = ?', [self::FOLDER]);

        foreach (glob(base_path('resources/stock/*.{jpg,png}'), GLOB_BRACE) ?: [] as $file) {
            if (!in_array(basename($file), $existing, true)) {
                $media->import($file, self::FOLDER);
            }
        }
    }
};
