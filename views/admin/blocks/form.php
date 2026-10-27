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

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.pages.index')) ?>">გვერდები</a></li>
        <li class="breadcrumb-item"><a href="<?= e(route('admin.pages.edit', ['id' => $page['id']])) ?>"><?= e($pageTitle) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($definition['label']) ?></li>
    </ol>
</nav>

<?php if ($sharedPages !== []): ?>
    <div class="alert alert-info">
        <i class="fa fa-link me-1"></i>საერთო ბლოკი — ერთი და იგივეა ყველა გვერდზე, ცვლილება აისახება:
        <strong><?= e(implode(', ', $sharedPages)) ?></strong>.
    </div>
<?php endif ?>
<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger">ფორმაში შეცდომებია — შეამოწმეთ მონიშნული ველები.</div>
<?php endif ?>

<form method="post" action="<?= e($action) ?>" class="bg-light rounded p-4 block-form" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php else: ?>
        <input type="hidden" name="type" value="<?= e($type) ?>">
    <?php endif ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <h6 class="mb-0"><i class="<?= e($definition['icon'] ?? 'bx bx-square') ?> text-primary me-2"></i><?= e($definition['label']) ?></h6>
        <?= $this->insert('admin/partials/lang-switch') ?>
    </div>

    <div style="max-width: 1000px">
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

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary">შენახვა</button>
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
