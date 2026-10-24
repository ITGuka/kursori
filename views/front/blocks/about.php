<?php
/** @var Core\View\Template $this @var array $data */
$items = array_filter($data['items'], fn ($item) => $item['text'] !== '');
$buttons = array_filter([
    ['label' => $data['button1_label'], 'link' => $data['button1_link'], 'class' => 'btn btn-outline-primary border-2 py-3 w-100'],
    ['label' => $data['button2_label'], 'link' => $data['button2_link'], 'class' => 'btn btn-primary py-3 w-100'],
], fn ($button) => $button['label'] !== '' && $button['link']);
$image = media_url($data['image'], 'medium');
?>
<div class="container-fluid<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="<?= $image !== '' ? 'col-lg-7' : 'col-12' ?> pb-0 pb-lg-5 py-5">
                <div class="pb-0 pb-lg-5 py-5">
                    <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title'], 'align' => 'left']) ?>
                    <?php if ($data['text'] !== ''): ?>
                        <div class="rich-text mb-4 wow fadeInUp" data-wow-delay="0.2s"><?= $data['text'] ?></div>
                    <?php endif ?>
                    <?php if ($items !== []): ?>
                        <ul class="list-group list-group-flush mb-5 wow fadeInUp" data-wow-delay="0.3s">
                            <?php foreach ($items as $item): ?>
                                <li class="list-group-item bg-dark text-body border-secondary ps-0">
                                    <i class="fa fa-check-circle text-primary me-1"></i> <?= e($item['text']) ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                    <?php if ($buttons !== []): ?>
                        <div class="row wow fadeInUp" data-wow-delay="0.4s">
                            <?php foreach ($buttons as $button): ?>
                                <div class="col-<?= count($buttons) === 1 ? '12 col-sm-6' : '6' ?>">
                                    <a<?= link_attrs($button['link']) ?> class="<?= $button['class'] ?>"><?= e($button['label']) ?></a>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
            <?php if ($image !== ''): ?>
                <div class="col-lg-5 wow fadeInUp" data-wow-delay="0.5s">
                    <img class="img-fluid" src="<?= e($image) ?>" alt="<?= e(media_alt($data['image'])) ?>" loading="lazy">
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
