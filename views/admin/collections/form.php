<?php
/**
 * @var Core\View\Template $this
 * @var array $definition
 * @var array|null $item
 * @var array $core category_id, image_id, is_active
 * @var array<string, array{title: string, slug: string}> $translations
 * @var array<string, string> $formErrors
 * @var Closure(string): string $routeBase
 */
$this->layout('admin/layouts/app');
$isEdit = $item !== null;
$action = $isEdit ? route('admin.collections.update', ['id' => $item['id']]) : route('admin.collections.store', ['type' => $type]);
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>
<?php $this->start('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/jodit@3.24.9/build/jodit.min.css" rel="stylesheet">
<?php $this->stop() ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>"><?= e($definition['label']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger" role="alert">ფორმაში შეცდომებია — შეამოწმეთ მონიშნული ველები.</div>
<?php endif ?>

<form method="post" action="<?= e($action) ?>" class="card block-form" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary"><i class="icon-base <?= e($definition['icon'] ?? 'bx bx-collection') ?>"></i></span>
            </div>
            <h5 class="card-title mb-0"><?= e($isEdit ? $definition['singular'] : 'ახალი: ' . $definition['singular']) ?></h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?= $this->insert('admin/partials/lang-switch') ?>
            <?php if ($publicUrl !== ''): ?>
                <a href="<?= e($publicUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-link-external me-1"></i>ნახვა</a>
            <?php endif ?>
        </div>
    </div>

    <div class="card-body">
        <div class="block-form-fields">
            <div class="mb-5">
                <label class="form-label d-block"><?= e($definition['title_label']) ?> * <span class="badge bg-label-info ms-1">თარგმნადი</span></label>
                <div class="row g-4">
                    <?php foreach ($languages as $language): ?>
                        <?php $code = $language['code']; ?>
                        <div class="col-md-6" data-slug-group data-lang="<?= e($code) ?>">
                            <label class="form-label small text-body-secondary mb-1" for="title-<?= e($code) ?>"><?= e($language['native_name']) ?></label>
                            <input type="text" class="form-control<?= $invalid("t.$code.title") ?>" id="title-<?= e($code) ?>" name="t[<?= e($code) ?>][title]" maxlength="190"
                                   value="<?= e($translations[$code]['title'] ?? '') ?>" data-slug-source>
                            <?= $feedback("t.$code.title") ?>
                            <?php if ($definition['public']): ?>
                                <div class="input-group input-group-sm mt-2">
                                    <span class="input-group-text">/<?= e($code) ?>/<?= e($routeBase($code)) ?>/</span>
                                    <input type="text" class="form-control<?= $invalid("t.$code.slug") ?>" name="t[<?= e($code) ?>][slug]" maxlength="150" aria-label="მისამართი"
                                           value="<?= e($translations[$code]['slug'] ?? '') ?>" data-slug-target data-touched="<?= ($translations[$code]['slug'] ?? '') !== '' ? '1' : '0' ?>">
                                    <?= $feedback("t.$code.slug") ?>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="t[<?= e($code) ?>][slug]" value="<?= e($translations[$code]['slug'] ?? '') ?>">
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="form-text">ცარიელი სათაური ენაზე — ჩანაწერი ამ ენაზე არ ჩანს.</div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label class="form-label">სურათი</label>
                    <?= $this->insert('admin/partials/media-input', ['name' => 'image_id', 'value' => $core['image_id'], 'inputId' => 'image_id', 'invalid' => isset($formErrors['image_id'])]) ?>
                    <?php if (isset($formErrors['image_id'])): ?><div class="invalid-feedback d-block"><?= e($formErrors['image_id']) ?></div><?php endif ?>
                </div>
                <?php if ($categoryOptions !== []): ?>
                    <div class="col-md-6">
                        <label class="form-label" for="category_id">კატეგორია</label>
                        <select class="form-select<?= $invalid('category_id') ?>" id="category_id" name="category_id">
                            <option value="">— კატეგორიის გარეშე —</option>
                            <?php foreach ($categoryOptions as $id => $name): ?>
                                <option value="<?= e($id) ?>"<?= (int)$core['category_id'] === $id ? ' selected' : '' ?>><?= e($name) ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= $feedback('category_id') ?>
                    </div>
                <?php elseif ($definition['categories']): ?>
                    <div class="col-md-6 small text-body-secondary pt-md-6">
                        კატეგორიები ჯერ არ არის — <a href="<?= e(route('admin.categories.create', ['type' => $type])) ?>">დამატება</a>.
                    </div>
                <?php endif ?>
            </div>

            <?php foreach ($definition['fields'] as $name => $field): ?>
                <?= $this->insert('admin/blocks/field', [
                    'name'        => $name,
                    'field'       => $field,
                    'settingsVal' => $settings[$name] ?? null,
                    'contentVals' => array_map(fn ($c) => $c[$name] ?? null, $content),
                    'path'        => $name,
                    'sName'       => "settings[$name]",
                    'cName'       => fn (string $lang) => "content[$lang][$name]",
                ]) ?>
            <?php endforeach ?>

            <div class="form-check form-switch mb-0">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $core['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">აქტიური (ჩანს საიტზე)</label>
            </div>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <?php if ($isEdit): ?><button type="submit" name="stay" value="1" class="btn btn-outline-primary">შენახვა და დარჩენა</button><?php endif ?>
        <a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/jodit@3.24.9/build/jodit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= asset('admin/js/block-editor.js') ?>"></script>
<script src="<?= asset('admin/js/page-editor.js') ?>"></script>
<?php $this->stop() ?>
