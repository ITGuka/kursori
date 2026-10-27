<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$statuses = [
    'active'       => ['აქტიური', 'bg-label-success', 'bx-user-check', 'success'],
    'pending'      => ['დასტურს ელოდება', 'bg-label-warning', 'bx-time', 'warning'],
    'unsubscribed' => ['გაუქმებული', 'bg-label-secondary', 'bx-user-x', 'secondary'],
];
?>
<?= $this->insert('admin/newsletter/tabs') ?>

<div class="row g-6 mb-6">
    <?php foreach ($statuses as $key => [$label, , $icon, $tone]): ?>
        <div class="col-sm-4">
            <div class="card h-100 dash-stat">
                <div class="card-body d-flex align-items-center gap-4">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-<?= e($tone) ?>"><i class="icon-base bx <?= e($icon) ?> icon-lg"></i></span>
                    </div>
                    <div>
                        <a href="<?= e(route('admin.newsletter.subscribers', ['status' => $key])) ?>" class="stretched-link text-body d-block"><?= e($label) ?></a>
                        <h5 class="mb-0"><?= e($counts[$key]) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="get" class="list-filters d-flex flex-wrap align-items-center gap-3">
            <select name="status" class="form-select w-auto" aria-label="სტატუსი">
                <option value="">ყველა სტატუსი</option>
                <?php foreach ($statuses as $key => [$label]): ?>
                    <option value="<?= e($key) ?>"<?= $filters['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach ?>
            </select>
            <div class="input-group input-group-merge flex-grow-1" style="max-width: 360px">
                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                <input type="search" name="q" class="form-control" placeholder="ელ-ფოსტა" value="<?= e($filters['q']) ?>" aria-label="ძებნა">
            </div>
            <button class="btn btn-outline-primary">ფილტრი</button>
            <a class="btn btn-outline-secondary ms-auto" href="<?= e(route('admin.newsletter.export', array_filter($filters))) ?>"><i class="icon-base bx bx-download me-1"></i>CSV</a>
        </form>
    </div>

    <?php if ($rows === []): ?>
        <div class="card-body"><p class="text-body-secondary mb-0">გამომწერები არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead><tr><th>ელ-ფოსტა</th><th>ენა</th><th>სტატუსი</th><th>გამოწერა</th><th>დასტური</th><th></th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="fw-medium text-heading"><?= e($row['email']) ?></td>
                        <td><span class="badge bg-label-secondary text-uppercase"><?= e($row['lang']) ?></span></td>
                        <td><span class="badge <?= $statuses[$row['status']][1] ?>"><?= e($statuses[$row['status']][0]) ?></span></td>
                        <td><?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?></td>
                        <td><?= $row['confirmed_at'] ? e(date('d.m.Y H:i', strtotime($row['confirmed_at']))) : '—' ?></td>
                        <td class="text-end">
                            <form method="post" action="<?= e(route('admin.newsletter.subscribers.destroy', ['id' => $row['id']])) ?>" data-confirm="წავშალოთ <?= e($row['email']) ?>? (გაუქმებისგან განსხვავებით, ჩანაწერი სრულად წაიშლება)">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1): ?><div class="card-footer border-top"><?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?></div><?php endif ?>
    <?php endif ?>
</div>
