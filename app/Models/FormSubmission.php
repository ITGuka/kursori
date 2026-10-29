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

    /**
     * შემოსული მონაცემები საჩვენებლად (Inbox, წერილები): ფორმის ველების რიგით, ნაბიჯებად (steps, თუ აქვს), options — წარწერით.
     * hide_empty — ცარიელი ველები გამოტოვებულია; მასივი — წარწერები მძიმით; type links — ბმულების სია; type file — ფაილის მონაცემები.
     * @return list<array{title: ?string, rows: list<array{name: string, label: string, value: string, multiline: bool, links: list<string>, file: ?array}>}>
     */
    public static function present(array $definition, array $data, string $lang): array
    {
        $fields = $definition['fields'] ?? [];
        $sections = [];
        // ფორმის ველების რიგით (MySQL JSON-ში გასაღებები სიგრძით ლაგდება), შემდეგ — დანარჩენი
        foreach (array_replace(array_intersect_key($fields, $data), $data) as $name => $value) {
            $field = $fields[$name] ?? [];
            $type = $field['type'] ?? '';
            $label = fn (mixed $item) => isset($field['options'][$item]) ? t($field['options'][$item], [], $lang) : (string)$item;
            $row = ['name' => $name, 'label' => isset($field['label']) ? t($field['label'], [], $lang) : $name, 'value' => '',
                    'multiline' => !empty($field['multiline']), 'links' => [], 'file' => null];
            if ($type === 'file') {
                $row['file'] = is_array($value) && isset($value['name']) ? $value : null;
                $row['value'] = (string)($row['file']['name'] ?? '');
            } elseif ($type === 'links') {
                $row['links'] = array_values(array_filter((array)$value, 'is_string'));
                $row['value'] = implode("\n", $row['links']);
            } else {
                $row['value'] = is_array($value) ? implode(', ', array_map($label, $value)) : $label((string)$value);
            }
            if (!empty($definition['hide_empty']) && $row['value'] === '') {
                continue;
            }
            $step = $field['step'] ?? '';
            $sections[$step] ??= ['title' => isset($definition['steps'][$step]) ? t($definition['steps'][$step], [], $lang) : null, 'rows' => []];
            $sections[$step]['rows'][] = $row;
        }
        // ნაბიჯების რიგით; ნაბიჯის გარეშე (ძველი ველები) — ბოლოს
        $order = array_flip(array_keys($definition['steps'] ?? []));
        uksort($sections, fn ($a, $b) => ($order[$a] ?? PHP_INT_MAX) <=> ($order[$b] ?? PHP_INT_MAX));
        return array_values($sections);
    }

    /** ფაილის ზომა წასაკითხად: 1.4 MB, 820 KB */
    public static function fileSize(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1) . ' MB' : max(1, (int)round($bytes / 1024)) . ' KB';
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
        'new'         => ['ახალი', 'bg-label-primary'],
        'in_progress' => ['განხილვაში', 'bg-label-warning'],
        'done'        => ['დასრულებული', 'bg-label-success'],
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
