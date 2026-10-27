<?php
/** @var Core\View\Template $this */
use App\Models\FormSubmission;
use Core\Support\Str;

$this->layout('admin/layouts/app');
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= e($total) ?>)</span></h5>
    </div>

    <div class="card-body border-bottom pt-0">
        <form method="get" class="list-filters d-flex flex-wrap align-items-center gap-3">
            <select name="form" class="form-select w-auto" aria-label="ფორმა">
                <option value="">ყველა ფორმა</option>
                <?php foreach ($forms as $key => $form): ?>
                    <option value="<?= e($key) ?>"<?= $filters['form'] === $key ? ' selected' : '' ?>><?= e($form['label']) ?><?= !empty($unread[$key]) ? ' (' . $unread[$key] . ' ახალი)' : '' ?></option>
                <?php endforeach ?>
            </select>
            <select name="status" class="form-select w-auto" aria-label="სტატუსი">
                <option value="">ყველა სტატუსი</option>
                <?php foreach (FormSubmission::STATUSES as $key => [$label]): ?>
                    <option value="<?= e($key) ?>"<?= $filters['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach ?>
            </select>
            <div class="input-group input-group-merge flex-grow-1" style="max-width: 360px">
                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                <input type="search" name="q" class="form-control" placeholder="ძებნა (სახელი, ელ-ფოსტა, ტექსტი)" value="<?= e($filters['q']) ?>" aria-label="ძებნა">
            </div>
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="unread" value="1" id="unread"<?= $filters['unread'] ? ' checked' : '' ?>>
                <label class="form-check-label" for="unread">მხოლოდ წაუკითხავი</label>
            </div>
            <button class="btn btn-outline-primary">ფილტრი</button>
        </form>
    </div>

    <?php if ($rows === []): ?>
        <div class="card-body"><p class="text-body-secondary mb-0">შეტყობინებები არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr><th>გამომგზავნი</th><th>ფორმა</th><th>შეტყობინება</th><th>თარიღი</th><th>სტატუსი</th><th class="text-end"></th></tr>
                </thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($rows as $row): ?>
                    <?php
                    $url = route('admin.inbox.show', ['id' => $row['id']]);
                    $name = (string)($row['data']['name'] ?? $row['data']['email'] ?? '—');
                    [$statusLabel, $statusClass] = FormSubmission::STATUSES[$row['status']] ?? [$row['status'], 'bg-label-secondary'];
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-sm flex-shrink-0">
                                    <span class="avatar-initial rounded-circle bg-label-<?= $row['is_read'] ? 'secondary' : 'primary' ?>"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
                                </div>
                                <div class="min-w-0">
                                    <a href="<?= e($url) ?>" class="text-heading<?= $row['is_read'] ? '' : ' fw-semibold' ?>"><?= e($row['data']['name'] ?? '—') ?></a>
                                    <?php if (!$row['is_read']): ?><span class="dash-dot ms-1" title="წაუკითხავი"></span><?php endif ?>
                                    <small class="d-block text-body-secondary"><?= e($row['data']['email'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="text-nowrap">
                            <i class="icon-base <?= e($forms[$row['form']]['icon'] ?? 'bx bx-envelope') ?> icon-sm text-primary me-1"></i><?= e($forms[$row['form']]['label'] ?? $row['form']) ?>
                            <span class="badge bg-label-secondary text-uppercase ms-1"><?= e($row['lang']) ?></span>
                        </td>
                        <td class="small cell-text">
                            <?php if (($row['data']['design'] ?? '') !== ''): ?><i class="icon-base bx bx-palette icon-xs text-primary me-1"></i><?= e($row['data']['design']) ?> · <?php endif ?>
                            <?= e(Str::limit((string)($row['data']['subject'] ?? '') . ' ' . (string)($row['data']['message'] ?? $row['data']['description'] ?? ''), 90)) ?>
                        </td>
                        <td class="text-nowrap small"><?= e(date('d.m.Y H:i', strtotime($row['created_at']))) ?></td>
                        <td><span class="badge <?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                        <td class="text-end">
                            <a href="<?= e($url) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="ნახვა" aria-label="ნახვა"><i class="icon-base bx bx-show"></i></a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1): ?><div class="card-footer border-top"><?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?></div><?php endif ?>
    <?php endif ?>
</div>
