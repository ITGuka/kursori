<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;
use Core\Support\Url;

/**
 * 301/302 გადამისამართებები (მუშაობს მხოლოდ მაშინ, როცა მისამართზე გვერდი აღარ არის — 404-ის ნაცვლად)
 * და 404-ების ჟურნალი.
 */
final class Redirect
{
    private const LOG_LIMIT = 2000;

    public function __construct(private readonly DB $db)
    {
    }

    /** "/ka/Old-Page/?x=1#y" → "/ka/Old-Page" (base path-ის გარეშე; რეგისტრი ინახება) */
    public static function normalize(string $path): string
    {
        $path = (string)parse_url(trim($path), PHP_URL_PATH);
        $path = '/' . trim((string)preg_replace('#/+#', '/', $path), '/');
        return mb_substr($path, 0, 500);
    }

    /** to_url დასაშვებია: შიდა ბილიკი (/...) ან http(s) ბმული */
    public static function validTarget(string $url): bool
    {
        return Url::isSafeHref($url) && (str_starts_with($url, '/') || preg_match('~^https?://~i', $url));
    }

    /** @return array{url: string, code: int}|null აქტიური გადამისამართება (hits++) */
    public function resolve(string $path): ?array
    {
        $row = $this->db->first('SELECT * FROM redirects WHERE from_path = ? AND is_active = 1', [self::normalize($path)]);
        if ($row === null) {
            return null;
        }
        $this->db->query('UPDATE redirects SET hits = hits + 1, last_hit_at = NOW() WHERE id = ?', [$row['id']]);
        return ['url' => Url::resolve($row['to_url']), 'code' => (int)$row['code'] === 302 ? 302 : 301];
    }

    /** @return list<array> */
    public function all(string $search = ''): array
    {
        $where = $search !== '' ? 'WHERE from_path LIKE ? OR to_url LIKE ?' : '';
        $like = '%' . addcslashes($search, '%_\\') . '%';
        return $this->db->select("SELECT * FROM redirects $where ORDER BY id DESC", $search !== '' ? [$like, $like] : []);
    }

    public function find(int $id): ?array
    {
        return $this->db->first('SELECT * FROM redirects WHERE id = ?', [$id]);
    }

    public function fromExists(string $from, ?int $exceptId = null): bool
    {
        return (bool)$this->db->value('SELECT 1 FROM redirects WHERE from_path = ?' . ($exceptId ? ' AND id <> ?' : ''), $exceptId ? [$from, $exceptId] : [$from]);
    }

    /** @param array{from_path: string, to_url: string, code: int, is_active: bool} $data */
    public function save(?int $id, array $data): int
    {
        $row = ['from_path' => $data['from_path'], 'to_url' => $data['to_url'], 'code' => $data['code'], 'is_active' => (int)$data['is_active']];
        if ($id === null) {
            $id = $this->db->insert('redirects', $row);
        } else {
            $this->db->update('redirects', $row + ['is_auto' => 0], 'id = ?', [$id]);
        }
        // ამ მისამართზე 404 მოგვარდა
        $this->db->delete('not_found_log', 'path = ?', [$data['from_path']]);
        return $id;
    }

    public function delete(int $id): void
    {
        $this->db->delete('redirects', 'id = ?', [$id]);
    }

    /**
     * ავტომატური 301 (slug-ის შეცვლისას): ძველი → ახალი.
     * ჯაჭვები არ იქმნება: ძველზე მიმავალი გადამისამართებები ახალზე გადაიყვანება; ახალი მისამართიდან გადამისამართება იშლება.
     */
    public function addAuto(string $from, string $to): void
    {
        $from = self::normalize($from);
        $to = self::normalize($to);
        if ($from === $to) {
            return;
        }
        $this->db->transaction(function (DB $db) use ($from, $to): void {
            $db->update('redirects', ['to_url' => $to], 'to_url = ?', [$from]);
            $db->delete('redirects', 'from_path = ?', [$to]);
            $db->query(
                'INSERT INTO redirects (from_path, to_url, code, is_active, is_auto) VALUES (?, ?, 301, 1, 1)
                 ON DUPLICATE KEY UPDATE to_url = VALUES(to_url), code = 301, is_active = 1, is_auto = 1',
                [$from, $to],
            );
            // თავის თავზე მიმავალი (A → B → A) აღარ არის საჭირო
            $db->query('DELETE FROM redirects WHERE from_path = to_url');
        });
    }

    // ---- 404 ჟურნალი ----

    public function logNotFound(string $path, string $referer): void
    {
        $this->db->query(
            'INSERT INTO not_found_log (path, referer) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE hits = hits + 1, last_seen_at = NOW(), referer = COALESCE(NULLIF(VALUES(referer), \'\'), referer)',
            [self::normalize($path), mb_substr($referer, 0, 500)],
        );
        // ბოტების სკანირება ცხრილს უსასრულოდ რომ არ გაზრდის — ძველი ჩანაწერები იშლება
        if (random_int(1, 50) === 1) {
            $count = (int)$this->db->value('SELECT COUNT(*) FROM not_found_log');
            if ($count > self::LOG_LIMIT) {
                $this->db->query('DELETE FROM not_found_log ORDER BY last_seen_at ASC LIMIT ' . ($count - self::LOG_LIMIT));
            }
        }
    }

    /** @return list<array> */
    public function notFound(int $limit = 100): array
    {
        return $this->db->select('SELECT * FROM not_found_log ORDER BY hits DESC, last_seen_at DESC LIMIT ' . max(1, $limit));
    }

    public function deleteNotFound(?int $id = null): void
    {
        $id === null ? $this->db->statement('DELETE FROM not_found_log') : $this->db->delete('not_found_log', 'id = ?', [$id]);
    }
}
