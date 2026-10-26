<?php
/** @var array $data — ნდობის ზოლი: რამდენიმე ციფრი წარწერით (მაგ. ჰეროს ქვეშ) */
$items = array_values(array_filter($data['items'], fn ($item) => $item['value'] !== ''));
if ($items === []) {
    return;
}
?>
<div class="container-fluid py-5 stats<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container">
        <div class="stats-panel" style="--stats-cols: <?= count($items) ?>">
            <?php foreach ($items as $item): ?>
                <div class="stats-item">
                    <div class="stats-value text-primary"><?= e($item['value']) ?></div>
                    <?php if ($item['label'] !== ''): ?><p class="stats-label mb-0"><?= e($item['label']) ?></p><?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
