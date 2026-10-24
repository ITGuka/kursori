<?php /** @var Core\View\Template $this */ ?>
<?php $this->layout('front/layouts/base') ?>

<div class="container py-5">
    <div class="title">
        <div class="title-left">
            <h5>Phase 0</h5>
            <h1><?= e(config('app.name')) ?></h1>
        </div>
    </div>

    <table class="table table-dark mb-4">
        <tr><td>PHP</td><td><?= e(PHP_VERSION) ?></td></tr>
        <tr><td>Database</td><td><?= e($dbStatus) ?></td></tr>
        <tr><td>Locale</td><td><?= e($lang) ?></td></tr>
        <tr><td>Route</td><td><?= e(route('home', ['lang' => $lang])) ?></td></tr>
    </table>

    <?php foreach ($languages as $language): ?>
        <a class="btn <?= $language['code'] === $lang ? 'btn-primary' : 'btn-outline-primary' ?> border-2 me-2"
           href="<?= e(route('home', ['lang' => $language['code']])) ?>"><?= e($language['native_name']) ?></a>
    <?php endforeach ?>
</div>
