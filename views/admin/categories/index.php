<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>"><?= e($definition['label']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">კატეგორიები</li>
    </ol>
</nav>

<div class="bg-light rounded p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0"><?= e($title) ?></h6>
        <a href="<?= e(route('admin.categories.create', ['type' => $type])) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>დამატება</a>
    </div>
    <?php if ($rows === []): ?>
        <p class="text-muted mb-0">კატეგორიები ჯერ არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                <tr class="text-dark"><th>სახელი</th><th>ენები</th><th>ჩანაწერები</th><th>რიგი</th><th class="text-end">მოქმედება</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <span class="text-muted"><?= str_repeat('— ', $row['depth']) ?></span>
                            <a href="<?= e(route('admin.categories.edit', ['id' => $row['id']])) ?>"><?= e($row['name']) ?></a>
                            <?php if (!$row['is_active']): ?><span class="badge bg-secondary ms-1">გათიშული</span><?php endif ?>
                        </td>
                        <td>
                            <?php foreach ($languages as $language): ?>
                                <span class="badge <?= in_array($language['code'], $row['langs'], true) ? 'bg-success' : 'bg-light text-muted border' ?> text-uppercase"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td><a href="<?= e(route('admin.collections.index', ['type' => $type, 'category' => $row['id']])) ?>"><?= e($row['items']) ?></a></td>
                        <td><?= e($row['sort']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= e(route('admin.categories.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-pen"></i><span class="visually-hidden">რედაქტირება</span></a>
                            <form method="post" action="<?= e(route('admin.categories.destroy', ['id' => $row['id']])) ?>" class="d-inline"
                                  data-confirm="წავშალოთ კატეგორია „<?= e($row['name']) ?>“? მისი ჩანაწერები (<?= e($row['items']) ?>) კატეგორიის გარეშე დარჩება.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i><span class="visually-hidden">წაშლა</span></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
