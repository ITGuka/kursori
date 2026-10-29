<?php
/**
 * სიახლეების ჩანართები: რიგი | feed-ები (+ ბმულები გრაფიკსა და პარამეტრებზე).
 * @var string $active queue | feeds
 * @var int $pending მოდერაციაში მყოფი
 */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">
    <ul class="nav nav-pills flex-wrap gap-2">
        <li class="nav-item"><a class="nav-link<?= $active === 'queue' ? ' active' : '' ?>" href="<?= e(route('admin.news.index')) ?>"><i class="icon-base bx bx-list-check icon-sm me-1"></i>რიგი<?php if ($pending > 0): ?> <span class="badge rounded-pill bg-warning ms-1"><?= e($pending) ?></span><?php endif ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $active === 'feeds' ? ' active' : '' ?>" href="<?= e(route('admin.news.feeds.index')) ?>"><i class="icon-base bx bx-rss icon-sm me-1"></i>Feed-ები</a></li>
    </ul>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= e(route('admin.jobs.index')) ?>" class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-time-five icon-sm me-1"></i>გრაფიკი</a>
        <a href="<?= e(route('admin.settings.index', ['tab' => 'news'])) ?>" class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-cog icon-sm me-1"></i>პარამეტრები</a>
    </div>
</div>
