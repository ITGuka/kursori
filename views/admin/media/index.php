<?php
/** @var Core\View\Template $this */
use App\Models\Media;

$this->layout('admin/layouts/app');
$model = app(Media::class);
?>

<div class="bg-light rounded p-4 mb-4">
    <h6 class="mb-3"><?= e($title) ?> <span class="text-muted fw-normal">(<?= e($total) ?>)</span></h6>
    <?= $this->insert('admin/partials/upload-zone', ['zoneId' => 'library-upload', 'folders' => $folders, 'accept' => '']) ?>

    <form method="get" action="<?= e(route('admin.media.index')) ?>" class="row g-2 align-items-center mt-3">
        <div class="col-sm-4">
            <input type="search" name="q" class="form-control form-control-sm" placeholder="ძებნა სახელში ან alt ტექსტში" value="<?= e($filters['q']) ?>">
        </div>
        <div class="col-sm-3">
            <select name="type" class="form-select form-select-sm" aria-label="ტიპი">
                <option value="">ყველა ტიპი</option>
                <option value="image"<?= $filters['type'] === 'image' ? ' selected' : '' ?>>სურათები</option>
                <option value="document"<?= $filters['type'] === 'document' ? ' selected' : '' ?>>დოკუმენტები</option>
            </select>
        </div>
        <div class="col-sm-3">
            <select name="folder" class="form-select form-select-sm" aria-label="საქაღალდე">
                <option value="">ყველა საქაღალდე</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= e($folder) ?>"<?= $folder === $filters['folder'] ? ' selected' : '' ?>><?= e($folder) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary" type="submit">ფილტრი</button>
            <?php if (array_filter($filters)): ?>
                <a class="btn btn-sm btn-link" href="<?= e(route('admin.media.index')) ?>">გასუფთავება</a>
            <?php endif ?>
        </div>
    </form>
</div>

<div class="bg-light rounded p-4">
    <?php if ($items === []): ?>
        <p class="mb-0 text-muted">ფაილები ვერ მოიძებნა.</p>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($items as $item): ?>
                <a class="media-card" href="<?= e(route('admin.media.edit', ['id' => $item['id']])) ?>" title="<?= e($item['original_name']) ?>">
                    <span class="media-card-thumb">
                        <?php if (Media::isImage($item)): ?>
                            <img src="<?= e($model->url($item, 'thumb')) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <i class="fa fa-file-pdf fa-3x text-danger"></i>
                        <?php endif ?>
                    </span>
                    <span class="media-card-name"><?= e($item['original_name']) ?></span>
                    <span class="media-card-meta">
                        <?= $item['width'] ? e($item['width'] . '×' . $item['height']) . ' · ' : '' ?><?= e(Media::humanSize((int)$item['size'])) ?>
                        <?= $item['folder'] ? ' · <i class="fa fa-folder"></i> ' . e($item['folder']) : '' ?>
                    </span>
                </a>
            <?php endforeach ?>
        </div>
        <?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?>
    <?php endif ?>
</div>
