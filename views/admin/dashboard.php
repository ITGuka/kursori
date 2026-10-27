<?php
/**
 * @var Core\View\Template $this
 * @var array<string, array{label: string, icon: string, counts: array{total: int, active: int}, latest: list<array>}> $collections
 */
use App\Models\ActivityLog;
use App\Models\Media;
use Core\Support\Str;

$this->layout('admin/layouts/app', ['title' => 'Dashboard']);

// „15 წუთის წინ“ — კვირაზე ძველი თარიღი ჩვეულებრივად
$ago = function (string $datetime): string {
    $seconds = time() - strtotime($datetime);
    return match (true) {
        $seconds < 60     => 'ახლახან',
        $seconds < 3600   => intdiv($seconds, 60) . ' წთ-ის წინ',
        $seconds < 86400  => intdiv($seconds, 3600) . ' სთ-ის წინ',
        $seconds < 604800 => intdiv($seconds, 86400) . ' დღის წინ',
        default           => date('d.m.Y', strtotime($datetime)),
    };
};
$contentTotal = array_sum(array_map(fn ($c) => $c['counts']['total'], $collections));
$admin = auth()->user();
// კოლექციების ბარათების ფერები რიგრიგობით
$tones = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
$stats = [
    ['url' => route('admin.pages.index'), 'icon' => 'bx bx-file', 'tone' => 'primary', 'label' => 'გვერდები',
     'value' => $pages['published'], 'note' => '/ ' . $pages['total'] . ' გამოქვეყნებული'],
    ['url' => route('admin.inbox.index', ['unread' => 1]), 'icon' => 'bx bx-envelope', 'tone' => 'warning', 'label' => 'წაუკითხავი',
     'value' => $unread, 'note' => $unread > 0 ? 'ახალი შეტყობინება' : 'ყველა წაკითხულია'],
    ['url' => route('admin.newsletter.subscribers', ['status' => 'active']), 'icon' => 'bx bx-paper-plane', 'tone' => 'info', 'label' => 'გამომწერები',
     'value' => $subscribers['active'], 'note' => $subscribers['pending'] > 0 ? '+' . $subscribers['pending'] . ' ელოდება' : 'აქტიური'],
    ['url' => route('admin.media.index'), 'icon' => 'bx bx-images', 'tone' => 'success', 'label' => 'მედია',
     'value' => $media['count'], 'note' => Media::humanSize($media['size'])],
];
?>
<?php $this->start('styles') ?>
<link href="<?= asset('admin/sneat/css/apex-charts.css') ?>" rel="stylesheet">
<link href="<?= asset('admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') ?>" rel="stylesheet">
<?php $this->stop() ?>

