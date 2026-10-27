<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= count($languages) ?>)</span></h5>
        <a href="<?= e(route('admin.languages.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>კოდი</th>
                <th>სახელი</th>
                <th>საკუთარ ენაზე</th>
                <th>სტატუსი</th>
                <th>რიგი</th>
                <th class="text-end">მოქმედება</th>
            </tr>
            </thead>
            <tbody class="table-border-bottom-0">
            <?php foreach ($languages as $language): ?>
                <tr>
                    <td><span class="badge bg-label-primary">/<?= e($language['code']) ?>/</span></td>
                    <td class="fw-medium text-heading"><?= e($language['name']) ?></td>
                    <td><?= e($language['native_name']) ?></td>
                    <td>
                        <?php if ($language['is_default']): ?><span class="badge bg-label-primary">ძირითადი</span><?php endif ?>
                        <?php if ($language['is_active']): ?>
                            <span class="badge bg-label-success">აქტიური</span>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">გათიშული</span>
                        <?php endif ?>
                    </td>
                    <td><?= e($language['sort']) ?></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a href="<?= e(route('admin.languages.edit', ['id' => $language['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება">
                                <i class="icon-base bx bx-edit-alt"></i>
                            </a>
                            <?php if (!$language['is_default']): ?>
                                <form method="post" action="<?= e(route('admin.languages.destroy', ['id' => $language['id']])) ?>"
                                      data-confirm="წავშალოთ ენა „<?= e($language['native_name']) ?>“? წაიშლება ამ ენის ყველა თარგმანი და პარამეტრი.">
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
    <div class="card-footer border-top">
        <small class="text-body-secondary">გათიშული ენის გვერდები საიტზე არ ჩანს (404), თარგმანები კი ინახება — შეგიძლიათ წინასწარ შეავსოთ.</small>
    </div>
</div>
