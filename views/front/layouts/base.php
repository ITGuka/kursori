<?php /** @var Core\View\Template $this */ ?>
<!doctype html>
<html lang="<?= e($lang ?? locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;700&family=Work+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="<?= asset('front/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?= $this->section('content') ?>
<?= $this->section('scripts') ?>
</body>
</html>
