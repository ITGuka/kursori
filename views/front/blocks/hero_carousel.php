<?php
/** @var array $data @var array $block */
$slides = array_values(array_filter($data['slides'], fn ($slide) => media_url($slide['image']) !== ''));
if ($slides === []) {
    return;
}
$carouselId = 'header-carousel-' . $block['id'];
?>
<div id="<?= e($carouselId) ?>" class="carousel slide header-carousel" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($slides as $i => $slide): ?>
            <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                <?= picture($slide['image'], 'large', ['class' => 'w-100', 'alt' => media_alt($slide['image']) ?: $slide['title']] +($i === 0 ? ['loading' => 'eager', 'fetchpriority' => 'high'] : [])) ?>
                <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                    <?php if ($slide['subtitle'] !== '' || $slide['title'] !== ''): ?>
                        <div class="title mx-5 px-5 animated slideInDown">
                            <div class="title-center">
                                <?php if ($slide['subtitle'] !== ''): ?><h5><?= e($slide['subtitle']) ?></h5><?php endif ?>
                                <?php if ($slide['title'] !== ''): ?><<?= $i === 0 ? 'h1' : 'h2' ?> class="display-1"><?= e($slide['title']) ?></<?= $i === 0 ? 'h1' : 'h2' ?>><?php endif ?>
                            </div>
                        </div>
                    <?php endif ?>
                    <?php if ($slide['text'] !== ''): ?>
                        <p class="fs-5 mb-5 animated slideInDown"><?= nl2br(e($slide['text'])) ?></p>
                    <?php endif ?>
                    <?php $hasPrimary = $slide['button_label'] !== '' && $slide['button_link']; ?>
                    <?php $hasSecondary = $slide['button2_label'] !== '' && $slide['button2_link']; ?>
                    <?php if ($hasPrimary || $hasSecondary): ?>
                        <div class="d-flex flex-wrap justify-content-center gap-3 animated slideInDown">
                            <?php if ($hasPrimary): ?>
                                <a<?= link_attrs($slide['button_link']) ?> class="btn btn-primary border-2 py-3 px-5"><?= e($slide['button_label']) ?></a>
                            <?php endif ?>
                            <?php if ($hasSecondary): ?>
                                <a<?= link_attrs($slide['button2_link']) ?> class="btn btn-outline-primary border-2 py-3 px-5"><?= e($slide['button2_label']) ?></a>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    <?php if (count($slides) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#<?= e($carouselId) ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden"><?= e(t('common.previous')) ?></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#<?= e($carouselId) ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden"><?= e(t('common.next')) ?></span>
        </button>
    <?php endif ?>
</div>
