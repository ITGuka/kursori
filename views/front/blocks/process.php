<?php
/** @var Core\View\Template $this @var array $data — თანამშრომლობის ეტაპები რიგით (ნომრები რიგს აჩვენებს) */
$steps = array_values(array_filter($data['steps'], fn ($step) => $step['title'] !== ''));
if ($steps === []) {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <ol class="process-steps wow fadeInUp" data-wow-delay="0.1s">
            <?php foreach ($steps as $i => $step): ?>
                <li class="process-step">
                    <span class="process-number" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                    <h3 class="h5 text-uppercase"><?= e($step['title']) ?></h3>
                    <?php if ($step['text'] !== ''): ?><p class="mb-0"><?= nl2br(e($step['text'])) ?></p><?php endif ?>
                </li>
            <?php endforeach ?>
        </ol>
    </div>
</div>
