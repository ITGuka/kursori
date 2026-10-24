<?php
/** @var Core\View\Template $this */
use App\Models\ActivityLog;

$this->layout('admin/layouts/app');
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="<?= e(route('admin.inbox.index', ['unread' => 1])) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset">
            <i class="fa fa-inbox fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">წაუკითხავი შეტყობინებები</p>
                <h6 class="mb-0"><?= e($unread) ?></h6>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= e(route('admin.newsletter.subscribers', ['status' => 'active'])) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset">
            <i class="fa fa-paper-plane fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">გამომწერები</p>
                <h6 class="mb-0"><?= e($subscribers) ?></h6>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
            <i class="fa fa-users fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">მომხმარებლები</p>
                <h6 class="mb-0"><?= e($usersCount) ?></h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
            <i class="fa fa-user-check fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">აქტიური</p>
                <h6 class="mb-0"><?= e($activeCount) ?></h6>
            </div>
        </div>
    </div>
</div>

<div class="bg-light rounded p-4">
    <h6 class="mb-4">ბოლო აქტივობა</h6>
    <?php if ($activity === []): ?>
        <p class="mb-0 text-muted">ჩანაწერები ჯერ არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                <tr class="text-dark">
                    <th>დრო</th>
                    <th>მომხმარებელი</th>
                    <th>ქმედება</th>
                    <th>დეტალები</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activity as $row): ?>
                    <tr>
                        <td class="text-nowrap"><?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?></td>
                        <td><?= e($row['user_name'] ?? '—') ?></td>
                        <td><?= e(ActivityLog::LABELS[$row['action']] ?? $row['action']) ?></td>
                        <td><?= e(ActivityLog::summary($row['data'])) ?></td>
                        <td><?= e($row['ip']) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
