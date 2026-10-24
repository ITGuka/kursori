<?php /** @var Core\View\Template $this */ ?>
<!doctype html>
<html lang="<?= e(locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($status) ?></title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: system-ui, sans-serif; background: #131313; color: #c8c8c8; text-align: center; }
        h1 { margin: 0; font-size: 96px; color: #e1a96b; }
        a { color: #e1a96b; }
    </style>
</head>
<body>
<main>
    <h1><?= e($status) ?></h1>
    <p><?= e($message) ?></p>
    <p><a href="<?= e(url(locale())) ?>">←</a></p>
</main>
</body>
</html>
