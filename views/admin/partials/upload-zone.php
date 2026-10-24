<?php
/**
 * ფაილების ატვირთვის ზონა (drag & drop / არჩევა). JS: assets/admin/js/media.js
 * @var string $zoneId
 * @var string $accept მაგ. "image/*" ან ცარიელი
 * @var list<string> $folders
 */
$accept = $accept ?? '';
?>
<div class="upload-zone" data-upload-zone id="<?= e($zoneId) ?>">
    <input type="file" class="d-none" multiple<?= $accept !== '' ? ' accept="' . e($accept) . '"' : '' ?> data-upload-input id="<?= e($zoneId) ?>-input">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <i class="fa fa-cloud-upload-alt fa-2x text-primary"></i>
        <div class="flex-grow-1">
            <div>გადმოიტანეთ ფაილები აქ ან <label for="<?= e($zoneId) ?>-input" class="link-primary text-decoration-underline" role="button">აირჩიეთ</label></div>
            <div class="small text-muted">JPG, PNG, WebP, GIF, PDF · მაქს. <?= e(round(config('media.max_size') / 1048576)) ?> MB</div>
        </div>
        <div style="min-width: 180px">
            <input type="text" class="form-control form-control-sm" placeholder="საქაღალდე (არასავალდებულო)" list="<?= e($zoneId) ?>-folders"
                   maxlength="50" data-upload-folder aria-label="საქაღალდე">
            <datalist id="<?= e($zoneId) ?>-folders">
                <?php foreach ($folders ?? [] as $folder): ?><option value="<?= e($folder) ?>"><?php endforeach ?>
            </datalist>
        </div>
    </div>
    <ul class="list-unstyled small mb-0 mt-2" data-upload-list></ul>
</div>
