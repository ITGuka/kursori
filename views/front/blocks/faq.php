<?php
/** @var Core\View\Template $this @var array $data — ხშირი კითხვები (FAQPage schema-თი) */
if (array_filter($data['items'], fn ($item) => $item['question'] !== '') === []) {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
                <?= $this->insert('front/partials/faq', ['items' => $data['items']]) ?>
            </div>
        </div>
    </div>
</div>
