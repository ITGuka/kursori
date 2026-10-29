<?php
/**
 * ფასების პაკეტები ბაზიდან (App\Models\Package) — ერთ პანელში სვეტებად (stats ბლოკის ენით: გამყოფები, ვარდისფერი აქცენტი);
 * გამორჩეული (ნიშნით) — ზედა ვარდისფერი ზოლით და მთავარი ღილაკით. ღილაკი → შეკვეთის გვერდი არჩეული პაკეტით.
 * ინდივიდუალური შეკვეთა (ბლოკის ველები) — პანელის ქვეშ ჰორიზონტალური ზოლი → შეკვეთის გვერდი ?package=custom.
 * @var Core\View\Template $this @var array $data
 */
use App\Controllers\Front\OrderController;
use App\Models\Language;
use App\Models\Package;

$packages = app(Package::class)->published(locale(), app(Language::class)->defaultCode());
$button = $data['button_label'] !== '' ? $data['button_label'] : t('order.order_now');
$customHref = $data['custom'] && $data['custom_name'] !== '' ? order_url(OrderController::CUSTOM) : '';
$customFeatures = array_values(array_filter(array_map('trim', explode("\n", $data['custom_features']))));
if ($packages === [] && $customHref === '') {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php if ($data['text'] !== ''): ?>
            <p class="fs-5 text-center mx-auto mb-5" style="max-width: 720px"><?= nl2br(e($data['text'])) ?></p>
        <?php endif ?>

        <?php if ($packages !== []): ?>
            <?php // subgrid: სათაური, სია და ღილაკი ყველა სვეტში ერთ ხაზზეა, ტექსტის სიგრძის მიუხედავად ?>
            <div class="pricing-panel wow fadeInUp" data-wow-delay="0.1s" style="--pricing-cols: <?= count($packages) ?>">
                <?php foreach ($packages as $package): ?>
                    <?php $featured = $package['badge'] !== ''; ?>
                    <article class="pricing-plan<?= $featured ? ' is-featured' : '' ?>">
                        <header class="pricing-plan-head">
                            <div class="pricing-plan-top">
                                <h3 class="pricing-name"><?= e($package['name']) ?></h3>
                                <?php if ($featured): ?><span class="pricing-badge"><?= e($package['badge']) ?></span><?php endif ?>
                            </div>
                            <?php if ($package['price'] !== ''): ?><p class="pricing-price"><?= e($package['price']) ?></p><?php endif ?>
                            <?php if ($package['note'] !== ''): ?>
                                <p class="pricing-note"><i class="fa fa-clock me-2" aria-hidden="true"></i><?= e($package['note']) ?></p>
                            <?php endif ?>
                        </header>
                        <ul class="pricing-features">
                            <?php foreach ($package['features'] as $feature): ?>
                                <li><span class="pricing-check" aria-hidden="true"><i class="fa fa-check"></i></span><?= e($feature) ?></li>
                            <?php endforeach ?>
                        </ul>
                        <a href="<?= e(order_url($package['code'])) ?>" class="btn <?= $featured ? 'btn-primary' : 'btn-outline-primary' ?> border-2 w-100 py-3 pricing-action">
                            <?= e($button) ?><i class="fa fa-arrow-right ms-2" aria-hidden="true"></i>
                        </a>
                    </article>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <?php if ($customHref !== ''): ?>
            <div class="pricing-custom wow fadeInUp" data-wow-delay="0.2s">
                <span class="pricing-custom-icon" aria-hidden="true"><i class="fa fa-lightbulb"></i></span>
                <div class="pricing-custom-body">
                    <h3 class="pricing-custom-title">
                        <?= e($data['custom_name']) ?>
                        <?php if ($data['custom_price'] !== ''): ?><span class="pricing-custom-price"><?= e($data['custom_price']) ?></span><?php endif ?>
                    </h3>
                    <?php if ($data['custom_note'] !== ''): ?><p class="pricing-note mb-0"><?= e($data['custom_note']) ?></p><?php endif ?>
                    <?php if ($customFeatures !== []): ?>
                        <ul class="pricing-custom-features">
                            <?php foreach ($customFeatures as $feature): ?><li><?= e($feature) ?></li><?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </div>
                <a href="<?= e($customHref) ?>" class="btn btn-outline-primary border-2 px-4 py-3 pricing-custom-action">
                    <?= e($data['custom_button_label'] !== '' ? $data['custom_button_label'] : t('order.custom_title')) ?><i class="fa fa-arrow-right ms-2" aria-hidden="true"></i>
                </a>
            </div>
        <?php endif ?>
    </div>
</div>
