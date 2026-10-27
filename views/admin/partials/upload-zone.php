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
    <div class="d-flex flex-wrap align-items-center gap-4">
        <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-cloud-upload icon-lg"></i></span>
        </div>
        <div class="flex-grow-1">
            <div class="text-heading">გადმოიტანეთ ფაილები აქ ან <label for="<?= e($zoneId) ?>-input" class="link-primary fw-medium" role="button">აირჩიეთ</label></div>
            <small class="text-body-secondary">JPG, PNG, WebP, GIF, PDF · მაქს. <?= e(round(config('media.max_size') / 1048576)) ?> MB</small>
        </div>
        <div class="upload-zone-folder">
            <input type="text" class="form-control form-control-sm" placeholder="საქაღალდე (არასავალდებულო)" list="<?= e($zoneId) ?>-folders"
                   maxlength="50" data-upload-folder aria-label="საქაღალდე">
            <datalist id="<?= e($zoneId) ?>-folders">
                <?php foreach ($folders ?? [] as $folder): ?><option value="<?= e($folder) ?>"><?php endforeach ?>
            </datalist>
        </div>
    </div>
    <ul class="list-unstyled small mb-0 mt-3" data-upload-list></ul>
</div>
