<?php
/** @var array $data — ნდობის ზოლი: რამდენიმე ციფრი წარწერით (მაგ. ჰეროს ქვეშ) */
$items = array_values(array_filter($data['items'], fn ($item) => $item['value'] !== ''));
if ($items === []) {
    return;
}
$column = count($items) <= 4 ? 'col-lg-' . intdiv(12, count($items)) : 'col-lg';
?>
<div class="container-fluid py-5 stats<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container">
        <div class="row g-4 justify-content-center text-center">
            <?php foreach ($items as $item): ?>
                <div class="col-6 <?= $column ?>">
                    <div class="stats-value text-primary"><?= e($item['value']) ?></div>
                    <?php if ($item['label'] !== ''): ?><p class="stats-label mb-0"><?= e($item['label']) ?></p><?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
