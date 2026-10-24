<?php /** @var Core\View\Template $this */ ?>
<?php $this->layout('admin/layouts/auth', ['title' => 'შესვლა']) ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="text-primary mb-0"><i class="fa fa-hashtag me-2"></i><?= e(config('app.name')) ?></h3>
    <h3 class="mb-0">შესვლა</h3>
</div>

<?php if ($error = errors('email')): ?>
    <div class="alert alert-danger py-2"><?= e($error) ?></div>
<?php endif ?>

<form method="post" action="<?= e(route('admin.login.attempt')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="form-floating mb-3">
        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
               value="<?= e(old('email')) ?>" autocomplete="username" required autofocus>
        <label for="email">ელ-ფოსტა</label>
    </div>
    <div class="form-floating mb-4">
        <input type="password" class="form-control" id="password" name="password" placeholder="პაროლი"
               autocomplete="current-password" required>
        <label for="password">პაროლი</label>
    </div>
    <button type="submit" class="btn btn-primary py-3 w-100">შესვლა</button>
</form>
