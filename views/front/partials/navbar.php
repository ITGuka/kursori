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
?>
<nav class="navbar navbar-expand-lg navbar-dark px-lg-5">
    <a href="<?= e(url($lang)) ?>" class="navbar-brand ms-4 ms-lg-0">
        <?php if ($logoUrl = media_url($logo, 'medium')): ?>
            <img src="<?= e($logoUrl) ?>" alt="<?= e(media_alt($logo) ?: $siteName) ?>" class="site-logo">
        <?php else: ?>
            <h2 class="mb-0 text-primary text-uppercase"><i class="fa-regular fa-face-smile me-1"></i><?= e($siteName) ?></h2>
        <?php endif ?>
    </a>
    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-label="<?= e(t('nav.menu')) ?>">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav mx-auto p-4 p-lg-0">
            <?php foreach ($nav as $item): ?>
                <?php if ($item['children'] === []): ?>
                    <a href="<?= e($item['href']) ?>"<?= link_target($item) ?> class="nav-item nav-link<?= $isActive($item['href']) ? ' active' : '' ?>"
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
        <?php if ($cta !== null): ?>
            <div class="px-4 px-lg-0 pb-3 pb-lg-0 me-lg-3">
                <a href="<?= e($cta['href']) ?>" class="btn btn-primary border-2 px-4 navbar-cta"<?= $isActive($cta['href']) ? ' aria-current="page"' : '' ?>><?= e($cta['label']) ?></a>
            </div>
        <?php endif ?>
        <?php if (count($languageLinks) > 1): ?>
            <div class="d-flex gap-2 px-4 px-lg-0 pb-3 pb-lg-0" aria-label="<?= e(t('common.language')) ?>">
                <?php foreach ($languages ?? [] as $language): ?>
                    <?php if (isset($languageLinks[$language['code']])): ?>
                        <a href="<?= e($languageLinks[$language['code']]) ?>" hreflang="<?= e($language['code']) ?>" lang="<?= e($language['code']) ?>"
                           class="btn btn-sm <?= $language['code'] === $lang ? 'btn-primary' : 'btn-outline-primary' ?> border-2 text-uppercase"><?= e($language['code']) ?></a>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</nav>
