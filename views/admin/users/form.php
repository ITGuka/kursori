<?php
/** @var Core\View\Template $this */
/** @var array|null $user */
$this->layout('admin/layouts/app');

$isEdit = $user !== null;
$isSelf = $isEdit && (int)$user['id'] === auth()->id();
$action = $isEdit ? route('admin.users.update', ['id' => $user['id']]) : route('admin.users.store');
$active = old('is_active', $isEdit ? (string)$user['is_active'] : '1') === '1';

// ველი + შეცდომის კლასი/ტექსტი
$invalid = fn (string $field) => errors($field) ? ' is-invalid' : '';
$feedback = fn (string $field) => ($error = errors($field)) ? '<div class="invalid-feedback">' . e($error) . '</div>' : '';
?>

<div class="bg-light rounded p-4">
    <h6 class="mb-4"><?= e($title) ?></h6>

    <form method="post" action="<?= e($action) ?>" novalidate style="max-width: 640px">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif ?>

        <div class="mb-3">
            <label for="name" class="form-label">სახელი</label>
            <input type="text" class="form-control<?= $invalid('name') ?>" id="name" name="name" maxlength="100" required
                   value="<?= e(old('name', $user['name'] ?? '')) ?>">
            <?= $feedback('name') ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">ელ-ფოსტა</label>
            <input type="email" class="form-control<?= $invalid('email') ?>" id="email" name="email" maxlength="190" required
                   autocomplete="off" value="<?= e(old('email', $user['email'] ?? '')) ?>">
            <?= $feedback('email') ?>
        </div>

        <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2 fs-6 mb-0"><?= $isEdit ? 'პაროლის შეცვლა' : 'პაროლი' ?></legend>
            <?php if ($isEdit): ?>
                <p class="small text-muted">დატოვეთ ცარიელი, თუ პაროლს არ ცვლით.</p>
            <?php endif ?>

            <?php if ($isSelf): ?>
                <div class="mb-3">
                    <label for="current_password" class="form-label">მიმდინარე პაროლი</label>
                    <input type="password" class="form-control<?= $invalid('current_password') ?>" id="current_password"
                           name="current_password" autocomplete="current-password">
                    <?= $feedback('current_password') ?>
                </div>
            <?php endif ?>

            <div class="mb-3">
                <label for="password" class="form-label"><?= $isEdit ? 'ახალი პაროლი' : 'პაროლი' ?></label>
                <input type="password" class="form-control<?= $invalid('password') ?>" id="password" name="password"
                       minlength="8" maxlength="72" autocomplete="new-password"<?= $isEdit ? '' : ' required' ?>>
                <div class="form-text">მინიმუმ 8 სიმბოლო.</div>
                <?= $feedback('password') ?>
            </div>
            <div>
                <label for="password_confirmation" class="form-label">გაიმეორეთ პაროლი</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                       autocomplete="new-password">
            </div>
        </fieldset>

        <div class="form-check form-switch mb-4">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input<?= $invalid('is_active') ?>" type="checkbox" id="is_active" name="is_active" value="1"
                <?= $active ? 'checked' : '' ?><?= $isSelf ? ' disabled' : '' ?>>
            <?php if ($isSelf): ?>
                <input type="hidden" name="is_active" value="1">
            <?php endif ?>
            <label class="form-check-label" for="is_active">აქტიური</label>
            <?= $feedback('is_active') ?>
        </div>

        <button type="submit" class="btn btn-primary">შენახვა</button>
        <a href="<?= e(route('admin.users.index')) ?>" class="btn btn-outline-secondary ms-2">გაუქმება</a>
    </form>
</div>
