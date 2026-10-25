<?php
/**
 * @var Core\View\Template $this
 * @var array<string, array{label: string, icon: string, counts: array{total: int, active: int}, latest: list<array>}> $collections
 */
use App\Models\ActivityLog;
use App\Models\Media;
use Core\Support\Str;

$this->layout('admin/layouts/app');

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
?>
<?php $this->start('styles') ?>
<link href="<?= asset('admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') ?>" rel="stylesheet">
<?php $this->stop() ?>

<!-- ძირითადი მაჩვენებლები -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="<?= e(route('admin.pages.index')) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset dash-stat">
            <i class="fa fa-file-alt fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">გვერდები</p>
                <h6 class="mb-0"><?= e($pages['published']) ?> <small class="text-muted fw-normal">/ <?= e($pages['total']) ?> გამოქვეყნებული</small></h6>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= e(route('admin.inbox.index', ['unread' => 1])) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset dash-stat">
            <i class="fa fa-inbox fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">წაუკითხავი შეტყობინებები</p>
                <h6 class="mb-0"><?= e($unread) ?><?php if ($unread > 0): ?> <span class="badge bg-primary align-text-top">ახალი</span><?php endif ?></h6>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= e(route('admin.newsletter.subscribers', ['status' => 'active'])) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset dash-stat">
            <i class="fa fa-paper-plane fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">აქტიური გამომწერები</p>
                <h6 class="mb-0"><?= e($subscribers['active']) ?><?php if ($subscribers['pending'] > 0): ?> <small class="text-muted fw-normal">+<?= e($subscribers['pending']) ?> ელოდება</small><?php endif ?></h6>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= e(route('admin.media.index')) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset dash-stat">
            <i class="fa fa-images fa-3x text-primary"></i>
            <div class="ms-3 text-end">
                <p class="mb-2">მედია ფაილები</p>
                <h6 class="mb-0"><?= e($media['count']) ?> <small class="text-muted fw-normal">· <?= e(Media::humanSize($media['size'])) ?></small></h6>
            </div>
        </a>
    </div>
</div>

<!-- კოლექციები -->
<div class="row g-4 mb-4">
    <?php foreach ($collections as $type => $collection): ?>
        <div class="col-sm-6 col-xl-3">
            <a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>" class="bg-light rounded d-flex align-items-center justify-content-between p-4 text-reset dash-stat">
                <i class="<?= e($collection['icon']) ?> fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2"><?= e($collection['label']) ?></p>
                    <h6 class="mb-0"><?= e($collection['counts']['total']) ?>
                        <?php if ($collection['counts']['active'] < $collection['counts']['total']): ?>
                            <small class="text-muted fw-normal">· <?= e($collection['counts']['total'] - $collection['counts']['active']) ?> დამალული</small>
                        <?php endif ?>
                    </h6>
                </div>
            </a>
        </div>
    <?php endforeach ?>
</div>

<!-- დიაგრამები -->
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="bg-light rounded p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">შეტყობინებები და გამომწერები — 30 დღე</h6>
                <a href="<?= e(route('admin.inbox.index')) ?>">Inbox</a>
            </div>
            <div class="dash-chart"><canvas id="chart-daily"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="bg-light rounded p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">კონტენტი</h6>
                <span class="text-muted small"><?= e($contentTotal) ?> ჩანაწერი</span>
            </div>
            <?php if ($contentTotal === 0): ?>
                <p class="text-muted mb-0">ჩანაწერები ჯერ არ არის.</p>
            <?php else: ?>
                <div class="dash-chart"><canvas id="chart-content"></canvas></div>
            <?php endif ?>
        </div>
    </div>
</div>

