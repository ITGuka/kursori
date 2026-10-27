<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$statuses = ['draft' => ['მონახაზი', 'bg-label-secondary'], 'sending' => ['იგზავნება', 'bg-label-warning'], 'sent' => ['გაგზავნილი', 'bg-label-success']];
$langNames = array_column($languages, 'native_name', 'code');
?>
<?= $this->insert('admin/newsletter/tabs') ?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0">კამპანიები <small class="text-body-secondary fw-normal">· აქტიური გამომწერი: <?= e($counts['active']) ?></small></h5>
        <a href="<?= e(route('admin.newsletter.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>ახალი კამპანია</a>
    </div>
    <?php if ($campaigns === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">კამპანიები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead><tr><th>თემა</th><th>ადრესატები</th><th>სტატუსი</th><th>გაგზავნილი</th><th>თარიღი</th></tr></thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td><a href="<?= e(route('admin.newsletter.edit', ['id' => $campaign['id']])) ?>" class="fw-medium text-heading"><?= e($campaign['subject']) ?></a></td>
                        <td><?= e($campaign['lang'] === '' ? 'ყველა ენა' : ($langNames[$campaign['lang']] ?? $campaign['lang'])) ?></td>
                        <td><span class="badge <?= $statuses[$campaign['status']][1] ?>"><?= e($statuses[$campaign['status']][0]) ?></span></td>
                        <td><?= $campaign['total'] ? e($campaign['sent'] . ' / ' . $campaign['total']) . ($campaign['failed'] ? ' <span class="text-danger">(' . (int)$campaign['failed'] . ' შეცდომა)</span>' : '') : '—' ?></td>
                        <td><?= e(date('d.m.Y H:i', strtotime($campaign['sent_at'] ?? $campaign['created_at']))) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
