<?php
/**
 * ჯობის პარამეტრები (ოფციები, ინტერვალი, აქტიურობა) და ბოლო გაშვებების ჟურნალი.
 * @var Core\View\Template $this
 * @var array $job
 * @var array $data
 * @var string $label
 * @var list<array> $runs
 */
$this->layout('admin/layouts/app');
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.jobs.index')) ?>">გრაფიკი</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($label) ?></li>
    </ol>
</nav>

<form method="post" action="<?= e(route('admin.jobs.update', ['id' => $job['id']])) ?>" class="card form-narrow mb-6" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">
    <h5 class="card-header"><?= e($label) ?> <small class="text-body-secondary"><code><?= e($job['command']) ?></code></small></h5>
    <div class="card-body">
        <div class="mb-5">
            <label class="form-label" for="args">ოფციები</label>
            <input type="text" class="form-control<?= $invalid('args') ?>" id="args" name="args" maxlength="190" value="<?= e($data['args']) ?>" placeholder="--limit=3">
            <?= $feedback('args') ?>
            <div class="form-text">ჩვეულებრივ ცარიელი. მაგ. <code>--dry-run</code> — სიახლის ჯობი მხოლოდ აჩვენებს, რას გამოაქვეყნებდა.</div>
        </div>
        <div class="mb-5" style="max-width: 240px">
            <label class="form-label" for="interval_minutes">ინტერვალი (წუთი)</label>
            <input type="number" class="form-control" id="interval_minutes" name="interval_minutes" min="5" max="10080" step="5" value="<?= e($data['interval_minutes']) ?>">
            <div class="form-text">მინ. 5 (cron-ის ტიკი), მაქს. 10080 (კვირა). გაშვებამდე მოწმდება, არის თუ არა სამუშაო — თუ არა, ჯობი გამოტოვდება.</div>
        </div>
        <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($data['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">აქტიური</label>
        </div>
    </div>
    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <a href="<?= e(route('admin.jobs.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>

<div class="card">
    <h5 class="card-header">ბოლო გაშვებები</h5>
    <?php if ($runs === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">ჯობი ჯერ არ გაშვებულა.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>დაწყება</th><th>ხანგრძლივობა</th><th>სტატუსი</th><th>გამოსავალი</th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($runs as $run): ?>
                    <tr>
                        <td class="small text-nowrap"><a href="<?= e(route('admin.jobs.run_log', ['id' => $run['id']])) ?>"><?= e(date('d.m.Y H:i:s', strtotime($run['started_at']))) ?></a></td>
                        <td class="small text-nowrap"><?= $run['finished_at'] ? e(max(0, strtotime($run['finished_at']) - strtotime($run['started_at']))) . ' წმ' : '…' ?></td>
                        <td><span class="badge bg-label-<?= ['running' => 'info', 'success' => 'success', 'failed' => 'danger'][$run['status']] ?>"><?= ['running' => 'მიმდინარეობს', 'success' => 'წარმატებული', 'failed' => 'შეცდომა'][$run['status']] ?></span></td>
                        <td class="small text-body-secondary text-break"><?= e(mb_substr(str_replace("\n", ' · ', (string)$run['output']), 0, 160)) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
