<?php
/** @var Core\View\Template $this */
$admin = auth()->user();
$initial = mb_strtoupper(mb_substr((string)$admin['name'], 0, 1));
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
                    <a href="<?= e(route($item['route'])) ?>" class="nav-item nav-link<?= is_route($item['active']) ? ' active' : '' ?>">
                        <i class="<?= e($item['icon']) ?> me-2"></i><?= e($item['label']) ?>
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
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-start">
                        &copy; <?= date('Y') ?> <?= e(config('app.name')) ?>
                    </div>
                    <div class="col-12 col-sm-6 text-center text-sm-end">
                        <!-- HTML Codex ატრიბუცია — იშლება Credit Removal ლიცენზიის შეძენის შემდეგ (ფაზა 9, D11) -->
                        Designed By <a href="https://htmlcodex.com">HTML Codex</a>
                    </div>
                </div>
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
