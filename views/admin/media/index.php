<?php
/** @var Core\View\Template $this */
use App\Models\Media;

$this->layout('admin/layouts/app');
$model = app(Media::class);
?>

<div class="card mb-6">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= e($total) ?>)</span></h5>
    </div>
    <div class="card-body">
        <?= $this->insert('admin/partials/upload-zone', ['zoneId' => 'library-upload', 'folders' => $folders, 'accept' => '']) ?>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="get" action="<?= e(route('admin.media.index')) ?>" class="list-filters d-flex flex-wrap align-items-center gap-3">
            <div class="input-group input-group-merge flex-grow-1" style="max-width: 360px">
                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                <input type="search" name="q" class="form-control" placeholder="ძებნა სახელში ან alt ტექსტში" value="<?= e($filters['q']) ?>" aria-label="ძებნა">
            </div>
            <select name="type" class="form-select w-auto" aria-label="ტიპი">
                <option value="">ყველა ტიპი</option>
                <option value="image"<?= $filters['type'] === 'image' ? ' selected' : '' ?>>სურათები</option>
                <option value="document"<?= $filters['type'] === 'document' ? ' selected' : '' ?>>დოკუმენტები</option>
            </select>
            <select name="folder" class="form-select w-auto" aria-label="საქაღალდე">
                <option value="">ყველა საქაღალდე</option>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?= e($folder) ?>"<?= $folder === $filters['folder'] ? ' selected' : '' ?>><?= e($folder) ?></option>
                <?php endforeach ?>
            </select>
            <button class="btn btn-outline-primary" type="submit">ფილტრი</button>
            <?php if (array_filter($filters)): ?>
                <a class="btn btn-link" href="<?= e(route('admin.media.index')) ?>">გასუფთავება</a>
            <?php endif ?>
        </form>
    </div>

    <div class="card-body">
        <?php if ($items === []): ?>
            <p class="mb-0 text-body-secondary">ფაილები ვერ მოიძებნა.</p>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($items as $item): ?>
                    <a class="media-card" href="<?= e(route('admin.media.edit', ['id' => $item['id']])) ?>" title="<?= e($item['original_name']) ?>">
                        <span class="media-card-thumb">
                            <?php if (Media::isImage($item)): ?>
                                <img src="<?= e($model->url($item, 'thumb')) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <i class="icon-base bx bxs-file-pdf icon-xl text-danger"></i>
                            <?php endif ?>
                        </span>
                        <span class="media-card-name text-heading"><?= e($item['original_name']) ?></span>
                        <span class="media-card-meta">
                            <?= $item['width'] ? e($item['width'] . '×' . $item['height']) . ' · ' : '' ?><?= e(Media::humanSize((int)$item['size'])) ?>
                            <?= $item['folder'] ? ' · <i class="icon-base bx bx-folder icon-xs"></i> ' . e($item['folder']) : '' ?>
                        </span>
                    </a>
                <?php endforeach ?>
            </div>
            <?php if ($pages > 1): ?><div class="mt-6"><?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?></div><?php endif ?>
        <?php endif ?>
    </div>
</div>
