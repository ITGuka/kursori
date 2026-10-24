<?php
/**
 * @var int $page მიმდინარე გვერდი
 * @var int $pages გვერდების რაოდენობა
 * არსებული query პარამეტრები (ფილტრები) ბმულებში რჩება.
 */
if ($pages <= 1) {
    return;
}
$query = (array)app()->request()?->query();
$link = fn (int $n) => e(url(ltrim((string)app()->request()?->path(), '/')) . '?' . http_build_query(['page' => $n] + $query));
$from = max(1, $page - 3);
$to = min($pages, $page + 3);
?>
<nav class="mt-3" aria-label="გვერდები">
    <ul class="pagination pagination-sm mb-0">
        <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= $link($page - 1) ?>">‹</a></li>
        <?php for ($n = $from; $n <= $to; $n++): ?>
            <li class="page-item<?= $n === $page ? ' active' : '' ?>"><a class="page-link" href="<?= $link($n) ?>"><?= $n ?></a></li>
        <?php endfor ?>
        <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>"><a class="page-link" href="<?= $link($page + 1) ?>">›</a></li>
    </ul>
</nav>
