<?php
/**
 * @var Core\View\Template $this
 * @var array $menu
 * @var array|null $item
 * @var array $data
 * @var array<string, array> $translations
 * @var array<string, string> $formErrors
 */
$this->layout('admin/layouts/app');
$isEdit = $item !== null;
$action = $isEdit ? route('admin.menus.items.update', ['id' => $item['id']]) : route('admin.menus.items.store', ['id' => $menu['id']]);
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
$type = $data['type'] ?? 'page';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.menus.index', ['location' => $menu['location']])) ?>">მენიუები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($title) ?></li>
    </ol>
</nav>

<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger" style="max-width: 760px">
        <ul class="mb-0 ps-3"><?php foreach (array_unique($formErrors) as $message): ?><li><?= e($message) ?></li><?php endforeach ?></ul>
    </div>
<?php endif ?>

<form method="post" action="<?= e($action) ?>" class="bg-light rounded p-4" novalidate style="max-width: 760px" data-menu-item-form>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <div class="mb-3">
        <label class="form-label d-block">ტიპი</label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="type" id="type-page" value="page" <?= $type === 'page' ? 'checked' : '' ?> data-item-type>
            <label class="btn btn-outline-primary btn-sm" for="type-page"><i class="fa fa-file-alt me-1"></i>გვერდი</label>
            <input type="radio" class="btn-check" name="type" id="type-url" value="url" <?= $type === 'url' ? 'checked' : '' ?> data-item-type>
            <label class="btn btn-outline-primary btn-sm" for="type-url"><i class="fa fa-link me-1"></i>ბმული</label>
        </div>
    </div>

    <div class="mb-3" data-show-type="page">
        <label class="form-label" for="page_id">გვერდი</label>
        <select class="form-select<?= $invalid('page_id') ?>" id="page_id" name="page_id">
            <option value="">— აირჩიეთ —</option>
            <?php foreach ($pageOptions as $pageId => $pageTitle): ?>
                <option value="<?= e($pageId) ?>"<?= (int)($data['page_id'] ?? 0) === $pageId ? ' selected' : '' ?>><?= e($pageTitle) ?></option>
            <?php endforeach ?>
        </select>
        <?= $feedback('page_id') ?>
        <div class="form-text">წარწერა და მისამართი ყველა ენაზე გვერდიდან აიღება. წარწერის შეცვლა — ქვემოთ (არასავალდებულო).</div>
    </div>

    <div class="mb-3" data-show-type="url">
        <label class="form-label" for="url">ბმული</label>
        <input type="text" class="form-control<?= $invalid('url') ?>" id="url" name="url" maxlength="2000" value="<?= e($data['url'] ?? '') ?>"
               placeholder="https://..., /ka/..., #contact, mailto:, tel:">
        <?= $feedback('url') ?>
    </div>

    <?php foreach ($languages as $language): ?>
        <?php $code = $language['code']; $t = $translations[$code] ?? []; ?>
        <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2 fs-6 mb-0"><?= e($language['native_name']) ?></legend>
            <div class="mb-2">
                <label class="form-label small" for="label-<?= e($code) ?>">წარწერა<span data-show-type="url"><?= $code === $defaultLang ? ' *' : '' ?></span></label>
                <input type="text" class="form-control<?= $invalid("$code.label") ?>" id="label-<?= e($code) ?>" name="t[<?= e($code) ?>][label]" maxlength="100" value="<?= e($t['label'] ?? '') ?>">
                <?= $feedback("$code.label") ?>
                <div class="form-text" data-show-type="page">ცარიელი — გვერდის სათაური ამ ენაზე.</div>
                <?php if ($code !== $defaultLang): ?><div class="form-text" data-show-type="url">ცარიელი — ძირითადი ენის წარწერა.</div><?php endif ?>
            </div>
            <div data-show-type="url">
                <label class="form-label small" for="url-<?= e($code) ?>">ბმული ამ ენაზე (არასავალდებულო)</label>
                <input type="text" class="form-control<?= $invalid("$code.url") ?>" id="url-<?= e($code) ?>" name="t[<?= e($code) ?>][url]" maxlength="2000" value="<?= e($t['url'] ?? '') ?>">
                <?= $feedback("$code.url") ?>
            </div>
        </fieldset>
    <?php endforeach ?>

    <?php if ($parents !== []): ?>
        <div class="mb-3" style="max-width: 360px">
            <label class="form-label" for="parent_id">მშობელი პუნქტი</label>
            <select class="form-select<?= $invalid('parent_id') ?>" id="parent_id" name="parent_id"<?= $hasChildren ? ' disabled' : '' ?>>
                <option value="">— ზედა დონე —</option>
                <?php foreach ($parents as $parentId => $parentLabel): ?>
                    <option value="<?= e($parentId) ?>"<?= (int)($data['parent_id'] ?? 0) === $parentId ? ' selected' : '' ?>><?= e($parentLabel) ?></option>
                <?php endforeach ?>
            </select>
            <?= $feedback('parent_id') ?>
            <?php if ($hasChildren): ?><div class="form-text">პუნქტს ქვეპუნქტები აქვს, ამიტომ ზედა დონეზე რჩება.</div><?php endif ?>
        </div>
    <?php endif ?>

    <div class="form-check form-switch mb-2">
        <input type="hidden" name="new_tab" value="0">
        <input class="form-check-input" type="checkbox" id="new_tab" name="new_tab" value="1" <?= !empty($data['new_tab']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="new_tab">ახალ ფანჯარაში გახსნა</label>
    </div>
    <div class="form-check form-switch mb-4">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($data['is_active']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_active">აქტიური</label>
    </div>

    <button type="submit" class="btn btn-primary">შენახვა</button>
    <a href="<?= e(route('admin.menus.index', ['location' => $menu['location']])) ?>" class="btn btn-outline-secondary ms-2">გაუქმება</a>
</form>

<?php $this->start('scripts') ?>
<script>
    // ტიპის მიხედვით ველების ჩვენება
    (function () {
        var form = document.querySelector('[data-menu-item-form]');
        var update = function () {
            var type = form.querySelector('[data-item-type]:checked').value;
            form.querySelectorAll('[data-show-type]').forEach(function (el) { el.classList.toggle('d-none', el.dataset.showType !== type); });
        };
        form.querySelectorAll('[data-item-type]').forEach(function (el) { el.addEventListener('change', update); });
        update();
    })();
</script>
<?php $this->stop() ?>
