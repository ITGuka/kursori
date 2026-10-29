<?php
/**
 * სიახლეების რიგი: სტატუსის ფილტრი, feed, ძებნა; მონიშვნა და დამტკიცება / გამოქვეყნება / უარყოფა.
 * @var Core\View\Template $this
 * @var list<array> $rows
 * @var array<string, int> $counts
 * @var array<int, string> $feeds
 */
$this->layout('admin/layouts/app');
$statuses = App\Models\NewsItem::STATUSES;
$query = array_filter(['status' => $filters['status'], 'feed' => $filters['feed'] ?: null, 'q' => $filters['q'] ?: null, 'page' => $page > 1 ? $page : null]);
$back = route('admin.news.index') . ($query ? '?' . http_build_query($query) : '');
$tabLink = fn (string $status) => route('admin.news.index', array_filter(['status' => $status, 'feed' => $filters['feed'] ?: null]));
?>
<?= $this->insert('admin/news/tabs', ['active' => 'queue', 'pending' => $counts['new']]) ?>

<?php if (!$translated): ?>
    <div class="alert alert-warning">ქართული თარგმანი გამორთულია ან Claude API გასაღები არ არის — სიახლეები მხოლოდ ინგლისურ ბლოგში გამოჩნდება. <a href="<?= e(route('admin.settings.index', ['tab' => 'news'])) ?>">პარამეტრები → სიახლეები</a></div>
<?php endif ?>

<div class="nav-align-top">
    <ul class="nav nav-tabs flex-wrap">
        <?php foreach ($statuses as $key => [$label]): ?>
            <li class="nav-item"><a class="nav-link<?= $filters['status'] === $key ? ' active' : '' ?>" href="<?= e($tabLink($key)) ?>"><?= e($label) ?> <span class="badge rounded-pill bg-label-secondary ms-1"><?= e($counts[$key]) ?></span></a></li>
        <?php endforeach ?>
        <li class="nav-item"><a class="nav-link<?= $filters['status'] === '' ? ' active' : '' ?>" href="<?= e($tabLink('all')) ?>">ყველა</a></li>
    </ul>
</div>

<div class="card rounded-top-0">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="get" class="d-flex flex-wrap gap-2 flex-grow-1" style="max-width: 640px">
            <input type="hidden" name="status" value="<?= e($filters['status'] ?: 'all') ?>">
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
        <form method="post" action="<?= e(route('admin.news.fetch_now')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline-primary" title="წამოღების ჯობი cron-ის შემდეგ ტიკზე"><i class="icon-base bx bx-download me-1"></i>წამოღება ახლავე</button>
        </form>
    </div>

    <?php if ($rows === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">
            <?= $filters['status'] === 'new' ? 'მოდერაციაში სიახლეები არ არის. ახალი სიახლეები feed-ებიდან გრაფიკით მოდის.' : 'სიახლეები ვერ მოიძებნა.' ?>
        </p></div>
    <?php else: ?>
        <form method="post" action="<?= e(route('admin.news.moderate')) ?>" id="news-bulk">
            <?= csrf_field() ?>
            <input type="hidden" name="back" value="<?= e($back) ?>">
            <div class="d-flex flex-wrap align-items-center gap-2 px-6 pb-4">
                <div class="form-check me-2 mb-0">
                    <input class="form-check-input" type="checkbox" id="news-all" data-check-all>
                    <label class="form-check-label" for="news-all">ყველა</label>
                </div>
                <button class="btn btn-sm btn-primary" name="action" value="publish"><i class="icon-base bx bx-send icon-sm me-1"></i>გამოქვეყნება</button>
                <button class="btn btn-sm btn-outline-primary" name="action" value="approve" title="გამოქვეყნდება გრაფიკით, დღიური ლიმიტით"><i class="icon-base bx bx-check icon-sm me-1"></i>დამტკიცება</button>
                <button class="btn btn-sm btn-outline-secondary" name="action" value="reject"><i class="icon-base bx bx-x icon-sm me-1"></i>უარყოფა</button>
                <small class="text-body-secondary ms-auto">გამოქვეყნება და თარგმანი ფონზე მიმდინარეობს (cron, ≤ 5 წთ; თარგმანი 1–2 წთ სტატიაზე).</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th style="width: 1%"></th><th>სიახლე</th><th>Feed</th><th>რეაქცია</th><th>სტატუსი</th><th class="text-end"></th></tr></thead>
                    <tbody class="table-border-bottom-0">
                    <?php foreach ($rows as $row): [$statusLabel, $statusColor] = $statuses[$row['status']]; ?>
                        <tr>
                            <td><input class="form-check-input" type="checkbox" name="ids[]" value="<?= e($row['id']) ?>" aria-label="მონიშვნა"></td>
                            <td style="min-width: 320px">
                                <div class="d-flex gap-3">
                                    <?php if ($row['image_url']): ?>
                                        <img src="<?= e($row['image_url']) ?>" alt="" width="96" height="40" class="rounded flex-shrink-0 object-fit-cover" loading="lazy" referrerpolicy="no-referrer">
                                    <?php endif ?>
                                    <div class="min-w-0">
                                        <a href="<?= e(route('admin.news.show', ['id' => $row['id']])) ?>" class="fw-medium text-heading d-block"><?= e($row['title']) ?></a>
                                        <small class="text-body-secondary">
                                            <?= e($row['author_name'] ?? '') ?><?= $row['source_published_at'] ? ' · ' . e(date('d.m.Y', strtotime($row['source_published_at']))) : '' ?> · <?= e($row['reading_minutes']) ?> წთ
                                            · <?= e(implode(', ', array_map(fn ($t) => '#' . $t, array_slice($row['tags'], 0, 4)))) ?>
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
        </form>
        <?php if ($pages > 1): ?><div class="card-footer border-top"><?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?></div><?php endif ?>
    <?php endif ?>
</div>

<?php $this->start('scripts') ?>
<script>
document.querySelector('[data-check-all]')?.addEventListener('change', (e) => {
    document.querySelectorAll('#news-bulk input[name="ids[]"]').forEach((box) => { box.checked = e.target.checked; });
});
</script>
<?php $this->stop() ?>
