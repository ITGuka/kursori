<?php /** @var Core\View\Template $this */ ?>
<!doctype html>
<html lang="ka" class="layout-wide customizer-hide" data-bs-theme="light" data-skin="default" data-template="vertical-menu-template">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e(($title ?? 'შესვლა') . ' · ' . config('app.name')) ?></title>
    <?= $this->insert('admin/partials/head') ?>
    <link href="<?= asset('admin/sneat/css/page-auth.css') ?>" rel="stylesheet">
</head>
<body>
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <div class="app-brand justify-content-center">
                        <span class="app-brand-link gap-2">
                            <span class="app-brand-logo demo admin-logo"><?= e(mb_substr((string)config('app.name'), 0, 1)) ?></span>
                            <span class="app-brand-text demo text-heading fw-bold"><?= e(config('app.name')) ?></span>
                        </span>
                    </div>
                    <?= $this->section('content') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->insert('admin/partials/scripts') ?>
</body>
</html>
