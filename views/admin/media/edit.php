<?php
/**
 * @var Core\View\Template $this
 * @var App\Models\Media $model
 */
use App\Models\Media;

$this->layout('admin/layouts/app');
$isImage = Media::isImage($media);
$oldTexts = old('texts', null);
$text = fn (string $lang, string $field) => (string)(is_array($oldTexts) ? ($oldTexts[$lang][$field] ?? '') : ($translations[$lang][$field] ?? ''));
$variants = Media::variants($media);
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.media.index')) ?>">მედია</a></li>
        <li class="breadcrumb-item active text-truncate" aria-current="page"><?= e($media['original_name']) ?></li>
    </ol>
</nav>

<div class="row g-6">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <div class="media-preview mb-6">
                    <?php if ($isImage): ?>
                        <img src="<?= e($model->url($media, 'medium')) ?>" alt="" class="img-fluid rounded">
                    <?php else: ?>
                        <a href="<?= e($model->url($media)) ?>" target="_blank" rel="noopener" aria-label="ფაილის გახსნა"><i class="icon-base bx bxs-file-pdf text-danger" style="font-size: 5rem"></i></a>
                    <?php endif ?>
                </div>
                <dl class="row small mb-0 g-2">
                    <dt class="col-4 fw-medium text-heading">სახელი</dt><dd class="col-8 text-break mb-0"><?= e($media['original_name']) ?></dd>
                    <dt class="col-4 fw-medium text-heading">ტიპი</dt><dd class="col-8 mb-0"><?= e($media['mime']) ?></dd>
                    <dt class="col-4 fw-medium text-heading">ზომა</dt><dd class="col-8 mb-0"><?= e(Media::humanSize((int)$media['size'])) ?><?= $media['width'] ? e(' · ' . $media['width'] . '×' . $media['height'] . ' px') : '' ?></dd>
                    <dt class="col-4 fw-medium text-heading">ატვირთულია</dt><dd class="col-8 mb-0"><?= e(date('d.m.Y H:i', strtotime($media['created_at']))) ?></dd>
                    <dt class="col-4 fw-medium text-heading">URL</dt>
                    <dd class="col-8 mb-0">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" value="<?= e($model->url($media)) ?>" readonly id="media-url" aria-label="URL">
                            <button class="btn btn-outline-secondary" type="button" data-copy="#media-url" title="კოპირება" aria-label="კოპირება"><i class="icon-base bx bx-copy"></i></button>
                        </div>
                    </dd>
                    <?php if ($variants !== []): ?>
                        <dt class="col-4 fw-medium text-heading">ვარიანტები</dt>
                        <dd class="col-8 mb-0">
                            <?php foreach ($variants as $name => $size): ?>
                                <a href="<?= e($model->url($media, $name)) ?>" target="_blank" rel="noopener" class="badge bg-label-primary me-1 mb-1"><?= e($name) ?> · <?= e($size['width'] . '×' . $size['height']) ?></a>
                            <?php endforeach ?>
                        </dd>
                    <?php endif ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <form method="post" action="<?= e(route('admin.media.update', ['id' => $media['id']])) ?>" class="card mb-6" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h5 class="card-title mb-0">აღწერა</h5>
                <?= $this->insert('admin/partials/lang-switch') ?>
            </div>

            <div class="card-body">
                <div class="mb-5" style="max-width: 320px">
                    <label class="form-label" for="folder">საქაღალდე</label>
                    <input type="text" class="form-control<?= errors('folder') ? ' is-invalid' : '' ?>" id="folder" name="folder" maxlength="50" list="folders"
                           value="<?= e(old('folder', $media['folder'] ?? '')) ?>">
                    <datalist id="folders"><?php foreach ($folders as $folder): ?><option value="<?= e($folder) ?>"><?php endforeach ?></datalist>
                    <?php if ($error = errors('folder')): ?><div class="invalid-feedback"><?= e($error) ?></div><?php endif ?>
                </div>

                <?php foreach ($languages as $language): ?>
                    <?php $code = $language['code']; ?>
                    <fieldset class="border rounded p-4 mb-4" data-lang="<?= e($code) ?>">
                        <legend class="float-none w-auto px-2 fs-6 fw-medium text-heading mb-0"><?= e($language['native_name']) ?></legend>
                        <?php if ($isImage): ?>
                            <div class="mb-4">
                                <label class="form-label" for="alt-<?= e($code) ?>">Alt ტექსტი</label>
                                <input type="text" class="form-control" id="alt-<?= e($code) ?>" name="texts[<?= e($code) ?>][alt]" maxlength="255"
                                       value="<?= e($text($code, 'alt')) ?>">
                            </div>
                        <?php endif ?>
                        <div>
                            <label class="form-label" for="caption-<?= e($code) ?>">წარწერა</label>
                            <input type="text" class="form-control" id="caption-<?= e($code) ?>" name="texts[<?= e($code) ?>][caption]" maxlength="500"
                                   value="<?= e($text($code, 'caption')) ?>">
                        </div>
                    </fieldset>
                <?php endforeach ?>
                <?php if ($isImage): ?>
                    <p class="form-text mb-0">Alt ტექსტი აღწერს სურათს მათთვის, ვინც მას ვერ ხედავს (ეკრანის წამკითხველი, საძიებო სისტემები).</p>
                <?php endif ?>
            </div>

            <div class="card-footer border-top d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
                <a href="<?= e(route('admin.media.index')) ?>" class="btn btn-outline-secondary">სიაში დაბრუნება</a>
            </div>
        </form>

        <div class="card">
            <h5 class="card-header">გამოყენება</h5>
            <div class="card-body">
                <?php if ($usages === []): ?>
                    <p class="text-body-secondary mb-4">ფაილი არსად გამოიყენება.</p>
                    <form method="post" action="<?= e(route('admin.media.destroy', ['id' => $media['id']])) ?>"
                          data-confirm="წავშალოთ „<?= e($media['original_name']) ?>“? ფაილი სამუდამოდ წაიშლება.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger"><i class="icon-base bx bx-trash me-1"></i>წაშლა</button>
                    </form>
                <?php else: ?>
                    <ul class="list-group list-group-flush mb-4">
                        <?php foreach ($usages as $usage): ?><li class="list-group-item px-0"><i class="icon-base bx bx-link icon-sm text-primary me-2"></i><?= e($usage) ?></li><?php endforeach ?>
                    </ul>
                    <small class="text-body-secondary">წასაშლელად ჯერ მოხსენით ზემოთ ჩამოთვლილ ადგილებში.</small>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
