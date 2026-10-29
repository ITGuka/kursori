<?php
/** @var Core\View\Template $this @var array $data */
$items = array_filter($data['items'], fn ($item) => $item['text'] !== '');
$buttons = array_filter([
    ['label' => $data['button1_label'], 'link' => $data['button1_link'], 'class' => 'btn btn-outline-primary border-2 py-3 px-4'],
    ['label' => $data['button2_label'], 'link' => $data['button2_link'], 'class' => 'btn btn-primary py-3 px-4'],
], fn ($button) => $button['label'] !== '' && $button['link']);
$image = media_url($data['image'], 'medium');
?>
<div class="container-fluid about-section<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="<?= $image !== '' ? 'col-lg-7' : 'col-12' ?>">
                <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title'], 'align' => 'left']) ?>
                <?php if ($data['text'] !== ''): ?>
                    <div class="rich-text about-text mb-4 wow fadeInUp" data-wow-delay="0.2s"><?= $data['text'] ?></div>
                <?php endif ?>
                <?php if ($items !== []): ?>
                    <ul class="about-list mb-5 wow fadeInUp" data-wow-delay="0.3s">
                        <?php foreach ($items as $item): ?>
                            <li><span class="about-list-icon"><i class="fa fa-check"></i></span><?= e($item['text']) ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <?php if ($buttons !== []): ?>
                    <div class="about-actions wow fadeInUp" data-wow-delay="0.4s">
                        <?php foreach ($buttons as $button): ?>
                            <a<?= link_attrs($button['link']) ?> class="<?= $button['class'] ?>"><?= e($button['label']) ?></a>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
            <?php if ($image !== ''): ?>
                <div class="col-lg-5 wow fadeInUp" data-wow-delay="0.5s">
                    <?php // hover: დიზაინ-რედაქტორის მონიშვნის ჩარჩო სახელურებით + ბრენდის კურსორი; კურსორის მიყოლით მსუბუქი 3D დახრა (main.js, data-tilt) ?>
                    <div class="about-media" data-tilt>
                        <?= picture($data['image'], 'medium', ['class' => 'about-image', 'alt' => media_alt($data['image']) ?: $data['title']], '(min-width: 992px) 40vw, 100vw') ?>
                        <span class="about-frame" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                        <span class="about-cursor" aria-hidden="true">
                            <i class="fa fa-arrow-pointer"></i><span class="about-cursor-tag"><?= e((string)setting('general.site_name')) ?></span>
                        </span>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
