<?php
/**
 * ბლოკის ფორმა — ველები config/blocks.php-ის სქემიდან (admin/blocks/field).
 * @var Core\View\Template $this
 * @var array $definition
 * @var array $settings
 * @var array<string, array> $content
 * @var array<string, string> $formErrors
 * @var list<string> $sharedPages საერთო ბლოკის გვერდები (config/blocks.php → shared)
 */
$this->layout('admin/layouts/app');
$isEdit = $block !== null;
$action = $isEdit ? route('admin.blocks.update', ['id' => $block['id']]) : route('admin.blocks.store', ['id' => $page['id']]);
?>
<?php $this->start('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/jodit@3.24.9/build/jodit.min.css" rel="stylesheet">
<?php $this->stop() ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.pages.index')) ?>">გვერდები</a></li>
        <li class="breadcrumb-item"><a href="<?= e(route('admin.pages.edit', ['id' => $page['id']])) ?>"><?= e($pageTitle) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($definition['label']) ?></li>
    </ol>
</nav>

<?php if ($sharedPages !== []): ?>
    <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
        <i class="icon-base bx bx-link mt-1"></i>
        <div>საერთო ბლოკი — ერთი და იგივეა ყველა გვერდზე, ცვლილება აისახება: <strong><?= e(implode(', ', $sharedPages)) ?></strong>.</div>
    </div>
<?php endif ?>
<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger" role="alert">ფორმაში შეცდომებია — შეამოწმეთ მონიშნული ველები.</div>
<?php endif ?>

<form method="post" action="<?= e($action) ?>" class="card block-form" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php else: ?>
        <input type="hidden" name="type" value="<?= e($type) ?>">
    <?php endif ?>

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary"><i class="icon-base <?= e($definition['icon'] ?? 'bx bx-square') ?>"></i></span>
            </div>
            <h5 class="card-title mb-0"><?= e($definition['label']) ?></h5>
        </div>
        <?= $this->insert('admin/partials/lang-switch') ?>
    </div>

    <div class="card-body">
        <div class="block-form-fields">
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
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <?php if ($isEdit): ?>
            <button type="submit" name="stay" value="1" class="btn btn-outline-primary">შენახვა და დარჩენა</button>
        <?php endif ?>
        <a href="<?= e(route('admin.pages.edit', ['id' => $page['id']])) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/jodit@3.24.9/build/jodit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= asset('admin/js/block-editor.js') ?>"></script>
<?php $this->stop() ?>
