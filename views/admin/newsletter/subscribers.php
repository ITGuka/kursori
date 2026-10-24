<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$statuses = ['active' => ['აქტიური', 'bg-success'], 'pending' => ['დასტურს ელოდება', 'bg-warning text-dark'], 'unsubscribed' => ['გაუქმებული', 'bg-secondary']];
?>
<?= $this->insert('admin/newsletter/tabs') ?>

<div class="bg-light rounded-bottom p-4 mb-4">
    <div class="d-flex flex-wrap gap-3 mb-3">
        <?php foreach ($statuses as $key => [$label, $class]): ?>
            <a href="<?= e(route('admin.newsletter.subscribers', ['status' => $key])) ?>" class="badge <?= $class ?> text-decoration-none fs-6"><?= e($label) ?>: <?= e($counts[$key]) ?></a>
        <?php endforeach ?>
    </div>
    <form method="get" class="row g-2 align-items-center">
        <div class="col-sm-3">
            <select name="status" class="form-select form-select-sm" aria-label="სტატუსი">
                <option value="">ყველა სტატუსი</option>
                <?php foreach ($statuses as $key => [$label]): ?>
                    <option value="<?= e($key) ?>"<?= $filters['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-4"><input type="search" name="q" class="form-control form-control-sm" placeholder="ელ-ფოსტა" value="<?= e($filters['q']) ?>"></div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary">ფილტრი</button>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e(route('admin.newsletter.export', array_filter($filters))) ?>"><i class="fa fa-download me-1"></i>CSV</a>
        </div>
    </form>
</div>

<div class="bg-light rounded p-4">
    <?php if ($rows === []): ?>
        <p class="text-muted mb-0">გამომწერები არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead><tr class="text-dark"><th>ელ-ფოსტა</th><th>ენა</th><th>სტატუსი</th><th>გამოწერა</th><th>დასტური</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['email']) ?></td>
                        <td class="text-uppercase"><?= e($row['lang']) ?></td>
                        <td><span class="badge <?= $statuses[$row['status']][1] ?>"><?= e($statuses[$row['status']][0]) ?></span></td>
                        <td class="text-nowrap"><?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?></td>
                        <td class="text-nowrap"><?= $row['confirmed_at'] ? e(date('d.m.Y H:i', strtotime($row['confirmed_at']))) : '—' ?></td>
                        <td class="text-end">
                            <form method="post" action="<?= e(route('admin.newsletter.subscribers.destroy', ['id' => $row['id']])) ?>" data-confirm="წავშალოთ <?= e($row['email']) ?>? (გაუქმებისგან განსხვავებით, ჩანაწერი სრულად წაიშლება)">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i><span class="visually-hidden">წაშლა</span></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?>
    <?php endif ?>
</div>
