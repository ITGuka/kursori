<?php
/**
 * ფორმის ველი მედიის ასარჩევად (ინახება id). JS: media.js — [data-media-input].
 *   $this->insert('admin/partials/media-input', ['name' => 'settings[general.logo]', 'value' => $id, 'inputId' => '...'])
 * @var string $name
 * @var mixed $value media id ან ''
 * @var string $inputId
 * @var string|null $type 'image' (default) ან '' — ნებისმიერი ფაილი
 * @var bool|null $invalid
 */
use App\Models\Media;

$type = $type ?? 'image';
$current = media($value);
$preview = $current !== null ? app(Media::class)->toArray($current) : null;
?>
<div class="media-input<?= !empty($invalid) ? ' is-invalid' : '' ?>" data-media-input data-type="<?= e($type) ?>">
    <input type="hidden" name="<?= e($name) ?>" id="<?= e($inputId) ?>" value="<?= e($current['id'] ?? '') ?>" data-media-value>
    <div class="media-input-preview" data-media-preview>
        <?php if ($preview !== null): ?>
            <?php if ($preview['image']): ?>
                <img src="<?= e($preview['thumb']) ?>" alt="">
            <?php else: ?>
                <i class="icon-base bx bxs-file-pdf icon-lg text-danger"></i>
            <?php endif ?>
        <?php else: ?>
            <i class="icon-base bx bx-image icon-lg text-body-secondary"></i>
        <?php endif ?>
    </div>
    <div class="media-input-body">
        <div class="small text-truncate" data-media-name><?= $preview !== null ? e($preview['name']) : '<span class="text-body-secondary">არ არის არჩეული</span>' ?></div>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-media-choose><i class="icon-base bx bx-images icon-sm me-1"></i>არჩევა</button>
            <button type="button" class="btn btn-sm btn-outline-secondary<?= $preview === null ? ' d-none' : '' ?>" data-media-clear>მოხსნა</button>
        </div>
    </div>
</div>
