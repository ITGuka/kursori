<?php /** @var Core\View\Template $this */ ?>
<!doctype html>
<html lang="ka">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e(($title ?? 'შესვლა') . ' · ' . config('app.name')) ?></title>
    <?= $this->insert('admin/partials/head') ?>
</head>
<body>
<div class="container-fluid position-relative bg-white d-flex p-0">
    <div class="container-fluid">
        <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                    <?= $this->section('content') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->insert('admin/partials/scripts') ?>
</body>
</html>
