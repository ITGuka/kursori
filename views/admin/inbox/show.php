<?php
/** @var Core\View\Template $this @var array $submission @var array $definition */
use App\Models\FormSubmission;

$this->layout('admin/layouts/app');
$data = $submission['data'];
$email = (string)($data['email'] ?? '');
[$statusLabel, $statusClass] = FormSubmission::STATUSES[$submission['status']] ?? [$submission['status'], 'bg-label-secondary'];
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.inbox.index')) ?>">Inbox</a></li>
        <li class="breadcrumb-item active" aria-current="page">#<?= e($submission['id']) ?></li>
    </ol>
</nav>

<div class="card form-wide">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary"><i class="icon-base <?= e($definition['icon'] ?? 'bx bx-envelope') ?>"></i></span>
            </div>
            <div>
                <h5 class="card-title mb-0"><?= e($definition['label']) ?></h5>
                <small class="text-body-secondary"><?= e(date('d.m.Y H:i', strtotime($submission['created_at']))) ?></small>
            </div>
            <span class="badge <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="post" action="<?= e(route('admin.inbox.status', ['id' => $submission['id']])) ?>">
                <?= csrf_field() ?>
                <select name="status" class="form-select form-select-sm" aria-label="სტატუსი" onchange="this.form.submit()">
                    <?php foreach (FormSubmission::STATUSES as $key => [$label]): ?>
                        <option value="<?= e($key) ?>"<?= $submission['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach ?>
                </select>
                <noscript><button class="btn btn-sm btn-outline-primary">შენახვა</button></noscript>
            </form>
            <?php if (filter_var($email, FILTER_VALIDATE_EMAIL)): ?>
                <a class="btn btn-sm btn-primary" href="mailto:<?= e($email) ?>?subject=<?= e(rawurlencode('Re: ' . ($data['subject'] ?? $definition['label']))) ?>"><i class="icon-base bx bx-reply icon-sm me-1"></i>პასუხი</a>
            <?php endif ?>
            <form method="post" action="<?= e(route('admin.inbox.unread', ['id' => $submission['id']])) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-envelope icon-sm me-1"></i>წაუკითხავად</button>
            </form>
            <form method="post" action="<?= e(route('admin.inbox.destroy', ['id' => $submission['id']])) ?>" data-confirm="წავშალოთ შეტყობინება?">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <?php // ველები ნაბიჯებად (ბრიფი) ან ერთ სიად — FormSubmission::present ?>
        <?php foreach (FormSubmission::present($definition, $data, (string)config('app.default_locale')) as $i => $section): ?>
            <?php if ($section['title'] !== null): ?>
                <h6 class="text-uppercase text-body-secondary small fw-semibold<?= $i > 0 ? ' mt-5' : '' ?> mb-3"><?= e($section['title']) ?></h6>
            <?php endif ?>
            <dl class="row mb-0 g-3">
                <?php foreach ($section['rows'] as $row): ?>
                    <dt class="col-sm-3 fw-medium text-heading"><?= e($row['label']) ?></dt>
                    <dd class="col-sm-9 text-break mb-0">
                        <?php if ($row['files'] !== []): ?>
                            <?php foreach ($row['files'] as $index => $file): ?>
                                <div>
                                    <a href="<?= e(route('admin.inbox.attachment', ['id' => $submission['id'], 'index' => $index])) ?>"><i class="icon-base bx bx-download icon-sm me-1"></i><?= e($file['name']) ?></a>
                                    <span class="text-body-secondary small ms-1"><?= e(FormSubmission::fileSize((int)($file['size'] ?? 0))) ?></span>
                                </div>
                            <?php endforeach ?>
                        <?php elseif ($row['links'] !== []): ?>
                            <?php foreach ($row['links'] as $link): ?>
                                <a class="d-block" href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($link) ?></a>
                            <?php endforeach ?>
                        <?php elseif ($row['value'] === ''): ?>
                            <span class="text-body-secondary">—</span>
                        <?php else: ?>
                            <?= $row['multiline'] ? nl2br(e($row['value'])) : e($row['value']) ?>
                        <?php endif ?>
                    </dd>
                <?php endforeach ?>
            </dl>
        <?php endforeach ?>
    </div>

    <div class="card-footer border-top">
        <dl class="row small text-body-secondary mb-0 g-2">
            <dt class="col-sm-3 fw-normal">ენა</dt><dd class="col-sm-9 text-uppercase mb-0"><?= e($submission['lang']) ?></dd>
            <?php if ($submission['page_url'] !== ''): ?>
                <dt class="col-sm-3 fw-normal">გვერდი</dt><dd class="col-sm-9 text-break mb-0"><?= e($submission['page_url']) ?></dd>
            <?php endif ?>
            <dt class="col-sm-3 fw-normal">IP</dt><dd class="col-sm-9 mb-0"><?= e($submission['ip']) ?></dd>
        </dl>
    </div>
</div>
