<?php
/**
 * Kursori-ის სათაური (ქვესათაური ხაზებით + სათაური).
 * @var string $subtitle
 * @var string $heading
 * @var string $align left | center
 */
$align = ($align ?? 'center') === 'left' ? 'left' : 'center';
if (($subtitle ?? '') === '' && ($heading ?? '') === '') {
    return;
}
?>
<?php if ($align === 'center'): ?><div class="text-center"><?php endif ?>
<div class="title wow fadeInUp" data-wow-delay="0.1s">
    <div class="title-<?= $align ?>">
        <?php if (($subtitle ?? '') !== ''): ?><h5><?= e($subtitle) ?></h5><?php endif ?>
        <?php if (($heading ?? '') !== ''): ?><h2 class="h1"><?= e($heading) ?></h2><?php endif ?>
    </div>
</div>
<?php if ($align === 'center'): ?></div><?php endif ?>
