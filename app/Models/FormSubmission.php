<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * საჯარო ფორმებიდან შემოსული შეტყობინებები (Inbox).
 */
final class FormSubmission
{
    public function __construct(private readonly DB $db)
    {
    }

    /** @return array<string, array> ფორმების განსაზღვრებები (config/forms.php, ანტი-სპამის პარამეტრების გარეშე) */
    public static function forms(): array
    {
        return array_filter((array)config('forms', []), fn ($value) => is_array($value) && isset($value['fields']));
    }

    public function create(string $form, string $lang, array $data, string $pageUrl, string $ip, string $userAgent): int
    {
        return $this->db->insert('form_submissions', [
            'form'       => $form,
            'lang'       => $lang,
            'data'       => json_encode($data, JSON_UNESCAPED_UNICODE),
            'page_url'   => mb_substr($pageUrl, 0, 500),
            'ip'         => $ip,
            'user_agent' => mb_substr($userAgent, 0, 255),
        ]);
    }

    /** სტატუსები: მნიშვნელობა => [წარწერა, badge-ის კლასი] */
    public const STATUSES = [
        'new'         => ['ახალი', 'bg-primary'],
        'in_progress' => ['განხილვაში', 'bg-warning text-dark'],
        'done'        => ['დასრულებული', 'bg-success'],
    ];

    public function setStatus(int $id, string $status): void
    {
        $this->db->update('form_submissions', ['status' => $status], 'id = ?', [$id]);
    }

    /**
     * @param array{form?: string, unread?: bool, status?: string, q?: string} $filters
     * @return array{rows: list<array>, total: int}
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        $where = ['1 = 1'];
        $bindings = [];
        if (($filters['form'] ?? '') !== '') {
            $where[] = 'form = ?';
            $bindings[] = $filters['form'];
        }
        if (!empty($filters['unread'])) {
            $where[] = 'is_read = 0';
        }
        if (($filters['status'] ?? '') !== '') {
            $where[] = 'status = ?';
            $bindings[] = $filters['status'];
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = 'CAST(data AS CHAR) LIKE ?';
            $bindings[] = '%' . addcslashes($filters['q'], '%_\\') . '%';
        }
        $whereSql = implode(' AND ', $where);
        $total = (int)$this->db->value("SELECT COUNT(*) FROM form_submissions WHERE $whereSql", $bindings);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $this->db->select("SELECT * FROM form_submissions WHERE $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset", $bindings);
        foreach ($rows as &$row) {
            $row['data'] = (array)json_decode($row['data'], true);
        }
        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        $row = $this->db->first('SELECT * FROM form_submissions WHERE id = ?', [$id]);
        if ($row !== null) {
            $row['data'] = (array)json_decode($row['data'], true);
        }
        return $row;
    }

    public function setRead(int $id, bool $read): void
    {
        $this->db->update('form_submissions', ['is_read' => (int)$read], 'id = ?', [$id]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('form_submissions', 'id = ?', [$id]);
    }

    /** @return array<string, int> form => წაუკითხავი */
    public function unreadCounts(): array
    {
        $counts = [];
        foreach ($this->db->select('SELECT form, COUNT(*) n FROM form_submissions WHERE is_read = 0 GROUP BY form') as $row) {
            $counts[$row['form']] = (int)$row['n'];
        }
        return $counts;
    }
}
