<?php
declare(strict_types=1);

use Core\Database\DB;
use Core\Database\Seeder;

// პირველი ადმინი .env-იდან (ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD); თუ ადმინი უკვე არსებობს — არაფერს აკეთებს.
// დამატებითი ან პაროლის აღდგენა: php bin/console admin:create
return new class extends Seeder {
    public function run(DB $db): void
    {
        if ((int)$db->value('SELECT COUNT(*) FROM admin_users') > 0) {
            return;
        }

        $email = strtolower(trim((string)env('ADMIN_EMAIL', '')));
        $password = (string)env('ADMIN_PASSWORD', '');
        if ($email === '' || strlen($password) < 8) {
            throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD (min 8 chars) in .env, or run: php bin/console admin:create');
        }

        $db->insert('admin_users', [
            'name'          => (string)env('ADMIN_NAME', 'Admin'),
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
};