<div class="row g-6 mb-6">
    <!-- მისალმება -->
    <div class="col-xxl-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">გამარჯობა, <?= e($admin['name']) ?>! 👋</h5>
                <p class="mb-6">
                    <?php if ($unread > 0): ?>
                        Inbox-ში <strong><?= e($unread) ?></strong> წაუკითხავი შეტყობინებაა.
                    <?php else: ?>
                        ახალი შეტყობინება არ არის.
                    <?php endif ?>
                    საიტზე გამოქვეყნებულია <?= e($pages['published']) ?> გვერდი და <?= e($contentTotal) ?> ჩანაწერი.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(route('admin.inbox.index', ['unread' => 1])) ?>" class="btn btn-sm btn-outline-primary"><i class="icon-base bx bx-envelope me-1"></i>Inbox</a>
                    <a href="<?= e(route('admin.pages.create')) ?>" class="btn btn-sm btn-outline-primary"><i class="icon-base bx bx-plus me-1"></i>ახალი გვერდი</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ძირითადი მაჩვენებლები -->
    <div class="col-xxl-4">
        <div class="row g-6">
            <?php foreach ($stats as $stat): ?>
                <div class="col-6">
                    <div class="card h-100 dash-stat">
                        <div class="card-body">
                            <div class="avatar flex-shrink-0 mb-4">
                                <span class="avatar-initial rounded bg-label-<?= e($stat['tone']) ?>"><i class="icon-base <?= e($stat['icon']) ?> icon-lg"></i></span>
                            </div>
                            <p class="mb-1"><a href="<?= e($stat['url']) ?>" class="stretched-link text-body"><?= e($stat['label']) ?></a></p>
                            <h4 class="card-title mb-1"><?= e($stat['value']) ?></h4>
                            <small class="text-body-secondary"><?= e($stat['note']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>

<!-- კოლექციები -->
<div class="row g-6 mb-6">
    <?php $i = 0 ?>
    <?php foreach ($collections as $type => $collection): ?>
        <?php $tone = $tones[$i++ % count($tones)]; $hidden = $collection['counts']['total'] - $collection['counts']['active']; ?>
        <div class="col-sm-6 col-lg-4 col-xxl">
            <div class="card h-100 dash-stat">
                <div class="card-body d-flex align-items-center gap-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-<?= e($tone) ?>"><i class="icon-base <?= e($collection['icon']) ?> icon-lg"></i></span>
                    </div>
                    <div class="min-w-0">
                        <a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>" class="stretched-link text-body d-block text-truncate"><?= e($collection['label']) ?></a>
                        <h5 class="mb-0"><?= e($collection['counts']['total']) ?>
                            <?php if ($hidden > 0): ?><small class="text-body-secondary fw-normal fs-tiny">· <?= e($hidden) ?> დამალული</small><?php endif ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<!-- დიაგრამები -->
<div class="row g-6 mb-6">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">შეტყობინებები და გამომწერები</h5>
                <small class="text-body-secondary">ბოლო 30 დღე</small>
            </div>
            <div class="card-body pt-0">
                <div id="chart-daily"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">კონტენტი</h5>
                <small class="text-body-secondary"><?= e($contentTotal) ?> ჩანაწერი</small>
            </div>
            <div class="card-body pt-0">
                <?php if ($contentTotal === 0): ?>
                    <p class="text-body-secondary mb-0">ჩანაწერები ჯერ არ არის.</p>
                <?php else: ?>
                    <div id="chart-content"></div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- ბოლოს დამატებული: თითო კოლექციაზე -->
<div class="row g-6 mb-6">
    <?php foreach ($collections as $type => $collection): ?>
        <div class="col-md-6 col-xxl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2"><i class="icon-base <?= e($collection['icon']) ?> text-primary me-2"></i><?= e($collection['label']) ?></h5>
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?= e(route('admin.collections.create', ['type' => $type])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="დამატება" aria-label="დამატება"><i class="icon-base bx bx-plus"></i></a>
                        <a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>">ყველა</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($collection['latest'] === []): ?>
                        <p class="text-body-secondary mb-0">ჩანაწერები ჯერ არ არის.</p>
                    <?php else: ?>
                        <ul class="p-0 m-0">
                            <?php foreach ($collection['latest'] as $j => $item): ?>
                                <?php $thumb = media_url($item['image_id'], 'thumb'); ?>
                                <li class="d-flex align-items-center<?= $j < count($collection['latest']) - 1 ? ' mb-5' : '' ?>">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <?php if ($thumb !== ''): ?>
                                            <img src="<?= e($thumb) ?>" alt="" class="rounded dash-thumb">
                                        <?php else: ?>
                                            <span class="avatar-initial rounded bg-label-secondary"><i class="icon-base <?= e($collection['icon']) ?>"></i></span>
                                        <?php endif ?>
                                    </div>
                                    <div class="d-flex w-100 align-items-center justify-content-between gap-2 min-w-0">
                                        <div class="min-w-0">
                                            <a href="<?= e(route('admin.collections.edit', ['id' => $item['id']])) ?>" class="d-block text-heading text-truncate fw-medium"><?= e($item['title']) ?></a>
                                            <small class="text-body-secondary">
                                                <?= e($item['category_name'] ?? '') ?>
                                                <?php if (!$item['is_active']): ?><span class="badge bg-label-secondary ms-1">დამალული</span><?php endif ?>
                                            </small>
                                        </div>
                                        <small class="text-body-secondary text-nowrap"><?= e($ago($item['created_at'])) ?></small>
                                    </div>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<!-- ბოლოს შეცვლილი გვერდები -->
<div class="card mb-6">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">ბოლოს შეცვლილი გვერდები</h5>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= e(route('admin.pages.create')) ?>" class="btn btn-sm btn-primary"><i class="icon-base bx bx-plus me-1"></i>ახალი გვერდი</a>
            <a href="<?= e(route('admin.pages.index')) ?>">ყველა</a>
        </div>
    </div>
    <?php if ($latestPages === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">გვერდები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>სათაური</th>
                    <th>სტატუსი</th>
                    <th>ბლოკები</th>
                    <th>შეცვლილია</th>
                    <th class="text-end"></th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($latestPages as $row): ?>
                    <tr>
                        <td>
                            <span class="fw-medium text-heading"><?= e($row['title']) ?></span>
                            <?php if ($row['is_home']): ?><i class="icon-base bx bx-home text-primary ms-1" title="მთავარი გვერდი"></i><?php endif ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'published'): ?>
                                <span class="badge bg-label-success">გამოქვეყნებული</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">მონახაზი</span>
                            <?php endif ?>
                        </td>
                        <td><?= e($row['blocks']) ?></td>
                        <td><?= e($ago($row['updated_at'])) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-icon btn-outline-secondary border-0" href="<?= e(route('admin.pages.edit', ['id' => $row['id']])) ?>" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<!-- ვიჯეტები -->
<div class="row g-6 mb-6">
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">შეტყობინებები</h5>
                <a href="<?= e(route('admin.inbox.index')) ?>">ყველა</a>
            </div>
            <div class="card-body">
                <?php if ($messages === []): ?>
                    <p class="text-body-secondary mb-0">შეტყობინებები ჯერ არ არის.</p>
                <?php else: ?>
                    <ul class="p-0 m-0">
                        <?php foreach ($messages as $j => $message): ?>
                            <?php $name = (string)($message['data']['name'] ?? $message['data']['email'] ?? '—'); ?>
                            <li class="d-flex align-items-center<?= $j < count($messages) - 1 ? ' mb-5' : '' ?>">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
                                </div>
                                <div class="w-100 min-w-0">
                                    <div class="d-flex justify-content-between gap-2">
                                        <a href="<?= e(route('admin.inbox.show', ['id' => $message['id']])) ?>" class="text-heading fw-medium text-truncate"><?= e($name) ?></a>
                                        <small class="text-body-secondary text-nowrap"><?= e($ago($message['created_at'])) ?></small>
                                    </div>
                                    <small class="d-block text-truncate text-body-secondary">
                                        <?php if (!$message['is_read']): ?><span class="dash-dot me-1" title="ახალი"></span><?php endif ?>
                                        <i class="icon-base <?= e($forms[$message['form']]['icon'] ?? 'bx bx-envelope') ?> icon-sm text-primary me-1"></i><?= e(Str::limit(trim((string)($message['data']['subject'] ?? '') . ' ' . (string)($message['data']['message'] ?? '')), 60) ?: ($forms[$message['form']]['label'] ?? $message['form'])) ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">კალენდარი</h5>
                <small class="text-body-secondary"><?= e(date('d.m.Y')) ?></small>
            </div>
            <div class="card-body">
                <div id="calender"></div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-xl-4">
        <div class="card h-100" data-todos data-url="<?= e(route('admin.todos.store')) ?>">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">დავალებები</h5>
                <small class="text-body-secondary">მხოლოდ თქვენთვის</small>
            </div>
            <div class="card-body">
                <form class="d-flex gap-2 mb-2" data-todo-form>
                    <input class="form-control" type="text" name="text" maxlength="255" placeholder="ახალი დავალება" aria-label="ახალი დავალება" required>
                    <button type="submit" class="btn btn-primary">დამატება</button>
                </form>
                <div class="text-danger small d-none" data-todo-error></div>
                <div data-todo-list>
                    <?php foreach ($todos as $todo): ?>
                        <?= $this->insert('admin/partials/todo-item', ['todo' => $todo]) ?>
                    <?php endforeach ?>
                </div>
                <p class="text-body-secondary small mb-0 pt-2<?= $todos === [] ? '' : ' d-none' ?>" data-todo-empty>დავალებები არ არის.</p>
                <template data-todo-template><?= $this->insert('admin/partials/todo-item', ['todo' => ['id' => 0, 'text' => '', 'is_done' => 0]]) ?></template>
            </div>
        </div>
    </div>
</div>

<!-- ბოლო აქტივობა -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">ბოლო აქტივობა</h5>
        <small class="text-body-secondary"><i class="icon-base bx bx-group icon-sm me-1"></i><?= e($activeCount) ?> / <?= e($usersCount) ?> აქტიური ადმინი</small>
    </div>
    <?php if ($activity === []): ?>
        <div class="card-body pt-0"><p class="mb-0 text-body-secondary">ჩანაწერები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>დრო</th>
                    <th>მომხმარებელი</th>
                    <th>ქმედება</th>
                    <th>დეტალები</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($activity as $row): ?>
                    <tr>
                        <td class="text-nowrap" title="<?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?>"><?= e($ago($row['created_at'])) ?></td>
                        <td class="text-nowrap"><?= e($row['user_name'] ?? '—') ?></td>
                        <td><?= e(ActivityLog::LABELS[$row['action']] ?? $row['action']) ?></td>
                        <td><?= e(ActivityLog::summary($row['data'])) ?></td>
                        <td class="text-nowrap"><?= e($row['ip']) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<?php $this->start('scripts') ?>
<script>
    window.DashboardData = <?= json_encode([
        'daily'   => $daily,
        'content' => [
            'labels' => array_values(array_column($collections, 'label')),
            'values' => array_values(array_map(fn ($c) => $c['counts']['total'], $collections)),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= asset('admin/sneat/js/apexcharts.min.js') ?>"></script>
<script src="<?= asset('admin/lib/tempusdominus/js/moment.min.js') ?>"></script>
<script src="<?= asset('admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
<script src="<?= asset('admin/js/dashboard.js') ?>"></script>
<?php $this->stop() ?>
