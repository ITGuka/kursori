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
        'language.created'    => 'დაამატა ენა',
        'language.updated'    => 'შეცვალა ენა',
        'language.deleted'    => 'წაშალა ენა',
        'translation.created' => 'დაამატა თარგმანის გასაღები',
        'translation.updated' => 'შეცვალა თარგმანები',
        'translation.deleted' => 'წაშალა თარგმანის გასაღები',
        'settings.updated'    => 'შეცვალა პარამეტრები',
        'media.uploaded'      => 'ატვირთა ფაილი',
        'media.updated'       => 'შეცვალა ფაილის მონაცემები',
        'media.deleted'       => 'წაშალა ფაილი',
        'page.created'        => 'შექმნა გვერდი',
        'page.updated'        => 'შეცვალა გვერდი',
        'page.deleted'        => 'წაშალა გვერდი',
        'block.created'       => 'დაამატა ბლოკი',
        'block.updated'       => 'შეცვალა ბლოკი',
        'block.deleted'       => 'წაშალა ბლოკი',
        'menu.item_created'   => 'დაამატა მენიუს პუნქტი',
        'menu.item_updated'   => 'შეცვალა მენიუს პუნქტი',
        'menu.item_deleted'   => 'წაშალა მენიუს პუნქტი',
        'menu.reordered'      => 'შეცვალა მენიუს რიგი',
        'collection.created'  => 'დაამატა კოლექციის ჩანაწერი',
        'collection.updated'  => 'შეცვალა კოლექციის ჩანაწერი',
        'collection.deleted'  => 'წაშალა კოლექციის ჩანაწერი',
        'category.created'    => 'დაამატა კატეგორია',
        'category.updated'    => 'შეცვალა კატეგორია',
        'category.deleted'    => 'წაშალა კატეგორია',
        'inbox.deleted'       => 'წაშალა შეტყობინება',
        'subscriber.deleted'  => 'წაშალა გამომწერი',
        'campaign.created'    => 'შექმნა კამპანია',
        'campaign.updated'    => 'შეცვალა კამპანია',
        'campaign.deleted'    => 'წაშალა კამპანია',
        'campaign.started'    => 'გაუშვა კამპანია',
    ];

    /** Dashboard-ის „დეტალები“ სვეტისთვის: data-დან ყველაზე აღწერითი მნიშვნელობა */
    public static function summary(?string $json): string
    {
        $data = $json ? (array)json_decode($json, true) : [];
        return (string)($data['email'] ?? $data['code'] ?? $data['key'] ?? $data['name'] ?? $data['title'] ?? $data['block'] ?? $data['label'] ?? (isset($data['count']) ? $data['count'] . ' ჩანაწერი' : ''));
    }

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
