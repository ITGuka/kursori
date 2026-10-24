<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
?>

<div class="bg-light rounded p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0"><?= e($title) ?></h6>
        <a href="<?= e(route('admin.languages.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>დამატება</a>
    </div>
    <div class="table-responsive">
        <table class="table text-start align-middle table-bordered table-hover mb-0">
            <thead>
            <tr class="text-dark">
                <th>კოდი</th>
                <th>სახელი</th>
                <th>საკუთარ ენაზე</th>
                <th>სტატუსი</th>
                <th>რიგი</th>
                <th class="text-end">მოქმედება</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($languages as $language): ?>
                <tr>
                    <td><code>/<?= e($language['code']) ?>/</code></td>
                    <td><?= e($language['name']) ?></td>
                    <td><?= e($language['native_name']) ?></td>
                    <td>
                        <?php if ($language['is_default']): ?><span class="badge bg-primary">ძირითადი</span><?php endif ?>
                        <?php if ($language['is_active']): ?>
                            <span class="badge bg-success">აქტიური</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">გათიშული</span>
                        <?php endif ?>
                    </td>
                    <td><?= e($language['sort']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= e(route('admin.languages.edit', ['id' => $language['id']])) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-pen"></i><span class="visually-hidden">რედაქტირება</span>
                        </a>
                        <?php if (!$language['is_default']): ?>
                            <form method="post" action="<?= e(route('admin.languages.destroy', ['id' => $language['id']])) ?>" class="d-inline"
                                  data-confirm="წავშალოთ ენა „<?= e($language['native_name']) ?>“? წაიშლება ამ ენის ყველა თარგმანი და პარამეტრი.">
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
    <p class="small text-muted mt-3 mb-0">გათიშული ენის გვერდები საიტზე არ ჩანს (404), თარგმანები კი ინახება — შეგიძლიათ წინასწარ შეავსოთ.</p>
</div>
