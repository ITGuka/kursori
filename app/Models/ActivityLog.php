<?php
declare(strict_types=1);

namespace App\Models;

use Core\Auth\Auth;
use Core\Database\DB;
use Core\Http\Request;

/**
 * ადმინის ქმედებების ჟურნალი: ვინ, რა, რომელ ობიექტზე, საიდან.
 */
final class ActivityLog
{
    /** action => ადამიანური აღწერა Dashboard-ისთვის */
    public const LABELS = [
        'login'         => 'შევიდა სისტემაში',
        'logout'        => 'გავიდა სისტემიდან',
        'login_failed'  => 'შესვლის წარუმატებელი მცდელობა',
        'user.created'  => 'შექმნა მომხმარებელი',
        'user.updated'  => 'შეცვალა მომხმარებელი',
        'user.deleted'  => 'წაშალა მომხმარებელი',
    ];

    public function __construct(
        private readonly DB $db,
        private readonly Auth $auth,
        private readonly Request $request,
    ) {
    }

    public function log(string $action, ?string $entity = null, ?int $entityId = null, array $data = [], ?int $userId = null): void
    {
        $this->db->insert('activity_log', [
            'user_id'   => $userId ?? $this->auth->id(),
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'data'      => $data === [] ? null : json_encode($data, JSON_UNESCAPED_UNICODE),
            'ip'        => $this->request->ip(),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function recent(int $limit = 10): array
    {
        return $this->db->select(
            'SELECT l.*, u.name AS user_name FROM activity_log l
             LEFT JOIN admin_users u ON u.id = l.user_id
             ORDER BY l.id DESC LIMIT ' . max(1, $limit),
        );
    }
}
