<?php
/** @var Core\View\Template $this */
use Core\Support\Str;

$this->layout('admin/layouts/app');
?>

<div class="bg-light rounded p-4 mb-4">
    <h6 class="mb-3"><?= e($title) ?> <span class="text-muted fw-normal">(<?= e($total) ?>)</span></h6>
    <form method="get" class="row g-2 align-items-center">
        <div class="col-sm-3">
            <select name="form" class="form-select form-select-sm" aria-label="ფორმა">
                <option value="">ყველა ფორმა</option>
                <?php foreach ($forms as $key => $form): ?>
                    <option value="<?= e($key) ?>"<?= $filters['form'] === $key ? ' selected' : '' ?>><?= e($form['label']) ?><?= !empty($unread[$key]) ? ' (' . $unread[$key] . ' ახალი)' : '' ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-2">
            <select name="status" class="form-select form-select-sm" aria-label="სტატუსი">
                <option value="">ყველა სტატუსი</option>
                <?php foreach (App\Models\FormSubmission::STATUSES as $key => [$label]): ?>
                    <option value="<?= e($key) ?>"<?= $filters['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-3"><input type="search" name="q" class="form-control form-control-sm" placeholder="ძებნა (სახელი, ელ-ფოსტა, ტექსტი)" value="<?= e($filters['q']) ?>"></div>
        <div class="col-auto">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="unread" value="1" id="unread"<?= $filters['unread'] ? ' checked' : '' ?>>
                <label class="form-check-label" for="unread">მხოლოდ წაუკითხავი</label>
            </div>
        </div>
        <div class="col-auto"><button class="btn btn-sm btn-outline-primary">ფილტრი</button></div>
    </form>
</div>

<div class="bg-light rounded p-4">
    <?php if ($rows === []): ?>
        <p class="text-muted mb-0">შეტყობინებები არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                <tr class="text-dark"><th>თარიღი</th><th>ფორმა</th><th>გამომგზავნი</th><th>შეტყობინება</th><th class="text-end"></th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $url = route('admin.inbox.show', ['id' => $row['id']]); ?>
                    <tr class="<?= $row['is_read'] ? '' : 'fw-semibold' ?>">
                        <td class="text-nowrap"><?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?></td>
                        <td><i class="<?= e($forms[$row['form']]['icon'] ?? 'fa fa-envelope') ?> text-primary me-1"></i><?= e($forms[$row['form']]['label'] ?? $row['form']) ?>
                            <span class="badge bg-light text-muted border text-uppercase"><?= e($row['lang']) ?></span></td>
                        <td>
                            <a href="<?= e($url) ?>"><?= e($row['data']['name'] ?? '—') ?></a>
                            <div class="small text-muted fw-normal"><?= e($row['data']['email'] ?? '') ?></div>
                        </td>
                        <td class="small fw-normal">
                            <?php if (($row['data']['design'] ?? '') !== ''): ?><i class="fa fa-palette text-primary me-1"></i><?= e($row['data']['design']) ?> · <?php endif ?>
                            <?= e(Str::limit((string)($row['data']['subject'] ?? '') . ' ' . (string)($row['data']['message'] ?? $row['data']['description'] ?? ''), 90)) ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if (!$row['is_read']): ?><span class="badge bg-danger">წაუკითხავი</span><?php endif ?>
                            <?php [$statusLabel, $statusClass] = App\Models\FormSubmission::STATUSES[$row['status']] ?? [$row['status'], 'bg-secondary']; ?>
                            <span class="badge <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
                            <a href="<?= e($url) ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i><span class="visually-hidden">ნახვა</span></a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?>
    <?php endif ?>
</div>
