<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;

// ახალი footer-ის ტექსტები (production-ზე seed-ები არ ეშვება; ადმინში შეცვლილს არ ეხება)
return new class extends Migration {
    public function up(DB $db): void
    {
        $lines = (require base_path('database/seeds/03_translations.php'))::LINES['footer'];
        $translations = app(Translation::class);
        foreach ($lines as $key => [$ka, $en]) {
            $translations->ensure('footer', $key, ['ka' => $ka, 'en' => $en]);
        }
        $translations->forgetCache();
    }

    public function down(DB $db): void
    {
        // თარგმანები ადმინიდან იმართება
    }
};
