<?php
/**
 * განაცხადის ფორმა (ყოფილი Casting) → POST /{lang}/forms/application → ადმინის Inbox.
 * @var Core\View\Template $this @var array $data @var array $block
 */
$field = fn (string $name, string $label, string $type, int $max, bool $required = true) => $this->insert('front/partials/form-field',
    ['form' => 'application', 'name' => $name, 'label' => $label, 'type' => $type, 'required' => $required, 'max' => $max, 'uid' => $block['id']]);
$choice = fn (string $name) => $this->insert('front/partials/form-choice', ['form' => 'application', 'name' => $name,
    'label' => t((string)config("forms.application.fields.$name.label")), 'options' => config("forms.application.fields.$name.options"), 'uid' => $block['id']]);
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
                        <div class="col-12"><?= $field('name', t('form.name'), 'text', 100) ?></div>
                        <div class="col-md-6"><?= $field('email', t('form.email'), 'email', 190, false) ?></div>
                        <div class="col-md-6"><?= $field('phone', t('form.phone'), 'tel', 50, false) ?></div>
                        <div class="col-12"><p class="form-hint mb-0"><?= e(t('form.one_of_hint')) ?></p></div>
                        <div class="col-12"><?= $choice('service') ?></div>
                        <div class="col-12"><?= $choice('budget') ?></div>
                        <div class="col-12"><?= $field('message', $data['message_label'] !== '' ? $data['message_label'] : t('form.about_you'), 'textarea', 5000) ?></div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit"><?= e($data['button_label'] !== '' ? $data['button_label'] : t('form.apply')) ?></button>
                            <p class="form-hint text-center mt-2 mb-0"><?= e(t('form.reply_time')) ?></p>
                            <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
