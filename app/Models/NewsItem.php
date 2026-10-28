<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;

/**
 * წამოღებული სიახლეები (news_items): new → approved → published, ან rejected / failed.
 */
final class NewsItem
{
    public function __construct(private readonly DB $db)
    {
    }

    /**
     * @param list<string> $externalIds
     * @return list<string> მათგან უკვე შენახული
     */
    public function existing(string $source, array $externalIds): array
    {
        if ($externalIds === []) {
            return [];
        }
        $placeholders = implode(', ', array_fill(0, count($externalIds), '?'));
        return array_map('strval', $this->db->column(
            "SELECT external_id FROM news_items WHERE source = ? AND external_id IN ($placeholders)",
            [$source, ...$externalIds],
        ));
    }

    /** @param array<string, mixed> $data news_items-ის სვეტები (tags — მასივი) */
    public function create(array $data): int
    {
        $data['tags'] = json_encode(array_values($data['tags'] ?? []), JSON_UNESCAPED_UNICODE);
        return $this->db->insert('news_items', $data);
    }
}
