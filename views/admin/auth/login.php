<?php /** @var Core\View\Template $this */ ?>
<?php $this->layout('admin/layouts/auth', ['title' => 'შესვლა']) ?>

<h4 class="mb-1">მოგესალმებით! 👋</h4>
<p class="mb-6">შედით ანგარიშში საიტის სამართავად.</p>

<?php if ($error = errors('email')): ?>
    <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
<?php endif ?>

<form class="mb-4" method="post" action="<?= e(route('admin.login.attempt')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="mb-6">
        <label for="email" class="form-label">ელ-ფოსტა</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
               value="<?= e(old('email')) ?>" autocomplete="username" required autofocus>
    </div>
    <div class="mb-6 form-password-toggle">
        <label class="form-label" for="password">პაროლი</label>
        <div class="input-group input-group-merge">
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                   autocomplete="current-password" required>
            <span class="input-group-text cursor-pointer" role="button" tabindex="0" aria-label="პაროლის ჩვენება"><i class="icon-base bx bx-hide"></i></span>
        </div>
    </div>
    <div class="mb-8">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1"<?= old('remember') === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="remember">დამიმახსოვრე ამ კომპიუტერზე (30 დღე)</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary d-grid w-100">შესვლა</button>
</form>
