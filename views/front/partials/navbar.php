<?php
/**
 * @var array<string, string> $languageLinks lang => URL (იმავე გვერდის თარგმანი ან მთავარი)
 * ნავიგაცია — ადმინის „მენიუები → მთავარი მენიუ“ (menu('header')).
 */
$lang = locale();
$nav = menu('header');
$currentPath = rtrim(app()->baseUrlPath() . (string)app()->request()?->path(), '/');
$isActive = fn (string $href) => $href !== '' && !preg_match('~^[a-z]+:~i', $href) && rtrim((string)parse_url($href, PHP_URL_PATH), '/') === $currentPath;
$logo = setting('general.logo');
$siteName = (string)setting('general.site_name');
$cta = cta_link();
$customOrder = order_url(null, 'custom_order_form');
// ენის გადამრთველი: წარწერა და დროშა (img/flags); სიაში არმყოფი ენა — კოდი, დროშის გარეშე
$langLabels = ['ka' => 'GEO', 'en' => 'ENG'];
$langFlags = ['ka' => 'ge', 'en' => 'gb'];
?>
<nav class="navbar navbar-expand-lg navbar-dark px-lg-5">
    <a href="<?= e(url($lang)) ?>" class="navbar-brand ms-4 ms-lg-0">
        <?php if ($logoUrl = media_url($logo, 'medium')): ?>
            <img src="<?= e($logoUrl) ?>" alt="<?= e(media_alt($logo) ?: $siteName) ?>" class="site-logo">
        <?php else: // ადმინში ლოგო არ არის → ენის მიხედვით SVG (ka — ქართული, სხვა — ლათინური) ?>
            <?php $logoLang = $lang === 'ka' ? 'ka' : 'en' ?>
            <img src="<?= asset("front/img/logo/kursori-logo-$logoLang-light.svg") ?>" alt="<?= e($siteName) ?>" class="site-logo site-logo-svg"
                width="<?= $logoLang === 'ka' ? 521 : 437 ?>" height="83">
        <?php endif ?>
    </a>
    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-label="<?= e(t('nav.menu')) ?>">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <?php // მენიუ ლოგოს გვერდით (მარცხნივ); CTA და ენები — მარჯვნივ ?>
        <div class="navbar-nav me-auto ms-lg-4 p-4 p-lg-0">
            <?php foreach ($nav as $item): ?>
                <?php if ($item['children'] === []): ?>
                    <?php // nav-link-home — მთავარი, დაბალ რეზოლუციაზე იმალება (ლოგოც მთავარზე გადადის) ?>
                    <a href="<?= e($item['href']) ?>"<?= link_target($item) ?> class="nav-item nav-link<?= $item['href'] === url($lang) ? ' nav-link-home' : '' ?><?= $isActive($item['href']) ? ' active' : '' ?>"
                        <?= $isActive($item['href']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
                <?php else: ?>
                    <?php $childActive = array_filter($item['children'], fn ($child) => $isActive($child['href'])); ?>
                    <div class="nav-item dropdown">
                        <a href="<?= e($item['href']) ?>" class="nav-link dropdown-toggle<?= $isActive($item['href']) || $childActive ? ' active' : '' ?>" data-bs-toggle="dropdown"><?= e($item['label']) ?></a>
                        <div class="dropdown-menu m-0">
                            <?php // dropdown-toggle ბმულს არ ხსნის — მშობელი გვერდი ჩამონათვალშიც ჩანს ?>
                            <a href="<?= e($item['href']) ?>"<?= link_target($item) ?> class="dropdown-item<?= $isActive($item['href']) ? ' active' : '' ?>"><?= e($item['label']) ?></a>
                            <?php foreach ($item['children'] as $child): ?>
                                <a href="<?= e($child['href']) ?>"<?= link_target($child) ?> class="dropdown-item<?= $isActive($child['href']) ? ' active' : '' ?>"><?= e($child['label']) ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <?php if ($cta !== null || $customOrder !== ''): ?>
            <?php // CTA (შეკვეთა) + ინდივიდუალური შეკვეთა (custom_order_form-ის გვერდი); მობილურზე — ჩამოსაშლელ მენიუში ?>
            <div class="d-flex flex-wrap flex-lg-nowrap gap-2 px-4 px-lg-0 pb-3 pb-lg-0 me-lg-3">
                <?php if ($customOrder !== ''): ?>
                    <?php // დესკტოპზე ვიწრო ეკრანზე — მხოლოდ ხატულა (წარწერა — title / aria-label), რომ მენიუ ერთ ხაზზე დაეტიოს ?>
                    <a href="<?= e($customOrder) ?>" class="btn btn-outline-primary border-2 navbar-cta navbar-cta-custom" title="<?= e(t('nav.custom_order')) ?>"
                       aria-label="<?= e(t('nav.custom_order')) ?>"<?= $isActive($customOrder) ? ' aria-current="page"' : '' ?>><i class="fa fa-lightbulb" aria-hidden="true"></i><span class="navbar-cta-label"><?= e(t('nav.custom_order')) ?></span></a>
                <?php endif ?>
                <?php if ($cta !== null): ?>
                    <a href="<?= e($cta['href']) ?>" class="btn btn-primary border-2 navbar-cta"<?= $isActive($cta['href']) ? ' aria-current="page"' : '' ?>><?= e($cta['label']) ?></a>
                <?php endif ?>
            </div>
        <?php endif ?>
        <?php if (count($languageLinks) > 1): ?>
            <div class="px-4 px-lg-0 pb-3 pb-lg-0">
                <div class="lang-switch" role="group" aria-label="<?= e(t('common.language')) ?>">
                    <?php foreach ($languages ?? [] as $language): ?>
                        <?php if (isset($languageLinks[$language['code']])): ?>
                            <a href="<?= e($languageLinks[$language['code']]) ?>" hreflang="<?= e($language['code']) ?>" lang="<?= e($language['code']) ?>"
                               class="lang-switch-item<?= $language['code'] === $lang ? ' is-active' : '' ?>"<?= $language['code'] === $lang ? ' aria-current="true"' : '' ?>>
                                <?php if (isset($langFlags[$language['code']])): ?>
                                    <img src="<?= asset('front/img/flags/' . $langFlags[$language['code']] . '.svg') ?>" alt="" class="lang-switch-flag" width="20" height="14">
                                <?php endif ?>
                                <?= e($langLabels[$language['code']] ?? $language['code']) ?>
                            </a>
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>
    </div>
</nav>
