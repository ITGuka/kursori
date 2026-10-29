<?php
/**
 * სიახლეების feed-ები: ტეგები → ბლოგის კატეგორია, ფილტრი, გამოქვეყნებული/ჩავარდნილი, ბოლო შემოწმება და შეცდომა.
 * @var Core\View\Template $this
 * @var list<array> $feeds
 */
$this->layout('admin/layouts/app');
$date = fn (?string $value) => $value ? e(date('d.m.Y H:i', strtotime($value))) : '—';
?>
<?= $this->insert('admin/news/tabs', ['active' => 'feeds']) ?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <small class="text-body-secondary" style="flex: 1 1 360px">თითო feed — dev.to-ს ტეგების ნაკრები ერთი ბლოგის კატეგორიისთვის. ჯობი ყველა აქტიური feed-იდან იღებს უახლეს სტატიას <?= e(App\Services\News\NewsFetcher::REACTIONS_ABOVE) ?>-ზე მეტი რეაქციით; ერთ კატეგორიაში დღეში — ერთი.</small>
        <a href="<?= e(route('admin.news.feeds.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
    </div>
    <?php if ($feeds === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">Feed-ები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Feed</th><th>კატეგორია</th><th>ფილტრი</th><th>სიახლეები</th><th>ბოლო გამოქვეყნება</th><th class="text-end"></th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($feeds as $feed): ?>
                    <tr class="<?= $feed['is_active'] ? '' : 'opacity-50' ?>">
                        <td style="min-width: 220px">
                            <span class="fw-medium text-heading"><?= e($feed['name']) ?></span>
                            <?php if (!$feed['is_active']): ?><span class="badge bg-label-secondary ms-1">გათიშული</span><?php endif ?>
                            <div class="small text-body-secondary"><?= e(implode(' ', array_map(fn ($t) => '#' . $t, $feed['tag_list']))) ?></div>
                            <?php if ($feed['last_error']): ?><div class="small text-danger text-break"><?= e($feed['last_error']) ?></div><?php endif ?>
                        </td>
                        <td class="small"><?= e($feed['category_name'] ?? '—') ?></td>
                        <td class="small text-nowrap">ბოლო <?= e($feed['top_days']) ?> დღე<?= $feed['require_image'] ? ' · ფოტოთი' : '' ?></td>
                        <td class="small text-nowrap">
                            <a href="<?= e(route('admin.news.index', ['feed' => $feed['id']])) ?>"><?= e($feed['items_published']) ?> გამოქვ.</a>
                            <?php if ($feed['items_failed'] > 0): ?><span class="badge bg-label-danger ms-1" title="შეცდომა"><?= e($feed['items_failed']) ?></span><?php endif ?>
                        </td>
                        <td class="small text-nowrap"><?= $date($feed['last_published_at']) ?></td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="<?= e(route('admin.news.feeds.test', ['id' => $feed['id']])) ?>" class="btn btn-sm btn-icon btn-outline-secondary" title="ტესტი: რა სტატიებია ახლა" aria-label="ტესტი"><i class="icon-base bx bx-search-alt"></i></a>
                                <a href="<?= e(route('admin.news.feeds.edit', ['id' => $feed['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                                <form method="post" action="<?= e(route('admin.news.feeds.destroy', ['id' => $feed['id']])) ?>" data-confirm="წავშალოთ feed „<?= e($feed['name']) ?>“? გამოქვეყნებული სტატიები ბლოგში დარჩება.">
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
