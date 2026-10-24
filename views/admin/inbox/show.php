<?php
/** @var Core\View\Template $this @var array $submission @var array $definition */
$this->layout('admin/layouts/app');
$data = $submission['data'];
$email = (string)($data['email'] ?? '');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.inbox.index')) ?>">Inbox</a></li>
        <li class="breadcrumb-item active" aria-current="page">#<?= e($submission['id']) ?></li>
    </ol>
</nav>

<div class="bg-light rounded p-4" style="max-width: 900px">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
        <h6 class="mb-0"><i class="<?= e($definition['icon'] ?? 'fa fa-envelope') ?> text-primary me-2"></i><?= e($definition['label']) ?></h6>
        <div class="d-flex gap-2">
            <?php if (filter_var($email, FILTER_VALIDATE_EMAIL)): ?>
                <a class="btn btn-sm btn-primary" href="mailto:<?= e($email) ?>?subject=<?= e(rawurlencode('Re: ' . ($data['subject'] ?? $definition['label']))) ?>"><i class="fa fa-reply me-1"></i>პასუხი</a>
            <?php endif ?>
            <form method="post" action="<?= e(route('admin.inbox.unread', ['id' => $submission['id']])) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-secondary"><i class="fa fa-envelope me-1"></i>წაუკითხავად</button>
            </form>
            <form method="post" action="<?= e(route('admin.inbox.destroy', ['id' => $submission['id']])) ?>" data-confirm="წავშალოთ შეტყობინება?">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash me-1"></i>წაშლა</button>
            </form>
        </div>
    </div>

    <dl class="row mb-0">
        <?php foreach ($data as $name => $value): ?>
            <?php $field = $definition['fields'][$name] ?? []; ?>
            <dt class="col-sm-3"><?= e(isset($field['label']) ? t($field['label'], [], config('app.default_locale')) : $name) ?></dt>
            <dd class="col-sm-9 text-break"><?= (string)$value === '' ? '<span class="text-muted">—</span>' : (!empty($field['multiline']) ? nl2br(e((string)$value)) : e((string)$value)) ?></dd>
        <?php endforeach ?>
        <dt class="col-sm-3 text-muted fw-normal">თარიღი</dt><dd class="col-sm-9 text-muted"><?= e(date('d.m.Y H:i', strtotime($submission['created_at']))) ?></dd>
        <dt class="col-sm-3 text-muted fw-normal">ენა</dt><dd class="col-sm-9 text-muted text-uppercase"><?= e($submission['lang']) ?></dd>
        <?php if ($submission['page_url'] !== ''): ?>
            <dt class="col-sm-3 text-muted fw-normal">გვერდი</dt><dd class="col-sm-9 text-muted text-break"><?= e($submission['page_url']) ?></dd>
        <?php endif ?>
        <dt class="col-sm-3 text-muted fw-normal">IP</dt><dd class="col-sm-9 text-muted"><?= e($submission['ip']) ?></dd>
    </dl>
</div>
