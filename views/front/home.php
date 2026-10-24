<?php /** @var Core\View\Template $this */ ?>
<?php $this->layout('front/layouts/base') ?>

<?php // დროებითი გვერდი: ამოწმებს ენებს, თარგმანებს და პარამეტრებს. ფაზა 4-ში ჩანაცვლდება ბლოკებით. ?>
<div class="container py-5">
    <?php if ($logo = media_url(setting('general.logo'), 'medium')): ?>
        <img src="<?= e($logo) ?>" alt="<?= e(media_alt(setting('general.logo')) ?: setting('general.site_name')) ?>" class="mb-4" style="max-height: 80px">
    <?php endif ?>
    <div class="title">
        <div class="title-left">
            <h5><?= e(setting('general.tagline') ?: 'Phase 2') ?></h5>
            <h1><?= e(setting('general.site_name')) ?></h1>
        </div>
    </div>

    <nav class="mb-4">
        <?php foreach (['home', 'about', 'services', 'team', 'contact'] as $item): ?>
            <span class="me-3 text-uppercase"><?= e(t("nav.$item")) ?></span>
        <?php endforeach ?>
    </nav>

    <table class="table table-dark mb-4">
        <tr><td><?= e(t('contact.phone')) ?></td><td><?= e(setting('contact.phone') ?: '—') ?></td></tr>
        <tr><td><?= e(t('contact.email')) ?></td><td><?= e(setting('contact.email') ?: '—') ?></td></tr>
        <tr><td><?= e(t('contact.address')) ?></td><td><?= e(setting('contact.address') ?: '—') ?></td></tr>
        <tr><td>Database</td><td><?= e($dbStatus) ?></td></tr>
    </table>

    <?php foreach ($languages as $language): ?>
        <a class="btn <?= $language['code'] === $lang ? 'btn-primary' : 'btn-outline-primary' ?> border-2 me-2"
           href="<?= e(route('home', ['lang' => $language['code']])) ?>"><?= e($language['native_name']) ?></a>
    <?php endforeach ?>

    <p class="mt-5 mb-0"><?= e(str_replace(':year', date('Y'), (string)setting('general.copyright'))) ?></p>
</div>
