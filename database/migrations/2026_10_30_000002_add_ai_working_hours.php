<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;
use Core\Settings\Settings;

// კონტაქტი: სამუშაო საათები ადამიანებისთვის (working_hours) და AI ბოტებისთვის (working_hours_ai, ახალი).
// production-ზე seed-ები არ ეშვება: ახალი პარამეტრის საწყისი მნიშვნელობა (თუ ცარიელია) და წარწერები ფუტერისა და კონტაქტის ბლოკისთვის
return new class extends Migration {
    public function up(DB $db): void
    {
        $settings = app(Settings::class);
        $values = [];
        foreach (['ka' => '24/7, დღე და ღამე', 'en' => '24/7, day and night'] as $lang => $text) {
            if ($settings->raw('contact.working_hours_ai', $lang) === '') {
                $values['contact.working_hours_ai'][$lang] = $text;
            }
        }
        if ($values !== []) {
            $settings->save($values);
        }

        $translations = app(Translation::class);
        $lines = (require base_path('database/seeds/03_translations.php'))::LINES['contact'];
        foreach (['hours_people', 'hours_ai'] as $key) {
            $translations->ensure('contact', $key, ['ka' => $lines[$key][0], 'en' => $lines[$key][1]]);
        }
        $translations->forgetCache();
    }

    public function down(DB $db): void
    {
        // პარამეტრი და თარგმანები ადმინიდან იმართება
    }
};
