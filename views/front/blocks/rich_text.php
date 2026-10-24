<?php
/** @var Core\View\Template $this @var array $data */
if ($data['body'] === '' && $data['title'] === '') {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title'], 'align' => $data['align']]) ?>
        <?php if ($data['body'] !== ''): ?>
            <div class="rich-text fs-5 wow fadeInUp" data-wow-delay="0.2s"><?= $data['body'] ?></div>
        <?php endif ?>
    </div>
</div>
