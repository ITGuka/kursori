<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * Newsletter-ის კამპანიები: draft → (გაშვება: ადრესატების სია newsletter_sends-ში) sending → sent.
 */
final class Campaign
{
    public function __construct(private readonly DB $db)
    {
    }

    /** @return list<array> სტატისტიკით (total, sent, failed) */
    public function all(): array
    {
        return $this->db->select(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM newsletter_sends s WHERE s.campaign_id = c.id) AS total,
                    (SELECT COUNT(*) FROM newsletter_sends s WHERE s.campaign_id = c.id AND s.status = 'sent') AS sent,
                    (SELECT COUNT(*) FROM newsletter_sends s WHERE s.campaign_id = c.id AND s.status = 'failed') AS failed
             FROM newsletter_campaigns c ORDER BY c.id DESC",
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->first('SELECT * FROM newsletter_campaigns WHERE id = ?', [$id]);
    }

    /** @param array{subject: string, body: string, lang: string} $data */
    public function save(?int $id, array $data, ?int $userId): int
    {
        if ($id === null) {
            return $this->db->insert('newsletter_campaigns', $data + ['created_by' => $userId]);
        }
        $this->db->update('newsletter_campaigns', $data, "id = ? AND status = 'draft'", [$id]);
        return $id;
    }

    public function delete(int $id): void
    {
        $this->db->delete('newsletter_campaigns', 'id = ?', [$id]);
    }

    /** ადრესატები: ამ მომენტის აქტიური გამომწერები (ენის ფილტრით); აბრუნებს რაოდენობას */
    public function start(int $id): int
    {
        return $this->db->transaction(function (DB $db) use ($id): int {
            $campaign = $db->first("SELECT * FROM newsletter_campaigns WHERE id = ? AND status = 'draft' FOR UPDATE", [$id]);
            if ($campaign === null) {
                return 0;
            }
            $db->query(
                "INSERT IGNORE INTO newsletter_sends (campaign_id, subscriber_id)
                 SELECT ?, id FROM subscribers WHERE status = 'active'" . ($campaign['lang'] !== '' ? ' AND lang = ?' : ''),
                $campaign['lang'] !== '' ? [$id, $campaign['lang']] : [$id],
            );
            $db->query("UPDATE newsletter_campaigns SET status = 'sending', started_at = NOW() WHERE id = ?", [$id]);
            return (int)$db->value('SELECT COUNT(*) FROM newsletter_sends WHERE campaign_id = ?', [$id]);
        });
    }

    /** @return list<array> მომდევნო პარტია: send + გამომწერის მონაცემები */
    public function pending(int $id, int $limit): array
    {
        return $this->db->select(
            "SELECT s.subscriber_id, sub.email, sub.lang, sub.token, sub.status AS subscriber_status
             FROM newsletter_sends s JOIN subscribers sub ON sub.id = s.subscriber_id
             WHERE s.campaign_id = ? AND s.status = 'pending' ORDER BY s.subscriber_id LIMIT " . max(1, $limit),
            [$id],
        );
    }

    public function markSend(int $campaignId, int $subscriberId, bool $ok, ?string $error = null): void
    {
        $this->db->query(
            'UPDATE newsletter_sends SET status = ?, error = ?, sent_at = NOW() WHERE campaign_id = ? AND subscriber_id = ?',
            [$ok ? 'sent' : 'failed', $error !== null ? mb_substr($error, 0, 500) : null, $campaignId, $subscriberId],
        );
    }

    /** @return array{total: int, sent: int, failed: int, pending: int} */
    public function stats(int $id): array
    {
        $stats = ['total' => 0, 'sent' => 0, 'failed' => 0, 'pending' => 0];
        foreach ($this->db->select('SELECT status, COUNT(*) n FROM newsletter_sends WHERE campaign_id = ? GROUP BY status', [$id]) as $row) {
            $stats[$row['status']] = (int)$row['n'];
            $stats['total'] += (int)$row['n'];
        }
        return $stats;
    }

    public function finish(int $id): void
    {
        $this->db->query("UPDATE newsletter_campaigns SET status = 'sent', sent_at = NOW() WHERE id = ? AND status = 'sending'", [$id]);
    }

    /** @return list<array> შეცდომით დასრულებული გაგზავნები (ადმინში საჩვენებლად) */
    public function failures(int $id, int $limit = 20): array
    {
        return $this->db->select(
            "SELECT sub.email, s.error FROM newsletter_sends s JOIN subscribers sub ON sub.id = s.subscriber_id
             WHERE s.campaign_id = ? AND s.status = 'failed' LIMIT " . max(1, $limit),
            [$id],
        );
    }
}
