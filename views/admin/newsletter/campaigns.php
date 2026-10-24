<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
$statuses = ['draft' => ['მონახაზი', 'bg-secondary'], 'sending' => ['იგზავნება', 'bg-warning text-dark'], 'sent' => ['გაგზავნილი', 'bg-success']];
$langNames = array_column($languages, 'native_name', 'code');
?>
<?= $this->insert('admin/newsletter/tabs') ?>

<div class="bg-light rounded-bottom p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0">კამპანიები <span class="text-muted fw-normal">· აქტიური გამომწერი: <?= e($counts['active']) ?></span></h6>
        <a href="<?= e(route('admin.newsletter.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>ახალი კამპანია</a>
    </div>
    <?php if ($campaigns === []): ?>
        <p class="text-muted mb-0">კამპანიები ჯერ არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead><tr class="text-dark"><th>თემა</th><th>ადრესატები</th><th>სტატუსი</th><th>გაგზავნილი</th><th>თარიღი</th></tr></thead>
                <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td><a href="<?= e(route('admin.newsletter.edit', ['id' => $campaign['id']])) ?>"><?= e($campaign['subject']) ?></a></td>
                        <td><?= e($campaign['lang'] === '' ? 'ყველა ენა' : ($langNames[$campaign['lang']] ?? $campaign['lang'])) ?></td>
                        <td><span class="badge <?= $statuses[$campaign['status']][1] ?>"><?= e($statuses[$campaign['status']][0]) ?></span></td>
                        <td><?= $campaign['total'] ? e($campaign['sent'] . ' / ' . $campaign['total']) . ($campaign['failed'] ? ' <span class="text-danger">(' . (int)$campaign['failed'] . ' შეცდომა)</span>' : '') : '—' ?></td>
                        <td class="text-nowrap"><?= e(date('d.m.Y H:i', strtotime($campaign['sent_at'] ?? $campaign['created_at']))) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
