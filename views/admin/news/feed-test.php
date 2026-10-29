<?php
/**
 * Feed-ის ტესტი: კანდიდატები ახლა (ფილტრის შემდეგ, ჯერ არ შენახული), უახლესი პირველი.
 * @var Core\View\Template $this
 * @var array $feed
 * @var list<array> $candidates dev.to-ს სიის ჩანაწერები
 * @var string|null $error
 */
$this->layout('admin/layouts/app');
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.news.feeds.index')) ?>">სიახლეები — feed-ები</a></li>
        <li class="breadcrumb-item"><a href="<?= e(route('admin.news.feeds.edit', ['id' => $feed['id']])) ?>"><?= e($feed['name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">ტესტი</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header">
        <h5 class="mb-1">„<?= e($feed['name']) ?>“-ის კანდიდატები ახლა</h5>
        <small class="text-body-secondary">
            <?= e(implode(' ', array_map(fn ($t) => '#' . $t, $feed['tag_list']))) ?> · ბოლო <?= e($feed['top_days']) ?> დღე · <?= e(App\Services\News\NewsFetcher::REACTIONS_ABOVE) ?>-ზე მეტი რეაქცია<?= $feed['require_image'] ? ' · ფოტოთი' : '' ?>.
            ჯერ აუღებელი სტატიები, უახლესი პირველი; მონიშნული აირჩევა, როცა ამ კატეგორიის ჯერი მოვა. არაფერი ინახება.
        </small>
    </div>
    <?php if ($error !== null): ?>
        <div class="card-body pt-0"><div class="alert alert-danger mb-0">dev.to: <?= e($error) ?></div></div>
    <?php elseif ($candidates === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">ახალი სტატიები ვერ მოიძებნა — სცადეთ მეტი დღე ან სხვა ტეგები.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>#</th><th>სტატია</th><th>ტეგები</th><th>რეაქცია</th><th>თარიღი</th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($candidates as $i => $article): ?>
                    <tr class="<?= $i === 0 ? 'table-success' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td>
                            <a href="<?= e($article['url']) ?>" target="_blank" rel="noopener" class="fw-medium"><?= e($article['title']) ?></a>
                            <div class="small text-body-secondary"><?= e($article['user']['name'] ?? '') ?> · <?= e($article['reading_time_minutes'] ?? 0) ?> წთ</div>
                        </td>
                        <td class="small"><?= e(implode(', ', array_map(fn ($t) => '#' . $t, (array)($article['tag_list'] ?? [])))) ?></td>
                        <td class="text-nowrap"><?= e($article['public_reactions_count'] ?? 0) ?></td>
                        <td class="small text-nowrap"><?= e(substr((string)($article['published_at'] ?? ''), 0, 10)) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
