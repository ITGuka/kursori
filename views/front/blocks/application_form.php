<?php
/**
 * განაცხადის ფორმა (ყოფილი Casting) → POST /{lang}/forms/application → ადმინის Inbox.
 * @var Core\View\Template $this @var array $data @var array $block
 */
$field = fn (string $name, string $label, string $type, int $max) => $this->insert('front/partials/form-field',
    ['form' => 'application', 'name' => $name, 'label' => $label, 'type' => $type, 'required' => true, 'max' => $max, 'uid' => $block['id']]);
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-8">
                <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
                <?php if ($data['text'] !== ''): ?>
                    <div class="rich-text text-center fs-5 mb-5 wow fadeInUp" data-wow-delay="0.2s"><?= $data['text'] ?></div>
                <?php endif ?>
                <form method="post" action="<?= e(route('form.submit', ['lang' => locale(), 'form' => 'application'])) ?>" id="form-application" novalidate class="wow fadeInUp" data-wow-delay="0.3s">
                    <?= csrf_field() ?>
                    <?= $this->insert('front/partials/form-status', ['form' => 'application']) ?>
                    <div class="row g-3">
                        <div class="col-md-6"><?= $field('name', t('form.name'), 'text', 100) ?></div>
                        <div class="col-md-6"><?= $field('email', t('form.email'), 'email', 190) ?></div>
                        <div class="col-12"><?= $field('phone', t('form.phone'), 'tel', 50) ?></div>
                        <div class="col-12"><?= $field('message', $data['message_label'] !== '' ? $data['message_label'] : t('form.about_you'), 'textarea', 5000) ?></div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit"><?= e($data['button_label'] !== '' ? $data['button_label'] : t('form.apply')) ?></button>
                            <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
