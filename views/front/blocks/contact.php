<?php
/**
 * @var Core\View\Template $this @var array $data
 * კონტაქტები — პარამეტრებიდან. ფორმა → POST /{lang}/forms/contact → ადმინის Inbox.
 */
$rows = array_filter([
    t('contact.phone')   => setting('contact.phone') ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', (string)setting('contact.phone'))) . '">' . e(setting('contact.phone')) . '</a>' : '',
    t('contact.email')   => setting('contact.email') ? '<a href="mailto:' . e(setting('contact.email')) . '">' . e(setting('contact.email')) . '</a>' : '',
    t('contact.address') => setting('contact.address') ? e(setting('contact.address')) : '',
    t('contact.hours')   => setting('contact.working_hours') ? e(setting('contact.working_hours')) : '',
]);
$social = ['x' => 'fab fa-x-twitter', 'facebook' => 'fab fa-facebook-f', 'instagram' => 'fab fa-instagram', 'linkedin' => 'fab fa-linkedin-in',
           'youtube' => 'fab fa-youtube', 'behance' => 'fab fa-behance', 'dribbble' => 'fab fa-dribbble'];
$socialLinks = array_filter(array_map(fn ($network) => setting("social.$network"), array_combine(array_keys($social), array_keys($social))));
?>
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="<?= $data['show_form'] ? 'col-lg-6' : 'col-12' ?>">
                <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title'], 'align' => 'left']) ?>
                <?php if ($data['text'] !== ''): ?>
                    <div class="rich-text lh-base mb-4"><?= $data['text'] ?></div>
                <?php endif ?>
                <?php if ($rows !== [] || $socialLinks !== []): ?>
                    <table class="table table-dark mb-0 wow fadeInUp" data-wow-delay="0.3s">
                        <?php foreach ($rows as $label => $value): ?>
                            <tr><td class="text-uppercase"><?= e($label) ?></td><td><?= $value ?></td></tr>
                        <?php endforeach ?>
                        <?php if ($socialLinks !== []): ?>
                            <tr class="border-dark">
                                <td class="text-uppercase"><?= e(t('contact.follow_us')) ?></td>
                                <td>
                                    <?php foreach ($socialLinks as $network => $href): ?>
                                        <a class="me-1" href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><i class="<?= e($social[$network]) ?>"></i></a>
                                    <?php endforeach ?>
                                </td>
                            </tr>
                        <?php endif ?>
                    </table>
                <?php endif ?>
            </div>
            <?php if ($data['show_form']): ?>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <form method="post" action="<?= e(route('form.submit', ['lang' => locale(), 'form' => 'contact'])) ?>" id="form-contact" novalidate>
                        <?= csrf_field() ?>
                        <?= $this->insert('front/partials/form-status', ['form' => 'contact']) ?>
                        <?php $field = fn (string $name, string $label, string $type, bool $required, int $max) => $this->insert('front/partials/form-field',
                            ['form' => 'contact', 'name' => $name, 'label' => $label, 'type' => $type, 'required' => $required, 'max' => $max, 'uid' => $block['id']]); ?>
                        <div class="row g-3">
                            <div class="col-md-6"><?= $field('name', t('form.name'), 'text', true, 100) ?></div>
                            <div class="col-md-6"><?= $field('email', t('form.email'), 'email', true, 190) ?></div>
                            <div class="col-12"><?= $field('subject', t('form.subject'), 'text', false, 190) ?></div>
                            <div class="col-12"><?= $field('message', t('form.message'), 'textarea', true, 5000) ?></div>
                            <div class="col-12">
                                <button class="btn btn-outline-primary border-2 w-100 py-3" type="submit"><?= e(t('form.send')) ?></button>
                                <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
