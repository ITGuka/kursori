<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AdminUser;
use Core\Console\Command;
use Core\Support\Validator;

/**
 * ადმინის შექმნა ან არსებულის პაროლის აღდგენა (ასევე ააქტიურებს ანგარიშს).
 * --password-ის გარეშე შემთხვევითი პაროლი გენერირდება და ერთხელ გამოჩნდება.
 */
final class CreateAdminCommand extends Command
{
    protected string $name = 'admin:create';
    protected string $description = 'Create an admin user, or reset the password of an existing one';
    protected string $usage = '--email=<email> [--name=<name>] [--password=<password>]';

    public function handle(array $args, array $options): int
    {
        $email = AdminUser::normalizeEmail(is_string($options['email'] ?? null) ? $options['email'] : '');
        $name = is_string($options['name'] ?? null) ? trim($options['name']) : 'Admin';
        $generated = !is_string($options['password'] ?? null);
        $password = $generated ? self::randomPassword() : $options['password'];

        $validator = Validator::make(compact('email', 'name', 'password'), [
            'email'    => 'required|email|max:190',
            'name'     => 'required|max:100',
            'password' => 'required|min:8|max:72',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors() as $error) {
                $this->output->error($error);
            }
            $this->output->line('Usage: php bin/console admin:create ' . $this->usage);
            return 1;
        }

        $users = $this->app->make(AdminUser::class);
        $existing = $users->findByEmail($email);
        if ($existing !== null) {
            $users->update((int)$existing['id'], ['password' => $password, 'is_active' => true]);
            $this->output->info("Password reset for $email.");
        } else {
            $users->create(['name' => $name, 'email' => $email, 'password' => $password]);
            $this->output->info("Admin $email created.");
        }

        if ($generated) {
            $this->output->line('Password: ' . $this->output->highlight($password));
            $this->output->warn('Save it now — it will not be shown again.');
        }
        return 0;
    }

    private static function randomPassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < 16; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $password;
    }
}
