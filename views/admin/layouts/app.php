<?php
/** @var Core\View\Template $this */
$admin = auth()->user();
$initial = mb_strtoupper(mb_substr((string)$admin['name'], 0, 1));
// ფორმის ენის გადამრთველი (partials/lang-switch): [data-lang="xx"] ელემენტები ჩანს მხოლოდ არჩეულ ენაზე
$langCodes = count($languages ?? []) > 1 ? array_column($languages, 'code') : [];
?>
<!doctype html>
<html lang="ka">
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
<div class="container-fluid position-relative bg-white d-flex p-0">

    <!-- Sidebar -->
    <div class="sidebar pe-4 pb-3">
        <nav class="navbar bg-light navbar-light">
            <a href="<?= e(route('admin.dashboard')) ?>" class="navbar-brand mx-4 mb-3">
                <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i><?= e(config('app.name')) ?></h3>
            </a>
            <div class="d-flex align-items-center ms-4 mb-4">
                <div class="admin-avatar"><?= e($initial) ?></div>
                <div class="ms-3">
                    <h6 class="mb-0"><?= e($admin['name']) ?></h6>
                    <span>ადმინი</span>
                </div>
            </div>
            <div class="navbar-nav w-100">
                <?php foreach (config('admin.menu', []) as $item): ?>
                    <?php $active = isset($item['collection']) ? ($collectionType ?? null) === $item['collection'] : is_route(...(array)$item['active']); ?>
                    <a href="<?= e(route($item['route'], $item['params'] ?? [])) ?>" class="nav-item nav-link<?= $active ? ' active' : '' ?>">
                        <i class="<?= e($item['icon']) ?> me-2"></i><?= e($item['label']) ?>
                        <?php if (($item['badge'] ?? '') === 'inbox' && ($unreadTotal = array_sum(app(App\Models\FormSubmission::class)->unreadCounts())) > 0): ?>
                            <span class="badge rounded-pill bg-danger ms-1"><?= e($unreadTotal) ?></span>
                        <?php endif ?>
                    </a>
                <?php endforeach ?>
            </div>
        </nav>
    </div>

    <!-- Content -->
    <div class="content">
        <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
            <a href="<?= e(route('admin.dashboard')) ?>" class="navbar-brand d-flex d-lg-none me-4">
                <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
            </a>
            <a href="#" class="sidebar-toggler flex-shrink-0" aria-label="მენიუ">
                <i class="fa fa-bars"></i>
            </a>
            <div class="navbar-nav align-items-center ms-auto">
                <a href="<?= e(url(config('app.default_locale'))) ?>" class="nav-link" target="_blank" rel="noopener">
                    <i class="fa fa-external-link-alt me-lg-2"></i><span class="d-none d-lg-inline-flex">საიტი</span>
                </a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="admin-avatar admin-avatar-sm me-lg-2"><?= e($initial) ?></span>
                        <span class="d-none d-lg-inline-flex"><?= e($admin['name']) ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="<?= e(route('admin.users.edit', ['id' => $admin['id']])) ?>" class="dropdown-item">ჩემი პროფილი</a>
                        <form method="post" action="<?= e(route('admin.logout')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="dropdown-item">გასვლა</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid pt-4 px-4">
            <?= $this->insert('admin/partials/flash') ?>
            <?= $this->section('content') ?>
        </div>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-light rounded-top p-4">
                <div class="text-center text-sm-start">&copy; <?= date('Y') ?> <?= e(config('app.name')) ?></div>
            </div>
        </div>
    </div>

    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>
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
