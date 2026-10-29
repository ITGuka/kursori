<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;

// შეტყობინებები (toast) და შეკვეთის ფორმების ფაილები: form ჯგუფის ახალი ტექსტები, common.close, order.attachments_hint
// (production-ზე seed-ები არ ეშვება; ensure არსებულს და ადმინში შეცვლილს არ ეხება)
return new class extends Migration {
    public function up(DB $db): void
    {
        $lines = (require base_path('database/seeds/03_translations.php'))::LINES;
        $translations = app(Translation::class);
        $keys = [['common', 'close'], ['order', 'attachments_hint']];
        foreach (array_keys($lines['form']) as $key) {
            $keys[] = ['form', $key];
        }
        foreach ($keys as [$group, $key]) {
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
