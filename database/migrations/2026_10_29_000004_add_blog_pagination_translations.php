<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;

// ბლოგის სიის პაგინაცია (blog_list → per_page): ტექსტები (production-ზე seed-ები არ ეშვება; ადმინში შეცვლილს არ ეხება)
// და 12 სტატია გვერდზე იმ ბლოკებში, რომლებიც ყველა სტატიას აჩვენებდა (limit = 0; მთავარის „ბოლო 3“ — უცვლელი)
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement("UPDATE page_blocks SET settings = JSON_SET(COALESCE(settings, '{}'), '$.per_page', 12)
            WHERE type = 'blog_list'
              AND CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(settings, '$.limit')), '0') AS UNSIGNED) = 0
              AND JSON_EXTRACT(settings, '$.per_page') IS NULL");

        $lines = (require base_path('database/seeds/03_translations.php'))::LINES['blog'];
        $translations = app(Translation::class);
        foreach (['pages', 'page', 'prev', 'next'] as $key) {
            $translations->ensure('blog', $key, ['ka' => $lines[$key][0], 'en' => $lines[$key][1]]);
        }
        $translations->forgetCache();
    }

    public function down(DB $db): void
    {
        // თარგმანები ადმინიდან იმართება
    }
};
