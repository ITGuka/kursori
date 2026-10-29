<?php
/** @var Core\View\Template $this */
use App\Models\Design;

$this->layout('admin/layouts/app');
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-package"></i></span>
            </div>
            <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= count($rows) ?>)</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= e(route('admin.categories.index', ['type' => Design::CATEGORIES])) ?>" class="btn btn-outline-primary"><i class="icon-base bx bx-folder me-1"></i>დარგები</a>
            <a href="<?= e(route('admin.packages.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
        </div>
    </div>
    <?php if ($rows === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">პაკეტები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                <tr><th>სახელი</th><th>კოდი</th><th>ფასი</th><th>ენები</th><th>დარგები</th><th>დიზაინები</th><th>რიგი</th><th class="text-end">მოქმედება</th></tr>
                </thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <a href="<?= e(route('admin.packages.edit', ['id' => $row['id']])) ?>" class="fw-medium text-heading"><?= e($row['name']) ?></a>
                            <?php if (!$row['is_active']): ?><span class="badge bg-label-secondary ms-1">გათიშული</span><?php endif ?>
                        </td>
                        <td><?php if ($row['code']): ?><code><?= e($row['code']) ?></code><?php else: ?><span class="badge bg-label-warning">კოდი არ აქვს</span><?php endif ?></td>
                        <td><?= $row['price'] !== '' ? e($row['price']) : '<span class="text-body-secondary">—</span>' ?></td>
                        <td>
                            <?php foreach ($languages as $language): ?>
                                <span class="badge <?= in_array($language['code'], $row['langs'], true) ? 'bg-label-success' : 'bg-label-secondary opacity-50' ?> text-uppercase"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td><a href="<?= e(route('admin.categories.index', ['type' => Design::CATEGORIES])) ?>" class="badge bg-label-primary"><?= e($row['children']) ?></a></td>
                        <td><a href="<?= e(route('admin.designs.index') . '?category=' . $row['id']) ?>" class="badge bg-label-primary"><?= e($row['designs']) ?></a></td>
                        <td><?= e($row['sort']) ?></td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="<?= e(route('admin.packages.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                                <form method="post" action="<?= e(route('admin.packages.destroy', ['id' => $row['id']])) ?>"
                                      data-confirm="წავშალოთ პაკეტი „<?= e($row['name']) ?>“?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top">
            <small class="text-body-secondary">
                პაკეტები ჩანს ფასების ბლოკში და შეკვეთის ფორმაში (ბმული: შეკვეთის გვერდი<code>?package=კოდი</code>). დარგები — პაკეტის ქვეკატეგორიები.
            </small>
        </div>
    <?php endif ?>
</div>
