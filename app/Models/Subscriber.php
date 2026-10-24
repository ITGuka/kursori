<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * Newsletter-ის გამომწერები (double opt-in): pending → (დასტური) active → (გაუქმება) unsubscribed.
 */
final class Subscriber
{
    public function __construct(private readonly DB $db)
    {
    }

    /**
     * გამოწერის მოთხოვნა: ახალი → pending; pending/unsubscribed → ისევ pending (დასტური ხელახლა); active → უცვლელი.
     * @return array{subscriber: array, needs_confirmation: bool}
     */
    public function request(string $email, string $lang, string $ip): array
    {
        $email = strtolower(trim($email));
        $existing = $this->db->first('SELECT * FROM subscribers WHERE email = ?', [$email]);
        if ($existing !== null && $existing['status'] === 'active') {
            return ['subscriber' => $existing, 'needs_confirmation' => false];
        }
        if ($existing !== null) {
            $this->db->update('subscribers', ['status' => 'pending', 'lang' => $lang, 'ip' => $ip], 'id = ?', [$existing['id']]);
            return ['subscriber' => $this->find((int)$existing['id']), 'needs_confirmation' => true];
        }
        $id = $this->db->insert('subscribers', ['email' => $email, 'lang' => $lang, 'token' => bin2hex(random_bytes(32)), 'ip' => $ip]);
        return ['subscriber' => $this->find($id), 'needs_confirmation' => true];
    }

    public function find(int $id): ?array
    {
        return $this->db->first('SELECT * FROM subscribers WHERE id = ?', [$id]);
    }

    public function findByToken(string $token): ?array
    {
        return preg_match('/^[a-f0-9]{64}$/', $token) ? $this->db->first('SELECT * FROM subscribers WHERE token = ?', [$token]) : null;
    }

    public function confirm(int $id): void
    {
        $this->db->query("UPDATE subscribers SET status = 'active', confirmed_at = NOW(), unsubscribed_at = NULL WHERE id = ?", [$id]);
    }

    public function unsubscribe(int $id): void
    {
        $this->db->query("UPDATE subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?", [$id]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('subscribers', 'id = ?', [$id]);
    }

    /**
     * @param array{status?: string, q?: string} $filters
     * @return array{rows: list<array>, total: int}
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$whereSql, $bindings] = $this->where($filters);
        $total = (int)$this->db->value("SELECT COUNT(*) FROM subscribers WHERE $whereSql", $bindings);
        $offset = max(0, ($page - 1) * $perPage);
        return ['rows' => $this->db->select("SELECT * FROM subscribers WHERE $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset", $bindings), 'total' => $total];
    }

    /** @return list<array> CSV ექსპორტისთვის */
    public function all(array $filters): array
    {
        [$whereSql, $bindings] = $this->where($filters);
        return $this->db->select("SELECT email, lang, status, confirmed_at, created_at FROM subscribers WHERE $whereSql ORDER BY id", $bindings);
    }

    /** @return array<string, int> status => რაოდენობა */
    public function counts(): array
    {
        $counts = ['pending' => 0, 'active' => 0, 'unsubscribed' => 0];
        foreach ($this->db->select('SELECT status, COUNT(*) n FROM subscribers GROUP BY status') as $row) {
            $counts[$row['status']] = (int)$row['n'];
        }
        return $counts;
    }

    /** @return array{0: string, 1: list<mixed>} */
    private function where(array $filters): array
    {
        $where = ['1 = 1'];
        $bindings = [];
        if (in_array($filters['status'] ?? '', ['pending', 'active', 'unsubscribed'], true)) {
            $where[] = 'status = ?';
            $bindings[] = $filters['status'];
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = 'email LIKE ?';
            $bindings[] = '%' . addcslashes($filters['q'], '%_\\') . '%';
        }
        return [implode(' AND ', $where), $bindings];
    }
}
