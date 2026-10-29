<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;

// შეტყობინებების (toast) ტექსტები: დახურვის ღილაკი და „შეამოწმეთ ველები“ (production-ზე seed-ები არ ეშვება; ადმინში შეცვლილს არ ეხება)
return new class extends Migration {
    public function up(DB $db): void
    {
        $lines = (require base_path('database/seeds/03_translations.php'))::LINES;
        $translations = app(Translation::class);
        foreach (['common' => 'close', 'form' => 'fix_errors'] as $group => $key) {
            [$ka, $en] = $lines[$group][$key];
            $translations->ensure($group, $key, ['ka' => $ka, 'en' => $en]);
        }
        $translations->forgetCache();
    }

    public function down(DB $db): void
    {
        // თარგმანები ადმინიდან იმართება
    }
};
