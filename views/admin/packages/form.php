<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$isEdit = $package !== null;
$action = $isEdit ? route('admin.packages.update', ['id' => $package['id']]) : route('admin.packages.store');
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.packages.index')) ?>">პაკეტები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<form method="post" action="<?= e($action) ?>" class="card form-wide" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= $isEdit ? 'პაკეტის რედაქტირება' : 'ახალი პაკეტი' ?></h5>
        <?= $this->insert('admin/partials/lang-switch') ?>
    </div>

    <div class="card-body">
        <?php foreach ($languages as $language): ?>
            <?php $code = $language['code']; $t = $translations[$code] ?? []; $id = fn (string $name) => e("$name-$code"); $name = fn (string $field) => e("t[$code][$field]"); ?>
            <fieldset class="border rounded p-4 mb-4" data-slug-group data-lang="<?= e($code) ?>">
                <legend class="float-none w-auto px-2 fs-6 fw-medium text-heading mb-0"><?= e($language['native_name']) ?><?= $code === $defaultLang ? ' *' : '' ?></legend>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label" for="<?= $id('name') ?>">სახელი</label>
                        <input type="text" class="form-control<?= $invalid("$code.name") ?>" id="<?= $id('name') ?>" name="<?= $name('name') ?>" maxlength="150"
                               value="<?= e($t['name'] ?? '') ?>" data-slug-source>
                        <?= $feedback("$code.name") ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="<?= $id('slug') ?>">slug</label>
                        <input type="text" class="form-control<?= $invalid("$code.slug") ?>" id="<?= $id('slug') ?>" name="<?= $name('slug') ?>" maxlength="150"
                               value="<?= e($t['slug'] ?? '') ?>" data-slug-target data-touched="<?= ($t['slug'] ?? '') !== '' ? '1' : '0' ?>">
                        <?= $feedback("$code.slug") ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="<?= $id('price') ?>">ფასი</label>
                        <input type="text" class="form-control" id="<?= $id('price') ?>" name="<?= $name('price') ?>" maxlength="60" value="<?= e($t['price'] ?? '') ?>">
                        <div class="form-text">მაგ. „1 500 ₾-დან“.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="<?= $id('note') ?>">ფასის მინიშნება</label>
                        <input type="text" class="form-control" id="<?= $id('note') ?>" name="<?= $name('note') ?>" maxlength="150" value="<?= e($t['note'] ?? '') ?>">
                        <div class="form-text">მაგ. „ვადა: 3–5 დღე“.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="<?= $id('badge') ?>">ნიშანი</label>
                        <input type="text" class="form-control" id="<?= $id('badge') ?>" name="<?= $name('badge') ?>" maxlength="60" value="<?= e($t['badge'] ?? '') ?>">
                        <div class="form-text">მაგ. „პოპულარული“ — ბარათი გამოიკვეთება.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="<?= $id('features') ?>">რა შედის</label>
                        <textarea class="form-control" id="<?= $id('features') ?>" name="<?= $name('features') ?>" rows="6" maxlength="1500"><?= e($t['features'] ?? '') ?></textarea>
                        <div class="form-text">თითო პუნქტი ახალ ხაზზე.</div>
                    </div>
                </div>
            </fieldset>
        <?php endforeach ?>

        <div class="row g-4 mb-5">
            <div class="col-sm-8">
                <label class="form-label" for="code">კოდი</label>
                <input type="text" class="form-control<?= $invalid('code') ?>" id="code" name="code" maxlength="30" value="<?= e($data['code'] ?? '') ?>" required>
                <?= $feedback('code') ?>
                <div class="form-text">ბმულში: შეკვეთის გვერდი<code>?package=კოდი</code> (მაგ. start, business, shop). შეცვლისას ძველი ბმულები აღარ იმუშავებს.</div>
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="sort">რიგი</label>
                <input type="number" class="form-control" id="sort" name="sort" value="<?= e($data['sort']) ?>">
            </div>
        </div>

        <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $data['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">აქტიური</label>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <a href="<?= e(route('admin.packages.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>

<?php $this->start('scripts') ?>
<script src="<?= asset('admin/js/page-editor.js') ?>"></script>
<?php $this->stop() ?>
