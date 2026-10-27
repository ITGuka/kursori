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
// input-group-ის გარეთ მდგომი შეცდომა (პაროლის ველები) — ყოველთვის ჩანს
$feedbackBlock = fn (string $field) => ($error = errors($field)) ? '<div class="invalid-feedback d-block">' . e($error) . '</div>' : '';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.users.index')) ?>">მომხმარებლები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<form method="post" action="<?= e($action) ?>" class="card form-narrow" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif ?>

    <div class="card-header d-flex align-items-center gap-3">
        <?php if ($isEdit): ?>
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-label-primary"><?= e(mb_strtoupper(mb_substr((string)$user['name'], 0, 1))) ?></span>
            </div>
        <?php endif ?>
        <h5 class="card-title mb-0"><?= e($title) ?></h5>
    </div>

    <div class="card-body">
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label for="name" class="form-label">სახელი</label>
                <input type="text" class="form-control<?= $invalid('name') ?>" id="name" name="name" maxlength="100" required
                       value="<?= e(old('name', $user['name'] ?? '')) ?>">
                <?= $feedback('name') ?>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">ელ-ფოსტა</label>
                <input type="email" class="form-control<?= $invalid('email') ?>" id="email" name="email" maxlength="190" required
                       autocomplete="off" value="<?= e(old('email', $user['email'] ?? '')) ?>">
                <?= $feedback('email') ?>
            </div>
        </div>

        <fieldset class="border rounded p-4 mb-5">
            <legend class="float-none w-auto px-2 fs-6 fw-medium text-heading mb-0"><?= $isEdit ? 'პაროლის შეცვლა' : 'პაროლი' ?></legend>
            <?php if ($isEdit): ?>
                <p class="small text-body-secondary">დატოვეთ ცარიელი, თუ პაროლს არ ცვლით.</p>
            <?php endif ?>

            <?php if ($isSelf): ?>
                <div class="mb-4 form-password-toggle">
                    <label for="current_password" class="form-label">მიმდინარე პაროლი</label>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control<?= $invalid('current_password') ?>" id="current_password"
                               name="current_password" autocomplete="current-password">
                        <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                    </div>
                    <?= $feedbackBlock('current_password') ?>
                </div>
            <?php endif ?>

            <div class="row g-4">
                <div class="col-md-6 form-password-toggle">
                    <label for="password" class="form-label"><?= $isEdit ? 'ახალი პაროლი' : 'პაროლი' ?></label>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control<?= $invalid('password') ?>" id="password" name="password"
                               minlength="8" maxlength="72" autocomplete="new-password"<?= $isEdit ? '' : ' required' ?>>
                        <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                    </div>
                    <div class="form-text">მინიმუმ 8 სიმბოლო.</div>
                    <?= $feedbackBlock('password') ?>
                </div>
                <div class="col-md-6 form-password-toggle">
                    <label for="password_confirmation" class="form-label">გაიმეორეთ პაროლი</label>
                    <div class="input-group input-group-merge">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                               autocomplete="new-password">
                        <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input<?= $invalid('is_active') ?>" type="checkbox" id="is_active" name="is_active" value="1"
                <?= $active ? 'checked' : '' ?><?= $isSelf ? ' disabled' : '' ?>>
            <?php if ($isSelf): ?>
                <input type="hidden" name="is_active" value="1">
            <?php endif ?>
            <label class="form-check-label" for="is_active">აქტიური</label>
            <?= $feedback('is_active') ?>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <a href="<?= e(route('admin.users.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>
