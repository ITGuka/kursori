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
// url()-ის შედეგი (base path-ით, მაგ. /WEBDEV/ka) → სრული URL APP_URL-ით (canonical, hreflang, OG)
$basePath = app()->baseUrlPath();
$absolute = fn (string $path) => $path === '' || preg_match('~^https?://~', $path)
    ? $path
    : absolute_url($basePath !== '' && str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath)) : $path);
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
    <meta property="og:locale" content="<?= e(locale()) ?>">
    <?php if (!empty($meta['canonical'])): ?><meta property="og:url" content="<?= e($absolute($meta['canonical'])) ?>"><?php endif ?>
    <?php if ($description !== ''): ?><meta property="og:description" content="<?= e($description) ?>"><?php endif ?>
    <?php if ($ogImage !== ''): ?>
        <meta property="og:image" content="<?= e($absolute($ogImage)) ?>">
        <meta name="twitter:card" content="summary_large_image">
    <?php endif ?>
    <?php
    // ორგანიზაციის სტრუქტურირებული მონაცემები (Google: სახელი, ლოგო, სოც. ქსელები, კონტაქტი, მისამართი)
    $address = (string)setting('contact.address');
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type'    => 'ProfessionalService',
        'address'  => $address !== '' ? ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressCountry' => 'GE'] : null,
        'name'     => $siteName,
        'url'      => absolute_url(locale()),
        'logo'     => ($logo = media_url(setting('general.logo'))) !== '' ? $absolute($logo) : null,
        'email'    => setting('contact.email') ?: null,
        'telephone'=> setting('contact.phone') ?: null,
        'sameAs'   => array_values(array_filter(array_map(fn ($n) => setting("social.$n"), ['facebook', 'instagram', 'x', 'linkedin', 'youtube', 'behance', 'dribbble']))) ?: null,
    ]);
    ?>
    <script type="application/ld+json"><?= json_encode($organization, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php if ($favicon = media_url(setting('general.favicon'))): ?>
        <link rel="icon" href="<?= e($favicon) ?>">
        <link rel="apple-touch-icon" href="<?= e(media_url(setting('general.favicon'), 'thumb')) ?>">
    <?php endif ?>
    <?php if ($verification = (string)setting('seo.google_verification')): ?><meta name="google-site-verification" content="<?= e($verification) ?>"><?php endif ?>

    <?php // მენიუს მთავრულის შრიფტი — URL ზუსტად site.css-ის url()-ის მსგავსი (ვერსიის გარეშე), თორემ ორჯერ ჩამოიტვირთება ?>
    <?php if (locale() === 'ka'): ?><link rel="preload" href="<?= e(url('assets/front/fonts/dejavu-sans-mtavruli-regular.woff2')) ?>" as="font" type="font/woff2" crossorigin><?php endif ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php
    // პირველ ხატვას არ აყოვნებს (media="print" → onload "all"); DOM-ში რიგი იგივეა, ამიტომ კასკადი არ იცვლება
    $deferred = ['https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;700&family=Work+Sans:wght@400;600&family=Noto+Sans+Georgian:wght@300;400;600;700&display=swap',
                 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
                 asset('front/lib/animate/animate.min.css'),
                 asset('front/lib/owlcarousel/assets/owl.carousel.min.css')];
    ?>
    <?php foreach ($deferred as $href): ?>
        <link href="<?= e($href) ?>" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript><link href="<?= e($href) ?>" rel="stylesheet"></noscript>
    <?php endforeach ?>
    <link href="<?= asset('front/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('front/css/site.css') ?>" rel="stylesheet">
    <?= setting('scripts.head_code') ?>
</head>
<body>
<?php // Poseify-ის ჩატვირთვის spinner ამოღებულია — გვერდს ფარავდა მაშინაც, როცა უკვე ჩატვირთული იყო ?>

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

<?php // მობილურზე ქვედა ზოლი: დარეკვა + მთავარი CTA (back-to-top-ის ნაცვლად) ?>
<?php $cta = cta_link(); $phone = (string)setting('contact.phone'); ?>
<?php if ($cta !== null || $phone !== ''): ?>
    <div class="mobile-cta d-lg-none">
        <?php if ($phone !== ''): ?>
            <a href="<?= e(tel_href($phone)) ?>" class="btn btn-outline-primary border-2"><i class="fa fa-phone me-2" aria-hidden="true"></i><?= e(t('mobile_cta.call')) ?></a>
        <?php endif ?>
        <?php if ($cta !== null): ?>
            <a href="<?= e($cta['href']) ?>" class="btn btn-primary border-2"><?= e($cta['label']) ?></a>
        <?php endif ?>
    </div>
<?php endif ?>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('front/lib/easing/easing.min.js') ?>"></script>
<script src="<?= asset('front/lib/waypoints/waypoints.min.js') ?>"></script>
<script src="<?= asset('front/lib/owlcarousel/owl.carousel.min.js') ?>"></script>
<script src="<?= asset('front/js/main.js') ?>"></script>
<?= $this->section('scripts') ?>
<?= setting('scripts.body_code') ?>
</body>
</html>
