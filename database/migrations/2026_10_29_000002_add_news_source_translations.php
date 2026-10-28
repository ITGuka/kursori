<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;

// სიახლის სტატიის ატრიბუციის ტექსტები (collections/post) — production-ზე seed-ები არ ეშვება; ადმინში შეცვლილს არ ეხება
return new class extends Migration {
    public function up(DB $db): void
    {
        $lines = (require base_path('database/seeds/03_translations.php'))::LINES['blog'];
        $translations = app(Translation::class);
        foreach (['source', 'source_author'] as $key) {
            $translations->ensure('blog', $key, ['ka' => $lines[$key][0], 'en' => $lines[$key][1]]);
        }
        $translations->forgetCache();
    }

    public function down(DB $db): void
    {
        // თარგმანები ადმინიდან იმართება
    }
};
