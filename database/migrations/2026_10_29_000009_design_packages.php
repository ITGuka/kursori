<?php
declare(strict_types=1);

use App\Models\Translation;
use Core\Database\DB;
use Core\Database\Migration;

// ფასების პაკეტები = დიზაინების ზედა დონის კატეგორიები (categories.code: start / business / shop), დარგები — მათი ქვეკატეგორიები.
// ფასი, მინიშნება, „რა შედის“, ნიშანი — package_translations. ფასების ბლოკის repeater-ის ტექსტები პაკეტებში გადადის,
// იქმნება ინდივიდუალური შეკვეთის გვერდი (production-ზე seed-ები არ ეშვება — ლოგიკა 10_order_page-შია)
return new class extends Migration {
    public function up(DB $db): void
    {
        $db->statement('ALTER TABLE categories ADD code VARCHAR(30) NULL AFTER collection, ADD UNIQUE KEY uq_categories_code (collection, code)');
        $db->statement('CREATE TABLE package_translations (
            category_id INT UNSIGNED NOT NULL,
            lang VARCHAR(5) NOT NULL,
            price VARCHAR(60) NOT NULL,
            note VARCHAR(150) NOT NULL,
            features TEXT NOT NULL,
            badge VARCHAR(60) NOT NULL,
            PRIMARY KEY (category_id, lang),
            CONSTRAINT fk_package_translations_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        ) ' . self::TABLE_OPTIONS);

        $orders = require base_path('database/seeds/10_order_page.php');
        // ცარიელი ბაზა (ახალი ინსტალაცია) → ყველაფერს seed-ები ქმნის
        if ((int)$db->value("SELECT COUNT(*) FROM categories WHERE collection = 'designs'") > 0) {
            $orders->packages($db, false);
            $orders->customPage($db);
        }

        $translations = app(Translation::class);
        foreach ((require base_path('database/seeds/03_translations.php'))::LINES as $group => $lines) {
            if (in_array($group, ['nav', 'order', 'custom'], true)) {
                foreach ($lines as $key => [$ka, $en]) {
                    $translations->ensure($group, $key, ['ka' => $ka, 'en' => $en]);
                }
            }
        }
        $translations->forgetCache();
    }

    public function down(DB $db): void
    {
        // კატეგორიების გადაწყობა და გვერდი რჩება (მონაცემია); სქემა ბრუნდება
        $db->statement('DROP TABLE IF EXISTS package_translations');
        $db->statement('ALTER TABLE categories DROP INDEX uq_categories_code, DROP COLUMN code');
    }
};
