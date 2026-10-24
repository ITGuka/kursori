<?php
/** @var Core\View\Template $this @var array $data */
$items = array_values(array_filter($data['items'], fn ($item) => $item['quote'] !== ''));
if ($items === []) {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.3s">
            <?php foreach ($items as $item): ?>
                <?php $photo = media_url($item['image'], 'thumb'); ?>
                <div class="testimonial-item text-center"
                     data-dot="<?= e($photo !== '' ? '<img class="img-fluid" src="' . e($photo) . '" alt="">' : '<span></span>') ?>">
                    <p class="fs-5"><?= nl2br(e($item['quote'])) ?></p>
                    <?php if ($item['name'] !== ''): ?><h5 class="text-uppercase"><?= e($item['name']) ?></h5><?php endif ?>
                    <?php if ($item['profession'] !== ''): ?><span class="text-primary"><?= e($item['profession']) ?></span><?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
