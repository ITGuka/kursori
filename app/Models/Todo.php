<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * ადმინის პირადი to-do სია (Dashboard).
 */
final class Todo
{
    public const MAX = 100;

    public function __construct(private readonly DB $db)
    {
    }

    /** @return list<array> შეუსრულებელი ჯერ, შემდეგ — ახალი ზემოთ */
    public function forUser(int $userId): array
    {
        return $this->db->select('SELECT id, text, is_done FROM admin_todos WHERE user_id = ? ORDER BY is_done, id DESC LIMIT ' . self::MAX, [$userId]);
    }

    public function count(int $userId): int
    {
        return (int)$this->db->value('SELECT COUNT(*) FROM admin_todos WHERE user_id = ?', [$userId]);
    }

    public function create(int $userId, string $text): int
    {
        return $this->db->insert('admin_todos', ['user_id' => $userId, 'text' => $text]);
    }

    public function setDone(int $userId, int $id, bool $done): void
    {
        $this->db->update('admin_todos', ['is_done' => (int)$done], 'id = ? AND user_id = ?', [$id, $userId]);
    }

    public function delete(int $userId, int $id): void
    {
        $this->db->delete('admin_todos', 'id = ? AND user_id = ?', [$id, $userId]);
    }
}