<!-- ბოლოს დამატებული: თითო კოლექციაზე -->
<div class="row g-4 mb-4">
    <?php foreach ($collections as $type => $collection): ?>
        <div class="col-md-6 col-xxl-3">
            <div class="h-100 bg-light rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0"><i class="<?= e($collection['icon']) ?> text-primary me-2"></i><?= e($collection['label']) ?></h6>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= e(route('admin.collections.create', ['type' => $type])) ?>" class="btn btn-sm btn-primary btn-sm-square" title="დამატება"><i class="fa fa-plus"></i></a>
                        <a href="<?= e(route('admin.collections.index', ['type' => $type])) ?>">ყველა</a>
                    </div>
                </div>
                <?php if ($collection['latest'] === []): ?>
                    <p class="text-muted small mb-0 pt-2">ჩანაწერები ჯერ არ არის.</p>
                <?php endif ?>
                <?php foreach ($collection['latest'] as $i => $item): ?>
                    <?php $thumb = media_url($item['image_id'], 'thumb'); ?>
                    <a href="<?= e(route('admin.collections.edit', ['id' => $item['id']])) ?>"
                       class="d-flex align-items-center text-reset py-3<?= $i < count($collection['latest']) - 1 ? ' border-bottom' : ' pb-0' ?>">
                        <?php if ($thumb !== ''): ?>
                            <img class="rounded-circle flex-shrink-0 dash-thumb" src="<?= e($thumb) ?>" alt="">
                        <?php else: ?>
                            <span class="rounded-circle flex-shrink-0 dash-thumb dash-thumb-empty"><i class="<?= e($collection['icon']) ?>"></i></span>
                        <?php endif ?>
                        <div class="w-100 ms-3 min-w-0">
                            <div class="d-flex w-100 justify-content-between gap-2">
                                <h6 class="mb-0 text-truncate"><?= e($item['title']) ?></h6>
                                <small class="text-nowrap"><?= e($ago($item['created_at'])) ?></small>
                            </div>
                            <span class="small text-muted">
                                <?= e($item['category_name'] ?? '') ?>
                                <?php if (!$item['is_active']): ?><span class="badge bg-secondary">დამალული</span><?php endif ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    <?php endforeach ?>
</div>

