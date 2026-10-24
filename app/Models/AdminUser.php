<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

final class AdminUser
{
    private const TABLE = 'admin_users';

    public function __construct(private readonly DB $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->select('SELECT id, name, email, is_active, last_login_at, created_at FROM admin_users ORDER BY name');
    }

    public function find(int $id): ?array
    {
        return $this->db->first('SELECT * FROM admin_users WHERE id = ?', [$id]);
    }

    public function count(): int
    {
        return (int)$this->db->value('SELECT COUNT(*) FROM admin_users');
    }

    public function countActive(): int
    {
        return (int)$this->db->value('SELECT COUNT(*) FROM admin_users WHERE is_active = 1');
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->first('SELECT * FROM admin_users WHERE email = ?', [self::normalizeEmail($email)]);
    }

    /** @param array{name: string, email: string, password: string, is_active?: bool} $data */
    public function create(array $data): int
    {
        return $this->db->insert(self::TABLE, [
            'name'          => trim($data['name']),
            'email'         => self::normalizeEmail($data['email']),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active'     => (int)($data['is_active'] ?? true),
        ]);
    }

    /** @param array{name?: string, email?: string, password?: string, is_active?: bool} $data ცარიელი პაროლი არ იცვლება */
    public function update(int $id, array $data): void
    {
        $fields = [];
        if (isset($data['name'])) {
            $fields['name'] = trim($data['name']);
        }
        if (isset($data['email'])) {
            $fields['email'] = self::normalizeEmail($data['email']);
        }
        if (($data['password'] ?? '') !== '') {
            $fields['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['is_active'])) {
            $fields['is_active'] = (int)$data['is_active'];
        }
        if ($fields !== []) {
            $this->db->update(self::TABLE, $fields, 'id = ?', [$id]);
        }
    }

    public function delete(int $id): void
    {
        $this->db->delete(self::TABLE, 'id = ?', [$id]);
    }

    public function recordLogin(int $id, string $ip): void
    {
        $this->db->update(self::TABLE, ['last_login_at' => date('Y-m-d H:i:s'), 'last_login_ip' => $ip], 'id = ?', [$id]);
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
