<?php
/**
 * სიახლეების feed-ები: ტეგები → ბლოგის კატეგორია, რეჟიმი, ფილტრები, ბოლო წამოღება და შეცდომა.
 * @var Core\View\Template $this
 * @var list<array> $feeds
 */
$this->layout('admin/layouts/app');
$pending = array_sum(array_column($feeds, 'items_new'));
?>
<?= $this->insert('admin/news/tabs', ['active' => 'feeds', 'pending' => $pending]) ?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <small class="text-body-secondary" style="flex: 1 1 360px">თითო feed — dev.to-ს ტეგების ნაკრები, რომლის სიახლეებიც ერთ ბლოგის კატეგორიაში ხვდება. ერთ სტატიას პირველი შესაბამისი feed იღებს (რიგით).</small>
        <a href="<?= e(route('admin.news.feeds.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
    </div>
    <?php if ($feeds === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">Feed-ები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Feed</th><th>კატეგორია</th><th>რეჟიმი</th><th>ფილტრი</th><th>სიახლეები</th><th>ბოლო წამოღება</th><th class="text-end"></th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($feeds as $feed): ?>
                    <tr class="<?= $feed['is_active'] ? '' : 'opacity-50' ?>">
                        <td style="min-width: 220px">
                            <span class="fw-medium text-heading"><?= e($feed['name']) ?></span>
                            <?php if (!$feed['is_active']): ?><span class="badge bg-label-secondary ms-1">გათიშული</span><?php endif ?>
                            <div class="small text-body-secondary"><?= e(implode(' ', array_map(fn ($t) => '#' . $t, $feed['tag_list']))) ?></div>
                        </td>
                        <td class="small"><?= e($feed['category_name'] ?? '—') ?></td>
                        <td><span class="badge bg-label-<?= $feed['publish_mode'] === 'auto' ? 'success' : 'warning' ?>"><?= $feed['publish_mode'] === 'auto' ? 'ავტომატური' : 'მოდერაცია' ?></span></td>
                        <td class="small text-nowrap"><?= e($feed['top_days']) ?> დღე · ≥ <?= e($feed['min_reactions']) ?> <i class="icon-base bx bx-heart icon-xs"></i> · <?= e($feed['per_run']) ?> / ჯერზე<?= $feed['require_image'] ? ' · ფოტოთი' : '' ?></td>
                        <td class="small text-nowrap">
                            <a href="<?= e(route('admin.news.index', ['status' => 'all', 'feed' => $feed['id']])) ?>"><?= e($feed['items_total']) ?></a>
                            <?php if ($feed['items_new'] > 0): ?><span class="badge bg-label-warning ms-1" title="მოდერაციაში"><?= e($feed['items_new']) ?></span><?php endif ?>
                            <span class="text-body-secondary">· <?= e($feed['items_published']) ?> გამოქვ.</span>
                        </td>
                        <td class="small">
                            <?= $feed['last_fetched_at'] ? e(date('d.m.Y H:i', strtotime($feed['last_fetched_at']))) : '—' ?>
                            <?php if ($feed['last_error']): ?><div class="text-danger text-break"><?= e($feed['last_error']) ?></div><?php endif ?>
                        </td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="<?= e(route('admin.news.feeds.test', ['id' => $feed['id']])) ?>" class="btn btn-sm btn-icon btn-outline-secondary" title="ტესტი: რას წამოიღებდა ახლა" aria-label="ტესტი"><i class="icon-base bx bx-search-alt"></i></a>
                                <a href="<?= e(route('admin.news.feeds.edit', ['id' => $feed['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                                <form method="post" action="<?= e(route('admin.news.feeds.destroy', ['id' => $feed['id']])) ?>" data-confirm="წავშალოთ feed „<?= e($feed['name']) ?>“? წამოღებული სიახლეები და ბლოგის სტატიები დარჩება.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
