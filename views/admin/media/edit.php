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

<div class="row g-4">
    <div class="col-lg-5">
        <div class="bg-light rounded p-4 h-100">
            <div class="media-preview mb-3">
                <?php if ($isImage): ?>
                    <img src="<?= e($model->url($media, 'medium')) ?>" alt="" class="img-fluid rounded">
                <?php else: ?>
                    <a href="<?= e($model->url($media)) ?>" target="_blank" rel="noopener"><i class="fa fa-file-pdf fa-5x text-danger"></i></a>
                <?php endif ?>
            </div>
            <dl class="row small mb-0">
                <dt class="col-4">სახელი</dt><dd class="col-8 text-break"><?= e($media['original_name']) ?></dd>
                <dt class="col-4">ტიპი</dt><dd class="col-8"><?= e($media['mime']) ?></dd>
                <dt class="col-4">ზომა</dt><dd class="col-8"><?= e(Media::humanSize((int)$media['size'])) ?><?= $media['width'] ? e(' · ' . $media['width'] . '×' . $media['height'] . ' px') : '' ?></dd>
                <dt class="col-4">ატვირთულია</dt><dd class="col-8"><?= e(date('d.m.Y H:i', strtotime($media['created_at']))) ?></dd>
                <dt class="col-4">URL</dt>
                <dd class="col-8">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" value="<?= e($model->url($media)) ?>" readonly id="media-url">
                        <button class="btn btn-outline-secondary" type="button" data-copy="#media-url" title="კოპირება"><i class="fa fa-copy"></i></button>
                    </div>
                </dd>
                <?php if ($variants !== []): ?>
                    <dt class="col-4">ვარიანტები</dt>
                    <dd class="col-8">
                        <?php foreach ($variants as $name => $size): ?>
                            <a href="<?= e($model->url($media, $name)) ?>" target="_blank" rel="noopener" class="me-2"><?= e($name) ?></a><span class="text-muted me-3"><?= e($size['width'] . '×' . $size['height']) ?></span>
                        <?php endforeach ?>
                    </dd>
                <?php endif ?>
            </dl>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="bg-light rounded p-4 mb-4">
            <form method="post" action="<?= e(route('admin.media.update', ['id' => $media['id']])) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div class="mb-3" style="max-width: 320px">
                    <label class="form-label" for="folder">საქაღალდე</label>
                    <input type="text" class="form-control<?= errors('folder') ? ' is-invalid' : '' ?>" id="folder" name="folder" maxlength="50" list="folders"
                           value="<?= e(old('folder', $media['folder'] ?? '')) ?>">
                    <datalist id="folders"><?php foreach ($folders as $folder): ?><option value="<?= e($folder) ?>"><?php endforeach ?></datalist>
                    <?php if ($error = errors('folder')): ?><div class="invalid-feedback"><?= e($error) ?></div><?php endif ?>
                </div>

                <?php foreach ($languages as $language): ?>
                    <?php $code = $language['code']; ?>
                    <fieldset class="border rounded p-3 mb-3">
                        <legend class="float-none w-auto px-2 fs-6 mb-0"><?= e($language['native_name']) ?></legend>
                        <?php if ($isImage): ?>
                            <div class="mb-2">
                                <label class="form-label small" for="alt-<?= e($code) ?>">Alt ტექსტი</label>
                                <input type="text" class="form-control" id="alt-<?= e($code) ?>" name="texts[<?= e($code) ?>][alt]" maxlength="255"
                                       value="<?= e($text($code, 'alt')) ?>">
                            </div>
                        <?php endif ?>
                        <div>
                            <label class="form-label small" for="caption-<?= e($code) ?>">წარწერა</label>
                            <input type="text" class="form-control" id="caption-<?= e($code) ?>" name="texts[<?= e($code) ?>][caption]" maxlength="500"
                                   value="<?= e($text($code, 'caption')) ?>">
                        </div>
                    </fieldset>
                <?php endforeach ?>
                <?php if ($isImage): ?>
                    <p class="form-text">Alt ტექსტი აღწერს სურათს მათთვის, ვინც მას ვერ ხედავს (ეკრანის წამკითხველი, საძიებო სისტემები).</p>
                <?php endif ?>

                <button type="submit" class="btn btn-primary">შენახვა</button>
                <a href="<?= e(route('admin.media.index')) ?>" class="btn btn-outline-secondary ms-2">სიაში დაბრუნება</a>
            </form>
        </div>

        <div class="bg-light rounded p-4">
            <h6 class="mb-3">გამოყენება</h6>
            <?php if ($usages === []): ?>
                <p class="text-muted small">ფაილი არსად გამოიყენება.</p>
                <form method="post" action="<?= e(route('admin.media.destroy', ['id' => $media['id']])) ?>"
                      data-confirm="წავშალოთ „<?= e($media['original_name']) ?>“? ფაილი სამუდამოდ წაიშლება.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash me-1"></i>წაშლა</button>
                </form>
            <?php else: ?>
                <ul class="small mb-2">
                    <?php foreach ($usages as $usage): ?><li><?= e($usage) ?></li><?php endforeach ?>
                </ul>
                <p class="small text-muted mb-0">წასაშლელად ჯერ მოხსენით ზემოთ ჩამოთვლილ ადგილებში.</p>
            <?php endif ?>
        </div>
    </div>
</div>
