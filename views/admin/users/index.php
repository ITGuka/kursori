<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$selfId = auth()->id();
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= count($users) ?>)</span></h5>
        <a href="<?= e(route('admin.users.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>მომხმარებელი</th>
                <th>სტატუსი</th>
                <th>ბოლო შესვლა</th>
                <th class="text-end">მოქმედება</th>
            </tr>
            </thead>
            <tbody class="table-border-bottom-0">
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-sm flex-shrink-0">
                                <span class="avatar-initial rounded-circle bg-label-primary"><?= e(mb_strtoupper(mb_substr((string)$user['name'], 0, 1))) ?></span>
                            </div>
                            <div>
                                <span class="fw-medium text-heading"><?= e($user['name']) ?></span>
                                <?= (int)$user['id'] === $selfId ? ' <span class="badge bg-label-info ms-1">თქვენ</span>' : '' ?>
                                <small class="d-block text-body-secondary"><?= e($user['email']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge bg-label-success">აქტიური</span>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">გათიშული</span>
                        <?php endif ?>
                    </td>
                    <td><?= $user['last_login_at'] ? e(date('d.m.Y H:i', strtotime($user['last_login_at']))) : '—' ?></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a href="<?= e(route('admin.users.edit', ['id' => $user['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება">
                                <i class="icon-base bx bx-edit-alt"></i>
                            </a>
                            <?php if ((int)$user['id'] !== $selfId): ?>
                                <form method="post" action="<?= e(route('admin.users.destroy', ['id' => $user['id']])) ?>"
                                      data-confirm="წავშალოთ მომხმარებელი <?= e($user['email']) ?>?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა">
                                        <i class="icon-base bx bx-trash"></i>
                                    </button>
                                </form>
                            <?php endif ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
