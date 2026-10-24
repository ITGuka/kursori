<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Seeder;

return new class extends Seeder {
    public function run(DB $db): void
    {
        $languages = [
            ['ka', 'Georgian', 'ქართული', 1, 1],
            ['en', 'English', 'English', 0, 2],
        ];

        foreach ($languages as [$code, $name, $native, $isDefault, $sort]) {
            $db->query(
                'INSERT INTO languages (code, name, native_name, is_default, is_active, sort) VALUES (?, ?, ?, ?, 1, ?)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), native_name = VALUES(native_name)',
                [$code, $name, $native, $isDefault, $sort],
            );
        }
    }
};
