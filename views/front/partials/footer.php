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
$phone = (string)setting('contact.phone');
$email = (string)setting('contact.email');
$contacts = array_filter([
    $phone !== '' ? ['fa fa-phone', $phone, tel_href($phone)] : null,
    $email !== '' ? ['fa fa-envelope', $email, 'mailto:' . $email] : null,
    ($address = (string)setting('contact.address')) !== '' ? ['fa fa-location-dot', $address, null] : null,
    ($hours = (string)setting('contact.working_hours')) !== '' ? ['fa fa-clock', $hours, null] : null,
]);
?>
<div class="container-fluid bg-dark text-light footer py-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center py-5">
        <a href="<?= e(url(locale())) ?>">
            <span class="d-block h1 display-4 mb-3 text-white text-uppercase"><i class="fa-regular fa-face-smile me-1"></i><?= e($siteName) ?></span>
        </a>
        <?php if ($tagline = setting('general.tagline')): ?><p class="mb-4"><?= e($tagline) ?></p><?php endif ?>
        <?php if ($contacts !== []): ?>
            <ul class="footer-contacts mb-4">
                <?php foreach ($contacts as [$icon, $text, $href]): ?>
                    <li>
                        <i class="<?= e($icon) ?> text-primary me-2" aria-hidden="true"></i>
                        <?php if ($href !== null): ?><a href="<?= e($href) ?>"><?= e($text) ?></a><?php else: ?><?= e($text) ?><?php endif ?>
                    </li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
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
        <p class="mb-0"><?= e(str_replace(':year', date('Y'), (string)setting('general.copyright'))) ?></p>
    </div>
</div>