<!-- ბოლოს შეცვლილი გვერდები -->
<div class="bg-light rounded p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0">ბოლოს შეცვლილი გვერდები</h6>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= e(route('admin.pages.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>ახალი გვერდი</a>
            <a href="<?= e(route('admin.pages.index')) ?>">ყველა</a>
        </div>
    </div>
    <?php if ($latestPages === []): ?>
        <p class="text-muted mb-0">გვერდები ჯერ არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                <tr class="text-dark">
                    <th>სათაური</th>
                    <th>სტატუსი</th>
                    <th>ბლოკები</th>
                    <th>შეცვლილია</th>
                    <th class="text-end"></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latestPages as $row): ?>
                    <tr>
                        <td><?= e($row['title']) ?><?php if ($row['is_home']): ?> <i class="fa fa-home text-primary ms-1" title="მთავარი გვერდი"></i><?php endif ?></td>
                        <td>
                            <?php if ($row['status'] === 'published'): ?>
                                <span class="badge bg-success">გამოქვეყნებული</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">მონახაზი</span>
                            <?php endif ?>
                        </td>
                        <td><?= e($row['blocks']) ?></td>
                        <td class="text-nowrap"><?= e($ago($row['updated_at'])) ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-primary" href="<?= e(route('admin.pages.edit', ['id' => $row['id']])) ?>">რედაქტირება</a></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<!-- ვიჯეტები -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="h-100 bg-light rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0">შეტყობინებები</h6>
                <a href="<?= e(route('admin.inbox.index')) ?>">ყველა</a>
            </div>
            <?php if ($messages === []): ?>
                <p class="text-muted small mb-0 pt-2">შეტყობინებები ჯერ არ არის.</p>
            <?php endif ?>
            <?php foreach ($messages as $i => $message): ?>
                <?php $name = (string)($message['data']['name'] ?? $message['data']['email'] ?? '—'); ?>
                <a href="<?= e(route('admin.inbox.show', ['id' => $message['id']])) ?>"
                   class="d-flex align-items-center text-reset py-3<?= $i < count($messages) - 1 ? ' border-bottom' : ' pb-0' ?>">
                    <span class="admin-avatar flex-shrink-0"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
                    <div class="w-100 ms-3 min-w-0">
                        <div class="d-flex w-100 justify-content-between gap-2">
                            <h6 class="mb-0 text-truncate"><?= e($name) ?><?php if (!$message['is_read']): ?> <span class="badge bg-primary">ახალი</span><?php endif ?></h6>
                            <small class="text-nowrap"><?= e($ago($message['created_at'])) ?></small>
                        </div>
                        <span class="small d-block text-truncate">
                            <i class="<?= e($forms[$message['form']]['icon'] ?? 'fa fa-envelope') ?> text-primary me-1"></i><?= e(Str::limit(trim((string)($message['data']['subject'] ?? '') . ' ' . (string)($message['data']['message'] ?? '')), 60) ?: ($forms[$message['form']]['label'] ?? $message['form'])) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="h-100 bg-light rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">კალენდარი</h6>
                <span class="text-muted small"><?= e(date('d.m.Y')) ?></span>
            </div>
            <div id="calender"></div>
        </div>
    </div>
    <div class="col-md-12 col-xl-4">
        <div class="h-100 bg-light rounded p-4" data-todos data-url="<?= e(route('admin.todos.store')) ?>">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">დავალებები</h6>
                <span class="text-muted small">მხოლოდ თქვენთვის</span>
            </div>
            <form class="d-flex mb-2" data-todo-form>
                <input class="form-control bg-transparent" type="text" name="text" maxlength="255" placeholder="ახალი დავალება" aria-label="ახალი დავალება" required>
                <button type="submit" class="btn btn-primary ms-2">დამატება</button>
            </form>
            <div class="text-danger small d-none" data-todo-error></div>
            <div data-todo-list>
                <?php foreach ($todos as $todo): ?>
                    <?= $this->insert('admin/partials/todo-item', ['todo' => $todo]) ?>
                <?php endforeach ?>
            </div>
            <p class="text-muted small mb-0 pt-2<?= $todos === [] ? '' : ' d-none' ?>" data-todo-empty>დავალებები არ არის.</p>
            <template data-todo-template><?= $this->insert('admin/partials/todo-item', ['todo' => ['id' => 0, 'text' => '', 'is_done' => 0]]) ?></template>
        </div>
    </div>
</div>

<!-- ბოლო აქტივობა -->
<div class="bg-light rounded p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0">ბოლო აქტივობა</h6>
        <span class="text-muted small"><i class="fa fa-users me-1"></i><?= e($activeCount) ?> / <?= e($usersCount) ?> აქტიური ადმინი</span>
    </div>
    <?php if ($activity === []): ?>
        <p class="mb-0 text-muted">ჩანაწერები ჯერ არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                <tr class="text-dark">
                    <th>დრო</th>
                    <th>მომხმარებელი</th>
                    <th>ქმედება</th>
                    <th>დეტალები</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($activity as $row): ?>
                    <tr>
                        <td class="text-nowrap" title="<?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?>"><?= e($ago($row['created_at'])) ?></td>
                        <td><?= e($row['user_name'] ?? '—') ?></td>
                        <td><?= e(ActivityLog::LABELS[$row['action']] ?? $row['action']) ?></td>
                        <td><?= e(ActivityLog::summary($row['data'])) ?></td>
                        <td><?= e($row['ip']) ?></td>
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
<script src="<?= asset('admin/lib/chart/chart.min.js') ?>"></script>
<script src="<?= asset('admin/lib/tempusdominus/js/moment.min.js') ?>"></script>
<script src="<?= asset('admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
<script src="<?= asset('admin/js/dashboard.js') ?>"></script>
<?php $this->stop() ?>
