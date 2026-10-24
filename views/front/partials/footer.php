<?php
$social = [
    'x'         => 'fab fa-x-twitter',
    'facebook'  => 'fab fa-facebook-f',
    'instagram' => 'fab fa-instagram',
    'linkedin'  => 'fab fa-linkedin-in',
    'youtube'   => 'fab fa-youtube',
    'behance'   => 'fab fa-behance',
    'dribbble'  => 'fab fa-dribbble',
];
$siteName = (string)setting('general.site_name');
?>
<div class="container-fluid bg-dark text-light footer py-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center py-5">
        <a href="<?= e(url(locale())) ?>">
            <h1 class="display-4 mb-3 text-white text-uppercase"><i class="fa-regular fa-face-smile me-1"></i><?= e($siteName) ?></h1>
        </a>
        <?php if ($tagline = setting('general.tagline')): ?><p class="mb-4"><?= e($tagline) ?></p><?php endif ?>
        <?php if ($footerMenu = menu('footer')): ?>
            <nav class="footer-menu mb-4" aria-label="<?= e(t('nav.menu')) ?>">
                <?php foreach ($footerMenu as $item): ?>
                    <a href="<?= e($item['href']) ?>"<?= link_target($item) ?>><?= e($item['label']) ?></a>
                <?php endforeach ?>
            </nav>
        <?php endif ?>
        <div class="d-flex justify-content-center flex-wrap mb-4">
            <?php foreach ($social as $network => $icon): ?>
                <?php if ($href = setting("social.$network")): ?>
                    <a class="btn btn-lg-square btn-outline-primary border-2 m-1" href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><i class="<?= e($icon) ?>"></i></a>
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <p><?= e(str_replace(':year', date('Y'), (string)setting('general.copyright'))) ?></p>
        <?php // HTML Codex ატრიბუცია — იშლება Credit Removal ლიცენზიის შეძენის შემდეგ (ფაზა 9, D11) ?>
        <p class="mb-0">Designed By <a class="border-bottom" href="https://htmlcodex.com">HTML Codex</a></p>
    </div>
</div>
