<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$isEdit = $redirect !== null;
$action = $isEdit ? route('admin.redirects.update', ['id' => $redirect['id']]) : route('admin.redirects.store');
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.redirects.index')) ?>">გადამისამართებები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<form method="post" action="<?= e($action) ?>" class="card form-narrow" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <h5 class="card-header"><?= $isEdit ? 'გადამისამართების რედაქტირება' : 'ახალი გადამისამართება' ?></h5>

    <div class="card-body">
        <div class="mb-5">
            <label class="form-label" for="from_path">ძველი მისამართი</label>
            <input type="text" class="form-control<?= $invalid('from_path') ?>" id="from_path" name="from_path" maxlength="500" value="<?= e($data['from_path']) ?>" placeholder="/ka/dzveli-gverdi">
            <?= $feedback('from_path') ?>
            <div class="form-text">საიტის ბილიკი, დომენის გარეშე. ? და # ნაწილი არ გაითვალისწინება.</div>
        </div>
        <div class="mb-5">
            <label class="form-label" for="to_url">ახალი მისამართი</label>
            <input type="text" class="form-control<?= $invalid('to_url') ?>" id="to_url" name="to_url" maxlength="2000" value="<?= e($data['to_url']) ?>" placeholder="/ka/akhali-gverdi ან https://...">
            <?= $feedback('to_url') ?>
        </div>
        <div class="mb-5" style="max-width: 360px">
            <label class="form-label" for="code">ტიპი</label>
            <select class="form-select" id="code" name="code">
                <option value="301"<?= (int)$data['code'] === 301 ? ' selected' : '' ?>>301 — სამუდამოდ (SEO-სთვის)</option>
                <option value="302"<?= (int)$data['code'] === 302 ? ' selected' : '' ?>>302 — დროებით</option>
            </select>
        </div>
        <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($data['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">აქტიური</label>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <a href="<?= e(route('admin.redirects.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>
