<?php
/**
 * სიახლის წინასწარი ნახვა მოდერაციისთვის: მეტა, ყდა, გაწმენდილი ტექსტი (როგორც ბლოგში გამოჩნდება), ქმედებები.
 * @var Core\View\Template $this
 * @var array $item news_items + category_id
 * @var string $body
 * @var array|null $feed
 */
$this->layout('admin/layouts/app');
[$statusLabel, $statusColor] = App\Models\NewsItem::STATUSES[$item['status']] ?? [$item['status'], 'secondary'];
$tags = (array)json_decode((string)$item['tags'], true);
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.news.index')) ?>">სიახლეები</a></li>
        <li class="breadcrumb-item active" aria-current="page">#<?= e($item['id']) ?></li>
    </ol>
</nav>

<div class="row g-6">
    <div class="col-xl-8">
        <article class="card">
            <?php if ($item['image_url']): ?>
                <img src="<?= e($item['image_url']) ?>" alt="" class="card-img-top" referrerpolicy="no-referrer">
            <?php endif ?>
            <div class="card-body">
                <h4 class="mb-2"><?= e($item['title']) ?></h4>
                <?php if ($item['description']): ?><p class="text-body-secondary"><?= e($item['description']) ?></p><?php endif ?>
                <hr>
                <div class="news-preview"><?= $body ?></div>
            </div>
        </article>
    </div>
    <div class="col-xl-4">
        <div class="card mb-6">
            <div class="card-body">
                <p class="mb-3"><span class="badge bg-label-<?= e($statusColor) ?>"><?= e($statusLabel) ?></span></p>
                <dl class="row small mb-0">
                    <dt class="col-5">Feed</dt><dd class="col-7"><?= e($feed['name'] ?? '—') ?></dd>
                    <dt class="col-5">ავტორი</dt><dd class="col-7"><?php if ($item['author_url']): ?><a href="<?= e($item['author_url']) ?>" target="_blank" rel="noopener"><?= e($item['author_name']) ?></a><?php else: ?><?= e($item['author_name'] ?? '—') ?><?php endif ?></dd>
                    <dt class="col-5">გამოქვეყნდა</dt><dd class="col-7"><?= $item['source_published_at'] ? e(date('d.m.Y H:i', strtotime($item['source_published_at']))) : '—' ?></dd>
                    <dt class="col-5">რეაქციები</dt><dd class="col-7"><?= e($item['reactions']) ?></dd>
                    <dt class="col-5">საკითხავი</dt><dd class="col-7"><?= e($item['reading_minutes']) ?> წთ · <?= e(number_format(strlen((string)$item['body_html']))) ?> სიმბ.</dd>
                    <dt class="col-5">ტეგები</dt><dd class="col-7"><?= e(implode(', ', array_map(fn ($t) => '#' . $t, $tags))) ?></dd>
                    <dt class="col-5">ორიგინალი</dt><dd class="col-7 text-break"><a href="<?= e($item['canonical_url']) ?>" target="_blank" rel="noopener"><?= e(parse_url($item['canonical_url'], PHP_URL_HOST)) ?></a></dd>
                </dl>
                <?php if ($item['error']): ?><div class="alert alert-danger small mt-4 mb-0 text-break"><?= e($item['error']) ?></div><?php endif ?>
            </div>
            <?php if ($item['collection_item_id']): ?>
                <div class="card-footer border-top">
                    <a href="<?= e(route('admin.collections.edit', ['id' => $item['collection_item_id']])) ?>" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i>ბლოგის სტატია</a>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<?php $this->start('styles') ?>
<style>
    .news-preview img { max-width: 100%; height: auto; }
    .news-preview pre { padding: .75rem 1rem; background: var(--bs-gray-100, #f5f5f9); border-radius: .375rem; overflow-x: auto; font-size: .8125rem; }
    .news-preview table { display: block; overflow-x: auto; }
</style>
<?php $this->stop() ?>
