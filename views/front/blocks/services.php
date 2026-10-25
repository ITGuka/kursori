<?php
/** @var Core\View\Template $this @var array $data */
$items = array_values(array_filter($data['items'], fn ($item) => $item['title'] !== ''));
?>
<div class="container-fluid py-5">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php foreach ($items as $i => $item): ?>
            <?php $left = $i % 2 === 0; $image = media_url($item['image'], 'thumb'); ?>
            <div class="service-item service-item-<?= $left ? 'left' : 'right' ?>">
                <div class="row g-0 align-items-center">
                    <div class="col-md-5<?= $left ? '' : ' order-md-1 text-md-end' ?>">
                        <?php if ($image !== ''): ?>
                            <div class="service-img p-5 wow <?= $left ? 'fadeInRight' : 'fadeInLeft' ?>" data-wow-delay="0.2s">
                                <?= picture($item['image'], 'thumb', ['class' => 'img-fluid rounded-circle']) ?>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="col-md-7">
                        <div class="service-text px-5 px-md-0 py-md-5<?= $left ? '' : ' text-md-end' ?> wow <?= $left ? 'fadeInRight' : 'fadeInLeft' ?>" data-wow-delay="0.5s">
                            <h3 class="text-uppercase"><?= e($item['title']) ?></h3>
                            <?php if ($item['text'] !== ''): ?><p class="mb-4"><?= nl2br(e($item['text'])) ?></p><?php endif ?>
                            <?php if ($item['button_label'] !== '' && $item['button_link']): ?>
                                <a<?= link_attrs($item['button_link']) ?> class="btn btn-outline-primary border-2 px-4"><?= e($item['button_label']) ?> <i class="fa fa-arrow-right ms-1"></i></a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
