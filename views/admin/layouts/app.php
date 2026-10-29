<?php
/** @var Core\View\Template $this */
$admin = auth()->user();
$initial = mb_strtoupper(mb_substr((string)$admin['name'], 0, 1));
// ფორმის ენის გადამრთველი (partials/lang-switch): [data-lang="xx"] ელემენტები ჩანს მხოლოდ არჩეულ ენაზე
$langCodes = count($languages ?? []) > 1 ? array_column($languages, 'code') : [];
?>
<!doctype html>
<html lang="ka" class="layout-menu-fixed layout-compact" data-bs-theme="light" data-skin="default" data-template="vertical-menu-template">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(($title ?? 'Admin') . ' · ' . config('app.name')) ?></title>
    <?= $this->insert('admin/partials/head') ?>
    <?= $this->section('styles') ?>
    <?php if ($langCodes !== []): ?>
        <link href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" rel="stylesheet">
        <style>
            <?php foreach ($langCodes as $code): ?>
            html[data-admin-lang="<?= e($code) ?>"] [data-lang]:not([data-lang="<?= e($code) ?>"]) { display: none !important; }
            <?php endforeach ?>
        </style>
        <script>
            // არჩეული ენა გვერდის დახატვამდე — რომ ველები არ „ახტეს“
            (function () {
                var codes = <?= json_encode($langCodes) ?>, lang = null;
                try { lang = localStorage.getItem('admin.lang'); } catch (e) {}
                if (lang !== 'all' && codes.indexOf(lang) === -1) lang = <?= json_encode(config('app.default_locale')) ?>;
                document.documentElement.setAttribute('data-admin-lang', lang);
            })();
        </script>
    <?php endif ?>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- Sidebar -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="<?= e(route('admin.dashboard')) ?>" class="app-brand-link">
                    <span class="app-brand-logo demo admin-logo"><?= e(mb_substr((string)config('app.name'), 0, 1)) ?></span>
                    <span class="app-brand-text demo menu-text fw-bold ms-2"><?= e(config('app.name')) ?></span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none" aria-label="მენიუს დახურვა">
                    <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
                </a>
            </div>

            <div class="menu-divider mt-0"></div>
            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                <?php foreach (config('admin.menu', []) as $item): ?>
                    <?php if (isset($item['header'])): ?>
                        <li class="menu-header small text-uppercase"><span class="menu-header-text"><?= e($item['header']) ?></span></li>
                        <?php continue ?>
                    <?php endif ?>
                    <?php $active = isset($item['collection']) ? ($collectionType ?? null) === $item['collection'] : is_route(...(array)$item['active']); ?>
                    <li class="menu-item<?= $active ? ' active' : '' ?>">
                        <a href="<?= e(route($item['route'], $item['params'] ?? [])) ?>" class="menu-link">
                            <i class="menu-icon icon-base <?= e($item['icon']) ?>"></i>
                            <div><?= e($item['label']) ?></div>
                            <?php if (($item['badge'] ?? '') === 'inbox' && ($unreadTotal = array_sum(app(App\Models\FormSubmission::class)->unreadCounts())) > 0): ?>
                                <div class="badge rounded-pill bg-danger ms-auto"><?= e($unreadTotal) ?></div>
                            <?php elseif (($item['badge'] ?? '') === 'news' && ($pendingNews = app(App\Models\NewsItem::class)->counts()['new']) > 0): ?>
                                <div class="badge rounded-pill bg-warning ms-auto" title="მოდერაციაში"><?= e($pendingNews) ?></div>
                            <?php endif ?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </aside>

        <!-- Page -->
        <div class="layout-page">
            <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" aria-label="მენიუ">
                        <i class="icon-base bx bx-menu icon-md"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                    <?php if (!empty($title)): ?>
                        <div class="navbar-nav align-items-center me-auto">
                            <span class="fw-medium text-heading"><?= e($title) ?></span>
                        </div>
                    <?php endif ?>
                    <ul class="navbar-nav flex-row align-items-center ms-auto gap-2">
                        <li class="nav-item">
                            <a href="<?= e(url(config('app.default_locale'))) ?>" class="nav-link d-flex align-items-center" target="_blank" rel="noopener">
                                <i class="icon-base bx bx-link-external icon-md me-sm-1"></i><span class="d-none d-sm-inline">საიტი</span>
                            </a>
                        </li>
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="პროფილი">
                                <div class="avatar avatar-online">
                                    <span class="avatar-initial rounded-circle bg-label-primary"><?= e($initial) ?></span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-item-text d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online"><span class="avatar-initial rounded-circle bg-label-primary"><?= e($initial) ?></span></div>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h6 class="mb-0 text-truncate"><?= e($admin['name']) ?></h6>
                                            <small class="text-body-secondary">ადმინი</small>
                                        </div>
                                    </div>
                                </li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li>
                                    <a class="dropdown-item" href="<?= e(route('admin.users.edit', ['id' => $admin['id']])) ?>">
                                        <i class="icon-base bx bx-user icon-md me-3"></i><span>ჩემი პროფილი</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= e(route('admin.settings.index')) ?>">
                                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>პარამეტრები</span>
                                    </a>
                                </li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li>
                                    <form method="post" action="<?= e(route('admin.logout')) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item">
                                            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>გასვლა</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <?= $this->insert('admin/partials/flash') ?>
                    <?= $this->section('content') ?>
                </div>

                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">&copy; <?= date('Y') ?> <?= e(config('app.name')) ?></div>
                        </div>
                    </div>
                </footer>

                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
</div>

<?= $this->insert('admin/partials/media-picker') ?>

<script>
    window.AdminConfig = <?= json_encode([
        'mediaUpload' => route('admin.media.store'),
        'mediaPicker' => route('admin.media.picker'),
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<?= $this->insert('admin/partials/scripts') ?>
<script src="<?= asset('admin/js/media.js') ?>"></script>
<?= $this->section('scripts') ?>
</body>
</html>
