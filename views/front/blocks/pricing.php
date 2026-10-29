<?php
/**
 * ფასების პაკეტები („-დან“ ფასით, რა შედის) ბაზიდან (App\Models\Package); ღილაკი → შეკვეთის გვერდი არჩეული პაკეტით.
 * ბოლოს — ინდივიდუალური შეკვეთის ბარათი (ბლოკის ველები) → custom_order_form-ის გვერდი.
 * @var Core\View\Template $this @var array $data
 */
use App\Models\Language;
use App\Models\Package;

$cards = [];
foreach (app(Package::class)->published(locale(), app(Language::class)->defaultCode()) as $package) {
    $cards[] = $package + ['href' => order_url($package['code']), 'button' => $data['button_label'] !== '' ? $data['button_label'] : t('order.order_now')];
}
$customHref = $data['custom'] ? order_url(null, 'custom_order_form') : '';
if ($customHref !== '' && $data['custom_name'] !== '') {
    $cards[] = [
        'name'     => $data['custom_name'],
        'price'    => $data['custom_price'],
        'note'     => $data['custom_note'],
        'features' => array_values(array_filter(array_map('trim', explode("\n", $data['custom_features'])))),
        'badge'    => '',
        'href'     => $customHref,
        'button'   => $data['custom_button_label'] !== '' ? $data['custom_button_label'] : t('order.custom_title'),
        'custom'   => true,
    ];
}
if ($cards === []) {
    return;
}
// 4 ბარათი — ერთ რიგში დიდ ეკრანზე
$column = count($cards) % 4 === 0 ? 'col-xl-3 col-md-6' : 'col-lg-4 col-md-6';
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php if ($data['text'] !== ''): ?>
            <p class="fs-5 text-center mx-auto mb-5" style="max-width: 720px"><?= nl2br(e($data['text'])) ?></p>
        <?php endif ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($cards as $i => $card): ?>
                <div class="<?= $column ?> wow fadeInUp" data-wow-delay="<?= e(number_format(0.1 + 0.1 * $i, 1)) ?>s">
                    <div class="pricing-card h-100<?= $card['badge'] !== '' ? ' is-featured' : '' ?><?= !empty($card['custom']) ? ' is-custom' : '' ?>">
                        <?php if ($card['badge'] !== ''): ?><span class="pricing-badge"><?= e($card['badge']) ?></span><?php endif ?>
                        <h3 class="h4 text-uppercase"><?= e($card['name']) ?></h3>
                        <?php if ($card['price'] !== ''): ?><p class="pricing-price mb-1"><?= e($card['price']) ?></p><?php endif ?>
                        <?php if ($card['note'] !== ''): ?><p class="pricing-note"><?= e($card['note']) ?></p><?php endif ?>
                        <?php if ($card['features'] !== []): ?>
                            <ul class="pricing-features">
                                <?php foreach ($card['features'] as $feature): ?>
                                    <li><i class="fa <?= !empty($card['custom']) ? 'fa-star' : 'fa-check' ?> text-primary me-2" aria-hidden="true"></i><?= e($feature) ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                        <?php if ($card['href'] !== ''): ?>
                            <a href="<?= e($card['href']) ?>" class="btn <?= $card['badge'] !== '' ? 'btn-primary' : 'btn-outline-primary' ?> border-2 w-100 py-3 mt-auto"><?= e($card['button']) ?></a>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
