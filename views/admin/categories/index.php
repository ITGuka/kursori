<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e($definition['index']) ?>"><?= e($definition['label']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">კატეგორიები</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= count($rows) ?>)</span></h5>
        <div class="d-flex flex-wrap gap-2">
            <?php if (isset($definition['packages'])): // დიზაინები: ზედა დონე = პაკეტი ?>
                <a href="<?= e($definition['packages']) ?>" class="btn btn-outline-primary"><i class="icon-base bx bx-package me-1"></i>პაკეტები</a>
            <?php endif ?>
            <a href="<?= e(route('admin.categories.create', ['type' => $type])) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
        </div>
    </div>
    <?php if ($rows === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">კატეგორიები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                <tr><th>სახელი</th><th>ენები</th><th>ჩანაწერები</th><th>რიგი</th><th class="text-end">მოქმედება</th></tr>
                </thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <?php if ($row['depth'] > 0): ?><span class="text-body-secondary" style="padding-left: <?= (int)$row['depth'] * 1.25 ?>rem">↳ </span><?php endif ?>
                            <a href="<?= e(route('admin.categories.edit', ['id' => $row['id']])) ?>" class="fw-medium text-heading"><?= e($row['name']) ?></a>
                            <?php if (isset($definition['packages']) && $row['depth'] === 0): ?><span class="badge bg-label-info ms-1">პაკეტი</span><?php endif ?>
                            <?php if (!$row['is_active']): ?><span class="badge bg-label-secondary ms-1">გათიშული</span><?php endif ?>
                        </td>
                        <td>
                            <?php foreach ($languages as $language): ?>
                                <span class="badge <?= in_array($language['code'], $row['langs'], true) ? 'bg-label-success' : 'bg-label-secondary opacity-50' ?> text-uppercase"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td><a href="<?= e($definition['index'] . '?category=' . $row['id']) ?>" class="badge bg-label-primary"><?= e($row['items']) ?></a></td>
                        <td><?= e($row['sort']) ?></td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="<?= e(route('admin.categories.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                                <form method="post" action="<?= e(route('admin.categories.destroy', ['id' => $row['id']])) ?>"
                                      data-confirm="წავშალოთ კატეგორია „<?= e($row['name']) ?>“? მისი ჩანაწერები (<?= e($row['items']) ?>) კატეგორიის გარეშე დარჩება.">
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
    <?php endif ?>
</div>
