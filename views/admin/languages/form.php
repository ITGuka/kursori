<?php
/** @var Core\View\Template $this */
/** @var array|null $language */
$this->layout('admin/layouts/app');

$isEdit = $language !== null;
$isDefault = $isEdit && $language['is_default'];
$action = $isEdit ? route('admin.languages.update', ['id' => $language['id']]) : route('admin.languages.store');
$active = old('is_active', $isEdit ? (string)$language['is_active'] : '1') === '1';
$default = old('is_default', $isEdit ? (string)$language['is_default'] : '0') === '1';

$invalid = fn (string $field) => errors($field) ? ' is-invalid' : '';
$feedback = fn (string $field) => ($error = errors($field)) ? '<div class="invalid-feedback">' . e($error) . '</div>' : '';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.languages.index')) ?>">ენები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<form method="post" action="<?= e($action) ?>" class="card form-narrow" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif ?>

    <h5 class="card-header"><?= e($title) ?></h5>

    <div class="card-body">
        <div class="mb-5">
            <label for="code" class="form-label">კოდი</label>
            <?php if ($isEdit): ?>
                <input type="text" class="form-control" id="code" value="<?= e($language['code']) ?>" disabled>
                <div class="form-text">კოდი არ იცვლება — მასზეა მიბმული URL-ები და თარგმანები.</div>
            <?php else: ?>
                <input type="text" class="form-control<?= $invalid('code') ?>" id="code" name="code" maxlength="2" required
                       value="<?= e(old('code')) ?>" placeholder="მაგ. ru">
                <div class="form-text">ორი ლათინური ასო (ISO 639-1). ჩანს URL-ში: /ru/...</div>
                <?= $feedback('code') ?>
            <?php endif ?>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label for="name" class="form-label">სახელი</label>
                <input type="text" class="form-control<?= $invalid('name') ?>" id="name" name="name" maxlength="50" required
                       value="<?= e(old('name', $language['name'] ?? '')) ?>" placeholder="მაგ. Russian">
                <?= $feedback('name') ?>
            </div>
            <div class="col-md-6">
                <label for="native_name" class="form-label">საკუთარ ენაზე</label>
                <input type="text" class="form-control<?= $invalid('native_name') ?>" id="native_name" name="native_name" maxlength="50" required
                       value="<?= e(old('native_name', $language['native_name'] ?? '')) ?>" placeholder="მაგ. Русский">
                <div class="form-text">ჩანს საიტის ენების გადამრთველში.</div>
                <?= $feedback('native_name') ?>
            </div>
        </div>

        <div class="mb-5" style="max-width: 160px">
            <label for="sort" class="form-label">რიგი</label>
            <input type="number" class="form-control<?= $invalid('sort') ?>" id="sort" name="sort"
                   value="<?= e(old('sort', $language['sort'] ?? '0')) ?>">
            <?= $feedback('sort') ?>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input<?= $invalid('is_active') ?>" type="checkbox" id="is_active" name="is_active" value="1" <?= $active ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">აქტიური (ჩანს საიტზე)</label>
            <?= $feedback('is_active') ?>
        </div>
        <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_default" value="0">
            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                <?= $default ? 'checked' : '' ?><?= $isDefault ? ' disabled' : '' ?>>
            <label class="form-check-label" for="is_default">ძირითადი ენა</label>
            <div class="form-text">
                <?= $isDefault ? 'ეს ძირითადი ენაა. შესაცვლელად სხვა ენა მონიშნეთ ძირითადად.' : 'ჩაირთვება „/“-დან გადამისამართება და ნაკლული თარგმანების ჩანაცვლება ამ ენით.' ?>
            </div>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <a href="<?= e(route('admin.languages.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>
