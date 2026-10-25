<?php
/** @var Core\View\Template $this @var array $data — ფასების პაკეტები („-დან“ ფასით, რა შედის, ღილაკი) */
$packages = array_values(array_filter($data['packages'], fn ($package) => $package['name'] !== ''));
if ($packages === []) {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php if ($data['text'] !== ''): ?>
            <p class="fs-5 text-center mx-auto mb-5" style="max-width: 720px"><?= nl2br(e($data['text'])) ?></p>
        <?php endif ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($packages as $package): ?>
                <?php $features = array_filter(array_map('trim', explode("\n", $package['features']))); ?>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="pricing-card h-100<?= $package['badge'] !== '' ? ' is-featured' : '' ?>">
                        <?php if ($package['badge'] !== ''): ?><span class="pricing-badge"><?= e($package['badge']) ?></span><?php endif ?>
                        <h3 class="h4 text-uppercase"><?= e($package['name']) ?></h3>
                        <?php if ($package['price'] !== ''): ?><p class="pricing-price mb-1"><?= e($package['price']) ?></p><?php endif ?>
                        <?php if ($package['note'] !== ''): ?><p class="pricing-note"><?= e($package['note']) ?></p><?php endif ?>
                        <?php if ($features !== []): ?>
                            <ul class="pricing-features">
                                <?php foreach ($features as $feature): ?>
                                    <li><i class="fa fa-check text-primary me-2" aria-hidden="true"></i><?= e($feature) ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                        <?php if ($package['button_label'] !== '' && $package['button_link']): ?>
                            <a<?= link_attrs($package['button_link']) ?> class="btn <?= $package['badge'] !== '' ? 'btn-primary' : 'btn-outline-primary' ?> border-2 w-100 py-3 mt-auto"><?= e($package['button_label']) ?></a>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
