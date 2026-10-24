<?php
declare(strict_types=1);

namespace Core\Support;

use Core\Database\DB;

/**
 * მცდელობების მთვლელი ბაზაში (ცხრილი: limiter_key, hits, reset_at).
 * გასაღები ინახება sha256-ით, ამიტომ email/IP ცხრილში ღიად არ ჩანს.
 */
final class RateLimiter
{
    /** ბრჭყალებში, SQL-ში ჩასასმელად */
    private string $table;

    public function __construct(private readonly DB $db, private readonly string $tableName = 'rate_limits')
    {
        $this->table = DB::ident($tableName);
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    public function attempts(string $key): int
    {
        return (int)$this->db->value(
            "SELECT hits FROM {$this->table} WHERE limiter_key = ? AND reset_at > NOW()",
            [self::hash($key)],
        );
    }

    /** მცდელობის დათვლა; ფანჯარა ($decaySeconds) იწყება პირველი მცდელობიდან */
    public function hit(string $key, int $decaySeconds): int
    {
        // hits პირველია: MySQL მარცხნიდან ანახლებს, ასე ორივე IF ძველ reset_at-ს ხედავს
        $this->db->query(
            "INSERT INTO {$this->table} (limiter_key, hits, reset_at) VALUES (?, 1, NOW() + INTERVAL ? SECOND)
             ON DUPLICATE KEY UPDATE
                hits = IF(reset_at <= NOW(), 1, hits + 1),
                reset_at = IF(reset_at <= NOW(), VALUES(reset_at), reset_at)",
            [self::hash($key), $decaySeconds],
        );
        return $this->attempts($key);
    }

    /** წამები, სანამ ლიმიტი მოიხსნება */
    public function availableIn(string $key): int
    {
        $seconds = $this->db->value(
            "SELECT TIMESTAMPDIFF(SECOND, NOW(), reset_at) FROM {$this->table} WHERE limiter_key = ?",
            [self::hash($key)],
        );
        return max(0, (int)$seconds);
    }

    public function clear(string $key): void
    {
        $this->db->delete($this->tableName, 'limiter_key = ?', [self::hash($key)]);
    }

    /** ვადაგასული ჩანაწერების წაშლა */
    public function prune(): int
    {
        return $this->db->delete($this->tableName, 'reset_at <= NOW()');
    }

    private static function hash(string $key): string
    {
        return hash('sha256', $key);
    }
}
