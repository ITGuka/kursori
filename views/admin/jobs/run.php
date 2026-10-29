<?php
/**
 * ერთი გაშვების სრული გამოსავალი.
 * @var Core\View\Template $this
 * @var array $run
 * @var array $job
 */
$this->layout('admin/layouts/app');
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.jobs.index')) ?>">გრაფიკი</a></li>
        <li class="breadcrumb-item"><a href="<?= e(route('admin.jobs.show', ['id' => $job['id']])) ?>"><?= e($job['command']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e(date('d.m.Y H:i:s', strtotime($run['started_at']))) ?></li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex flex-wrap gap-3 align-items-center">
        <code><?= e(trim($job['command'] . ' ' . $job['args'])) ?></code>
        <span class="badge bg-label-<?= ['running' => 'info', 'success' => 'success', 'failed' => 'danger'][$run['status']] ?>"><?= e($run['status']) ?></span>
        <small class="text-body-secondary">კოდი: <?= e($run['exit_code'] ?? '—') ?><?= $run['finished_at'] ? ' · ' . e(max(0, strtotime($run['finished_at']) - strtotime($run['started_at']))) . ' წმ' : '' ?></small>
    </div>
    <div class="card-body pt-0">
        <pre class="mb-0 p-4 bg-lighter rounded small text-wrap"><?= e((string)$run['output'] ?: '(გამოსავალი ცარიელია)') ?></pre>
    </div>
</div>
