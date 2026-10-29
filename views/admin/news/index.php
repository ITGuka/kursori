<?php
/**
 * სიახლეების ჟურნალი: რა გამოქვეყნდა და რა ჩავარდა (ფილტრი სტატუსით, feed-ით, ძებნა), ჯობის მდგომარეობა, „ახლავე“.
 * @var Core\View\Template $this
 * @var list<array> $rows
 * @var array<string, int> $counts
 * @var array<int, string> $feeds
 * @var array|null $job news:run
 */
$this->layout('admin/layouts/app');
$statuses = App\Models\NewsItem::STATUSES;
$tabLink = fn (string $status) => route('admin.news.index', array_filter(['status' => $status, 'feed' => $filters['feed'] ?: null]));
$date = fn (?string $value) => $value ? e(date('d.m.Y H:i', strtotime($value))) : '—';
?>
<?= $this->insert('admin/news/tabs', ['active' => 'log']) ?>

<?php if (!$translated): ?>
    <div class="alert alert-warning">ქართული თარგმანი გამორთულია ან Claude API გასაღები არ არის — სიახლეები მხოლოდ ინგლისურ ბლოგში გამოჩნდება. <a href="<?= e(route('admin.settings.index', ['tab' => 'news'])) ?>">პარამეტრები → სიახლეები</a></div>
<?php endif ?>

<div class="card mb-6">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <p class="mb-1">ყოველ <?= e((int)($job['interval_minutes'] ?? 360) / 60) ?> საათში ერთხელ: უახლესი სტატია dev.to-დან, <?= e(App\Services\News\NewsFetcher::REACTIONS_ABOVE) ?>-ზე მეტი რეაქციით, კატეგორიაში დღეში ერთი — თარგმანით, პირდაპირ ბლოგში.</p>
            <small class="text-body-secondary">
                ბოლო გაშვება: <?= $date($job['last_run_at'] ?? null) ?>
                <?php if (($job['last_status'] ?? '') === 'skipped' && $job['last_message']): ?> (<?= e($job['last_message']) ?>)<?php endif ?>
                · შემდეგი: <?= $job && $job['is_active'] ? ($job['next_run_at'] === null || strtotime($job['next_run_at']) <= time() ? 'ახლა (შემდეგ ტიკზე)' : $date($job['next_run_at'])) : 'გათიშულია' ?>
            </small>
        </div>
        <form method="post" action="<?= e(route('admin.news.run_now')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline-primary" title="cron-ის შემდეგ ტიკზე"><i class="icon-base bx bx-play me-1"></i>ახლავე</button>
        </form>
    </div>
</div>

<div class="nav-align-top">
    <ul class="nav nav-tabs flex-wrap">
        <li class="nav-item"><a class="nav-link<?= $filters['status'] === '' ? ' active' : '' ?>" href="<?= e($tabLink('')) ?>">ყველა</a></li>
        <?php foreach ($statuses as $key => [$label]): ?>
            <li class="nav-item"><a class="nav-link<?= $filters['status'] === $key ? ' active' : '' ?>" href="<?= e($tabLink($key)) ?>"><?= e($label) ?> <span class="badge rounded-pill bg-label-secondary ms-1"><?= e($counts[$key]) ?></span></a></li>
        <?php endforeach ?>
    </ul>
</div>

<div class="card rounded-top-0">
    <div class="card-header">
        <form method="get" class="d-flex flex-wrap gap-2" style="max-width: 640px">
            <?php if ($filters['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif ?>
            <select name="feed" class="form-select" style="max-width: 220px" aria-label="Feed" onchange="this.form.submit()">
                <option value="">ყველა feed</option>
                <?php foreach ($feeds as $id => $name): ?>
                    <option value="<?= e($id) ?>"<?= $filters['feed'] === $id ? ' selected' : '' ?>><?= e($name) ?></option>
                <?php endforeach ?>
            </select>
            <div class="input-group input-group-merge flex-grow-1" style="max-width: 300px">
                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                <input type="search" name="q" class="form-control" placeholder="ძებნა სათაურში" value="<?= e($filters['q']) ?>" aria-label="ძებნა">
            </div>
            <button class="btn btn-outline-primary">ძებნა</button>
        </form>
    </div>

    <?php if ($rows === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">სიახლეები ჯერ არ არის — პირველი ჯობის შემდეგ გაჩნდება.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>სიახლე</th><th>Feed</th><th>რეაქცია</th><th>სტატუსი</th><th>გამოქვეყნდა</th><th class="text-end"></th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($rows as $row): [$statusLabel, $statusColor] = $statuses[$row['status']] ?? [$row['status'], 'secondary']; ?>
                    <tr>
                        <td style="min-width: 320px">
                            <div class="d-flex gap-3">
                                <?php if ($row['image_url']): ?>
                                    <img src="<?= e($row['image_url']) ?>" alt="" width="96" height="40" class="rounded flex-shrink-0 object-fit-cover" loading="lazy" referrerpolicy="no-referrer">
                                <?php endif ?>
                                <div class="min-w-0">
                                    <a href="<?= e(route('admin.news.show', ['id' => $row['id']])) ?>" class="fw-medium text-heading d-block"><?= e($row['title']) ?></a>
                                    <small class="text-body-secondary">
                                        <?= e($row['author_name'] ?? '') ?><?= $row['source_published_at'] ? ' · ' . e(date('d.m.Y', strtotime($row['source_published_at']))) : '' ?> · <?= e($row['reading_minutes']) ?> წთ
                                    </small>
                                    <?php if ($row['error']): ?><div class="small text-danger text-break mt-1"><?= e(mb_substr($row['error'], 0, 300)) ?></div><?php endif ?>
                                </div>
                            </div>
                        </td>
                        <td class="small text-nowrap"><?= e($row['feed_name'] ?? '—') ?></td>
                        <td class="text-nowrap"><i class="icon-base bx bx-heart icon-sm text-danger"></i> <?= e($row['reactions']) ?></td>
                        <td class="text-nowrap">
                            <span class="badge bg-label-<?= e($statusColor) ?>"><?= e($statusLabel) ?></span>
                            <?php if ($row['status'] === 'published'): ?>
                                <?php foreach (['en', 'ka'] as $lang): ?>
                                    <span class="badge bg-label-<?= in_array($lang, $row['langs'], true) ? 'primary' : 'secondary opacity-50' ?>" title="<?= in_array($lang, $row['langs'], true) ? 'არის' : 'არ არის' ?>"><?= e(strtoupper($lang)) ?></span>
                                <?php endforeach ?>
                            <?php endif ?>
                        </td>
                        <td class="small text-nowrap"><?= $date($row['published_at']) ?></td>
                        <td class="text-end">
                            <div class="table-actions">
                                <?php if ($row['collection_item_id']): ?>
                                    <a href="<?= e(route('admin.collections.edit', ['id' => $row['collection_item_id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="ბლოგის სტატია" aria-label="ბლოგის სტატია"><i class="icon-base bx bx-edit-alt"></i></a>
                                <?php endif ?>
                                <a href="<?= e($row['url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-outline-secondary" title="dev.to-ზე" aria-label="dev.to-ზე"><i class="icon-base bx bx-link-external"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1): ?><div class="card-footer border-top"><?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?></div><?php endif ?>
    <?php endif ?>
</div>
