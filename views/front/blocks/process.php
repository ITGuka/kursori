<?php
/** @var Core\View\Template $this @var array $data — თანამშრომლობის ეტაპები რიგით (ნომრები რიგს აჩვენებს) */
$steps = array_values(array_filter($data['steps'], fn ($step) => $step['title'] !== ''));
if ($steps === []) {
    return;
}
// pipeline: ვერტიკალური ხაზი, ეტაპზე AI აგენტების ბეჯები (გუნდის ბარათების მსგავსად)
$pipeline = ($data['layout'] ?? 'grid') === 'pipeline';
$agents = config('blocks.team_grid.fields.members.fields.agent.options', []);
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php if ($pipeline): ?>
            <ol class="process-pipeline">
                <?php foreach ($steps as $i => $step): ?>
                    <?php $keys = array_filter(array_map('trim', explode(',', strtolower((string)($step['agents'] ?? '')))), fn ($key) => $key !== '' && isset($agents[$key])) ?>
                    <li class="pipeline-step wow fadeInUp" data-wow-delay="<?= e(0.1 + ($i % 4) * 0.1) ?>s">
                        <span class="pipeline-number" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                        <div class="pipeline-card">
                            <div class="pipeline-body">
                                <h3 class="h5 text-uppercase mb-2"><?= e($step['title']) ?></h3>
                                <?php if ($step['text'] !== ''): ?><p class="mb-0"><?= nl2br(e($step['text'])) ?></p><?php endif ?>
                            </div>
                            <?php if ($keys !== []): ?>
                                <div class="pipeline-agents">
                                    <?php foreach (array_unique($keys) as $key): ?>
                                        <span class="team-agent team-agent-<?= e($key) ?>"><?= e($agents[$key]) ?></span>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                        </div>
                    </li>
                <?php endforeach ?>
            </ol>
        <?php else: ?>
            <ol class="process-steps wow fadeInUp" data-wow-delay="0.1s">
                <?php foreach ($steps as $i => $step): ?>
                    <li class="process-step">
                        <span class="process-number" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                        <h3 class="h5 text-uppercase"><?= e($step['title']) ?></h3>
                        <?php if ($step['text'] !== ''): ?><p class="mb-0"><?= nl2br(e($step['text'])) ?></p><?php endif ?>
                    </li>
                <?php endforeach ?>
            </ol>
        <?php endif ?>
    </div>
</div>
