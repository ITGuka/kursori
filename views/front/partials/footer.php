<?php
/**
 * საიტის footer: CTA ზოლი → სვეტები (ბრენდი, სერვისები, კომპანია, კონტაქტი) → copyright.
 * სერვისები — header მენიუს პირველი ჩამოსაშლელიდან, კომპანია — footer მენიუდან (ორივე ადმინიდან იმართება).
 */
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
$address = (string)setting('contact.address');
$hours = (string)setting('contact.working_hours');
$hoursAi = (string)setting('contact.working_hours_ai');

$currentPath = rtrim(app()->baseUrlPath() . (string)app()->request()?->path(), '/');
$isActive = fn (string $href) => $href !== '' && !preg_match('~^[a-z]+:~i', $href) && rtrim((string)parse_url($href, PHP_URL_PATH), '/') === $currentPath;
$current = fn (string $href) => $isActive($href) ? ' aria-current="page"' : '';

// სერვისები: header-ის პირველი ჩამოსაშლელი; შვილი, რომელიც მშობელს იმეორებს (dropdown-ის „ყველა სერვისი“), გამოტოვებულია
$services = null;
foreach (menu('header') as $item) {
    if ($item['children'] !== []) {
        $services = ['label' => $item['label'], 'items' => array_values(array_filter($item['children'], fn ($child) => $child['href'] !== $item['href']))];
        break;
    }
}
$companyMenu = menu('footer');
$socialLinks = array_filter(array_map(fn ($network) => (string)setting("social.$network"), array_combine(array_keys($social), array_keys($social))));

// CTA ზოლი არ ჩანს თავად CTA გვერდზე (იქ ფორმა უკვე არის)
$cta = cta_link();
$showCta = $cta !== null && !$isActive($cta['href']);
?>
<footer class="footer bg-dark text-light">
    <?php if ($showCta): ?>
        <div class="container">
            <div class="footer-cta">
                <div>
                    <p class="footer-cta-title"><?= e(t('footer.cta_title')) ?></p>
                    <p class="footer-cta-text mb-0"><?= e(t('footer.cta_text')) ?></p>
                </div>
                <div class="footer-cta-actions">
                    <a href="<?= e($cta['href']) ?>" class="btn btn-primary border-2 py-3 px-4"><?= e($cta['label']) ?></a>
                    <?php if ($phone !== ''): // მობილურზე ქვედა ზოლის „დარეკვა“ ცვლის ?>
                        <a href="<?= e(tel_href($phone)) ?>" class="btn btn-outline-primary border-2 py-3 px-4 d-none d-lg-inline-block"><i class="fa fa-phone me-2" aria-hidden="true"></i><?= e($phone) ?></a>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endif ?>

    <div class="container footer-main">
        <div class="row g-5">
            <div class="col-lg-3">
                <?php // ლოგოს ნიშანი კურსორით, ენის მიხედვით: ka — „კ“, სხვა — „K“ (სრული ლოგო ჰედერშია) ?>
                <a href="<?= e(url(locale())) ?>" class="d-inline-block<?= $socialLinks !== [] ? ' mb-4' : '' ?>" aria-label="<?= e($siteName) ?>">
                    <img src="<?= asset('front/img/logo/kursori-icon-' . (locale() === 'ka' ? 'ka' : 'en') . '.svg') ?>" alt="<?= e($siteName) ?>" class="footer-logo footer-logo-icon"
                        width="100" height="100" loading="lazy">
                </a>
                <?php if ($socialLinks !== []): ?>
                    <ul class="footer-social" aria-label="<?= e(t('contact.follow_us')) ?>">
                        <?php foreach ($socialLinks as $network => $href): ?>
                            <li><a href="<?= e($href) ?>" target="_blank" rel="noopener me" aria-label="<?= e(ucfirst($network)) ?>"><i class="<?= e($social[$network]) ?>" aria-hidden="true"></i></a></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>

            <?php if ($services !== null && $services['items'] !== []): ?>
                <nav class="col-6 col-md-4 col-lg-3" aria-labelledby="footer-services">
                    <h2 class="footer-title" id="footer-services"><?= e($services['label']) ?></h2>
                    <ul class="footer-links">
                        <?php foreach ($services['items'] as $item): ?>
                            <li><a href="<?= e($item['href']) ?>"<?= link_target($item) ?><?= $current($item['href']) ?>><?= e($item['label']) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </nav>
            <?php endif ?>

            <?php if ($companyMenu !== []): ?>
                <nav class="col-6 col-md-4 col-lg-3" aria-labelledby="footer-company">
                    <h2 class="footer-title" id="footer-company"><?= e(t('footer.company')) ?></h2>
                    <ul class="footer-links">
                        <?php foreach ($companyMenu as $item): ?>
                            <li><a href="<?= e($item['href']) ?>"<?= link_target($item) ?><?= $current($item['href']) ?>><?= e($item['label']) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </nav>
            <?php endif ?>

            <?php if ($phone !== '' || $email !== '' || $address !== '' || $hours !== '' || $hoursAi !== ''): ?>
                <div class="col-md-4 col-lg-3">
                    <h2 class="footer-title"><?= e(t('nav.contact')) ?></h2>
                    <address class="footer-contacts">
                        <?php if ($phone !== ''): ?>
                            <a href="<?= e(tel_href($phone)) ?>"><i class="fa fa-phone" aria-hidden="true"></i><span><?= e($phone) ?></span></a>
                        <?php endif ?>
                        <?php if ($email !== ''): ?>
                            <a href="mailto:<?= e($email) ?>"><i class="fa fa-envelope" aria-hidden="true"></i><span><?= e($email) ?></span></a>
                        <?php endif ?>
                        <?php if ($address !== ''): ?>
                            <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($address)) ?>" target="_blank" rel="noopener"><i class="fa fa-location-dot" aria-hidden="true"></i><span><?= e($address) ?></span></a>
                        <?php endif ?>
                        <?php // სამუშაო საათები: გუნდი (ადამიანები) და AI ბოტები ?>
                        <?php if ($hours !== ''): ?>
                            <span class="footer-contacts-item"><i class="fa fa-user-clock" aria-hidden="true"></i><span><span class="footer-hours-label"><?= e(t('contact.hours_people')) ?></span><?= e($hours) ?></span></span>
                        <?php endif ?>
                        <?php if ($hoursAi !== ''): ?>
                            <span class="footer-contacts-item"><i class="fa fa-robot" aria-hidden="true"></i><span><span class="footer-hours-label"><?= e(t('contact.hours_ai')) ?></span><?= e($hoursAi) ?></span></span>
                        <?php endif ?>
                    </address>
                </div>
            <?php endif ?>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p class="mb-0"><?= e(str_replace(':year', date('Y'), (string)setting('general.copyright'))) ?></p>
        </div>
    </div>
</footer>
