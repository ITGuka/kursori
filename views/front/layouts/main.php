<?php
/**
 * Poseify-ის მთავარი layout.
 * @var Core\View\Template $this
 * @var array $meta title, description, image (URL), canonical, alternates [lang => url], robots
 */
$meta ??= [];
$siteName = (string)setting('general.site_name');
$pageTitle = $meta['title'] ?? $siteName;
$description = $meta['description'] ?? (string)setting('seo.meta_description');
$ogImage = $meta['image'] ?? media_url(setting('seo.og_image'), 'large');
$indexing = setting('seo.indexing') === true && empty($meta['noindex']);
$absolute = fn (string $path) => $path === '' || preg_match('~^https?://~', $path) ? $path : rtrim((string)config('app.url'), '/') . $path;
?>
<!doctype html>
<html lang="<?= e(locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <?php if ($description !== ''): ?><meta name="description" content="<?= e($description) ?>"><?php endif ?>
    <?php if (!$indexing): ?><meta name="robots" content="noindex, nofollow"><?php endif ?>
    <?php if (!empty($meta['canonical'])): ?><link rel="canonical" href="<?= e($absolute($meta['canonical'])) ?>"><?php endif ?>
    <?php foreach ($meta['alternates'] ?? [] as $hreflang => $href): ?>
        <link rel="alternate" hreflang="<?= e($hreflang) ?>" href="<?= e($absolute($href)) ?>">
    <?php endforeach ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <?php if ($description !== ''): ?><meta property="og:description" content="<?= e($description) ?>"><?php endif ?>
    <?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= e($absolute($ogImage)) ?>"><?php endif ?>
    <?php if ($favicon = media_url(setting('general.favicon'))): ?><link rel="icon" href="<?= e($favicon) ?>"><?php endif ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;700&family=Work+Sans:wght@400;600&family=Noto+Sans+Georgian:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('front/lib/animate/animate.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/lib/owlcarousel/assets/owl.carousel.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/css/site.css') ?>" rel="stylesheet">
    <?= setting('scripts.head_code') ?>
</head>
<body>
<div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only"><?= e(t('common.loading')) ?></span>
    </div>
</div>

<?php if (!empty($preview)): ?>
    <div class="preview-bar"><?= e($preview) ?></div>
<?php endif ?>

<div class="container-fluid p-0">
    <?= $this->insert('front/partials/navbar', ['languageLinks' => $meta['language_links'] ?? []]) ?>
    <?= $this->section('header') ?>
</div>

<?= $this->section('content') ?>

<?= $this->insert('front/partials/footer') ?>

<a href="#" class="btn btn-outline-primary border-2 btn-lg-square back-to-top" aria-label="<?= e(t('common.back_to_top')) ?>"><i class="fa fa-arrow-up"></i></a>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('front/lib/wow/wow.min.js') ?>"></script>
<script src="<?= asset('front/lib/easing/easing.min.js') ?>"></script>
<script src="<?= asset('front/lib/waypoints/waypoints.min.js') ?>"></script>
<script src="<?= asset('front/lib/owlcarousel/owl.carousel.min.js') ?>"></script>
<script src="<?= asset('front/js/main.js') ?>"></script>
<?= $this->section('scripts') ?>
<?= setting('scripts.body_code') ?>
</body>
</html>
