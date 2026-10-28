<?php
/** @var array $data — ნდობის ზოლი: რამდენიმე ციფრი წარწერით (მაგ. ჰეროს ქვეშ) */
$items = array_values(array_filter($data['items'], fn ($item) => $item['value'] !== ''));
if ($items === []) {
    return;
}
// დიდ ეკრანზე: 4-მდე — ერთ რიგად; მეტი — 2 რიგად. კენტისას (5, 7) პირველი ციფრი დიდ უჯრაშია, ორივე რიგზე — ცარიელი უჯრა არ რჩება
$count = count($items);
$featured = $count >= 5 && $count % 2 === 1;
$cols = $count <= 4 ? $count : intdiv($count, 2);
?>
<div class="container-fluid py-5 stats<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container">
        <div class="stats-panel<?= $featured ? ' stats-panel-featured' : '' ?>" style="--stats-cols: <?= $cols ?>">
            <?php foreach ($items as $item): ?>
                <div class="stats-item">
                    <div class="stats-value text-primary"><?= e($item['value']) ?></div>
                    <?php if ($item['label'] !== ''): ?><p class="stats-label mb-0"><?= e($item['label']) ?></p><?php endif ?>
                    <?php // ჩამონათვალი „ · “-ით → ცალკე ელემენტები, რომ ხაზზე გადატანისას სიტყვები არ გაწყდეს ?>
                    <?php $note = array_filter(array_map('trim', explode('·', (string)($item['note'] ?? ''))), 'strlen'); ?>
                    <?php if ($note !== []): ?>
                        <ul class="stats-note list-unstyled mb-0"><?php foreach ($note as $part): ?><li><?= e($part) ?></li><?php endforeach ?></ul>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
