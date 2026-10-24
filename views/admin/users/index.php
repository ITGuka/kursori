<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$selfId = auth()->id();
?>

<div class="bg-light rounded p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0"><?= e($title) ?></h6>
        <a href="<?= e(route('admin.users.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>დამატება</a>
    </div>
    <div class="table-responsive">
        <table class="table text-start align-middle table-bordered table-hover mb-0">
            <thead>
            <tr class="text-dark">
                <th>სახელი</th>
                <th>ელ-ფოსტა</th>
                <th>სტატუსი</th>
                <th>ბოლო შესვლა</th>
                <th class="text-end">მოქმედება</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['name']) ?><?= (int)$user['id'] === $selfId ? ' <span class="badge bg-secondary">თქვენ</span>' : '' ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">აქტიური</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">გათიშული</span>
                        <?php endif ?>
                    </td>
                    <td class="text-nowrap"><?= $user['last_login_at'] ? e(date('d.m.Y H:i', strtotime($user['last_login_at']))) : '—' ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= e(route('admin.users.edit', ['id' => $user['id']])) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-pen"></i><span class="visually-hidden">რედაქტირება</span>
                        </a>
                        <?php if ((int)$user['id'] !== $selfId): ?>
                            <form method="post" action="<?= e(route('admin.users.destroy', ['id' => $user['id']])) ?>" class="d-inline"
                                  data-confirm="წავშალოთ მომხმარებელი <?= e($user['email']) ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i><span class="visually-hidden">წაშლა</span>
                                </button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
