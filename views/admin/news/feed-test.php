<?php
/**
 * Feed-ის ტესტი: კანდიდატები ახლა (ფილტრის შემდეგ, ჯერ არ შენახული), პირველი per_run — შემდეგ წამოღებაზე.
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
        <h5 class="mb-1">რას წამოიღებდა „<?= e($feed['name']) ?>“ ახლა</h5>
        <small class="text-body-secondary">
            <?= e(implode(' ', array_map(fn ($t) => '#' . $t, $feed['tag_list']))) ?> · ბოლო <?= e($feed['top_days']) ?> დღე · ≥ <?= e($feed['min_reactions']) ?> რეაქცია<?= $feed['require_image'] ? ' · ფოტოთი' : '' ?>.
            ჩანს ჯერ არ წამოღებული სტატიები; მონიშნული <?= e($feed['per_run']) ?> — შემდეგ წამოღებაზე. არაფერი ინახება.
        </small>
    </div>
    <?php if ($error !== null): ?>
        <div class="card-body pt-0"><div class="alert alert-danger mb-0">dev.to: <?= e($error) ?></div></div>
    <?php elseif ($candidates === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">ახალი სტატიები ვერ მოიძებნა — სცადეთ მეტი დღე, ნაკლები რეაქცია ან სხვა ტეგები.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>#</th><th>სტატია</th><th>ტეგები</th><th>რეაქცია</th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($candidates as $i => $article): ?>
                    <tr class="<?= $i < $feed['per_run'] ? 'table-success' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td>
                            <a href="<?= e($article['url']) ?>" target="_blank" rel="noopener" class="fw-medium"><?= e($article['title']) ?></a>
                            <div class="small text-body-secondary"><?= e($article['user']['name'] ?? '') ?> · <?= e($article['reading_time_minutes'] ?? 0) ?> წთ</div>
                        </td>
                        <td class="small"><?= e(implode(', ', array_map(fn ($t) => '#' . $t, (array)($article['tag_list'] ?? [])))) ?></td>
                        <td class="text-nowrap"><?= e($article['public_reactions_count'] ?? 0) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
