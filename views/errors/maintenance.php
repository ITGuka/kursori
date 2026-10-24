<?php /** @var Core\View\Template $this */ ?>
<!doctype html>
<html lang="<?= e(locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e(t('errors.maintenance')) ?> · <?= e(setting('general.site_name')) ?></title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: system-ui, sans-serif; background: #131313; color: #c8c8c8; text-align: center; padding: 16px; }
        h1 { margin: 0 0 16px; color: #e1a96b; font-size: 32px; }
        p { max-width: 560px; line-height: 1.6; }
    </style>
</head>
<body>
<main>
    <h1><?= e(setting('general.site_name')) ?></h1>
    <p><?= nl2br(e($message)) ?></p>
</main>
</body>
</html>
