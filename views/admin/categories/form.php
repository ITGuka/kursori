<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$isEdit = $category !== null;
$action = $isEdit ? route('admin.categories.update', ['id' => $category['id']]) : route('admin.categories.store', ['type' => $type]);
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>"><?= e($definition['label']) ?></a></li>
        <li class="breadcrumb-item"><a href="<?= e(route('admin.categories.index', ['type' => $type])) ?>">კატეგორიები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<form method="post" action="<?= e($action) ?>" class="bg-light rounded p-4" novalidate style="max-width: 760px">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <div class="d-flex justify-content-end mb-3"><?= $this->insert('admin/partials/lang-switch') ?></div>
    <?php foreach ($languages as $language): ?>
        <?php $code = $language['code']; $t = $translations[$code] ?? []; ?>
        <fieldset class="border rounded p-3 mb-3" data-slug-group data-lang="<?= e($code) ?>">
            <legend class="float-none w-auto px-2 fs-6 mb-0"><?= e($language['native_name']) ?><?= $code === $defaultLang ? ' *' : '' ?></legend>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small" for="name-<?= e($code) ?>">სახელი</label>
                    <input type="text" class="form-control<?= $invalid("$code.name") ?>" id="name-<?= e($code) ?>" name="t[<?= e($code) ?>][name]" maxlength="150"
                           value="<?= e($t['name'] ?? '') ?>" data-slug-source>
                    <?= $feedback("$code.name") ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label small" for="slug-<?= e($code) ?>">slug</label>
                    <input type="text" class="form-control<?= $invalid("$code.slug") ?>" id="slug-<?= e($code) ?>" name="t[<?= e($code) ?>][slug]" maxlength="150"
                           value="<?= e($t['slug'] ?? '') ?>" data-slug-target data-touched="<?= ($t['slug'] ?? '') !== '' ? '1' : '0' ?>">
                    <?= $feedback("$code.slug") ?>
                </div>
            </div>
        </fieldset>
    <?php endforeach ?>

    <div class="row g-3 mb-3">
        <div class="col-sm-8">
            <label class="form-label" for="parent_id">მშობელი კატეგორია</label>
            <select class="form-select<?= $invalid('parent_id') ?>" id="parent_id" name="parent_id"<?= $hasChildren ? ' disabled' : '' ?>>
                <option value="">— ზედა დონე —</option>
                <?php foreach ($parents as $id => $name): ?>
                    <option value="<?= e($id) ?>"<?= (int)$data['parent_id'] === $id ? ' selected' : '' ?>><?= e($name) ?></option>
                <?php endforeach ?>
            </select>
            <?= $feedback('parent_id') ?>
            <?php if ($hasChildren): ?><div class="form-text">კატეგორიას ქვეკატეგორიები აქვს, ამიტომ ზედა დონეზე რჩება.</div><?php endif ?>
        </div>
        <div class="col-sm-4">
            <label class="form-label" for="sort">რიგი</label>
            <input type="number" class="form-control" id="sort" name="sort" value="<?= e($data['sort']) ?>">
        </div>
    </div>

    <div class="form-check form-switch mb-4">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $data['is_active'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_active">აქტიური</label>
    </div>

    <button type="submit" class="btn btn-primary">შენახვა</button>
    <a href="<?= e(route('admin.categories.index', ['type' => $type])) ?>" class="btn btn-outline-secondary ms-2">გაუქმება</a>
</form>

<?php $this->start('scripts') ?>
<script src="<?= asset('admin/js/page-editor.js') ?>"></script>
<?php $this->stop() ?>
