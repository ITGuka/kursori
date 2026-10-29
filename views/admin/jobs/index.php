<?php
/**
 * ჯობების გრაფიკი: cron-ის მდგომარეობა, ჯობები (ინტერვალი, შემდეგი/ბოლო გაშვება, სტატუსი), „ახლავე“.
 * @var Core\View\Template $this
 * @var list<array> $jobs
 * @var array<string, array> $labels config/schedule.php → commands
 * @var string|null $lastActivity
 */
$this->layout('admin/layouts/app');
$cronOk = $lastActivity !== null && strtotime($lastActivity) > time() - 15 * 60;
$date = fn (?string $value) => $value ? e(date('d.m.Y H:i', strtotime($value))) : '—';
$byId = array_column($jobs, null, 'id');
$jobLabel = fn (array $job) => $labels[$job['command']]['label'] ?? $job['command'];
?>
<div class="alert alert-<?= $cronOk ? 'success' : 'warning' ?>">
    <?php if ($cronOk): ?>
        cron მუშაობს — ბოლო გაშვება <?= $date($lastActivity) ?>.
    <?php else: ?>
        <strong>cron <?= $lastActivity === null ? 'ჯერ არ გაშვებულა' : 'ბოლო 15 წუთში არ გაშვებულა (ბოლოს: ' . $date($lastActivity) . ')' ?>.</strong>
        ჯობები მხოლოდ cron-ით ეშვება. cPanel → Cron Jobs → ყოველ 5 წუთში:
    <?php endif ?>
    <code class="d-block mt-2 user-select-all">cd ~/kursori.dev &amp;&amp; php bin/console schedule:run &gt;/dev/null 2&gt;&amp;1</code>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>ჯობი</th><th>ყოველ</th><th>შემდეგი</th><th>ბოლო</th><th>სტატუსი</th><th class="text-end"></th></tr></thead>
            <tbody class="table-border-bottom-0">
            <?php foreach ($jobs as $job): ?>
                <tr class="<?= $job['is_active'] ? '' : 'opacity-50' ?>">
                    <td>
                        <a href="<?= e(route('admin.jobs.show', ['id' => $job['id']])) ?>" class="fw-medium text-heading"><?= e($labels[$job['command']]['label'] ?? $job['command']) ?></a>
                        <?php if (!$job['is_active']): ?><span class="badge bg-label-secondary ms-1">გათიშული</span><?php endif ?>
                        <div class="small text-body-secondary"><code><?= e(trim($job['command'] . ' ' . $job['args'])) ?></code></div>
                    </td>
                    <?php $parent = $job['after_job_id'] !== null ? ($byId[$job['after_job_id']] ?? null) : null; ?>
                    <td class="small">
                        <?php if ($parent !== null): ?>
                            <i class="icon-base bx bx-subdirectory-right icon-sm text-body-secondary"></i> „<?= e($jobLabel($parent)) ?>“-ის შემდეგ
                        <?php else: ?>
                            <span class="text-nowrap"><?= (int)$job['interval_minutes'] % 60 === 0 ? e((int)$job['interval_minutes'] / 60) . ' სთ' : e($job['interval_minutes']) . ' წთ' ?></span>
                        <?php endif ?>
                    </td>
                    <td class="small text-nowrap">
                        <?php if (!$job['is_active']): ?>—
                        <?php elseif ($job['next_run_at'] !== null): ?><?= strtotime($job['next_run_at']) <= time() ? 'ახლა (შემდეგ ტიკზე)' : $date($job['next_run_at']) ?>
                        <?php elseif ($parent !== null): ?><?= $parent['next_run_at'] !== null ? $date(max($parent['next_run_at'], date('Y-m-d H:i:s'))) : '—' ?>
                        <?php else: ?>ახლა (შემდეგ ტიკზე)<?php endif ?>
                    </td>
                    <td class="small text-nowrap"><?= $date($job['last_run_at']) ?></td>
                    <td>
                        <?php if ($job['running_since']): ?>
                            <span class="badge bg-label-info"><i class="icon-base bx bx-loader-alt bx-spin icon-xs me-1"></i>მიმდინარეობს</span>
                        <?php elseif ($job['last_status']): ?>
                            <?php [$statusText, $statusColor] = ['success' => ['წარმატებული', 'success'], 'failed' => ['შეცდომა', 'danger'], 'skipped' => ['გამოტოვდა', 'secondary']][$job['last_status']]; ?>
                            <span class="badge bg-label-<?= e($statusColor) ?>"><?= e($statusText) ?></span>
                            <?php if ($job['last_status'] === 'skipped' && $job['last_message']): ?><div class="small text-body-secondary mt-1"><?= e($job['last_message']) ?></div><?php endif ?>
                        <?php else: ?>—<?php endif ?>
                    </td>
                    <td class="text-end">
                        <div class="table-actions">
                            <form method="post" action="<?= e(route('admin.jobs.run', ['id' => $job['id']])) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-icon btn-outline-primary" title="ახლავე (cron-ის შემდეგ ტიკზე)" aria-label="ახლავე"><i class="icon-base bx bx-play"></i></button>
                            </form>
                            <a href="<?= e(route('admin.jobs.show', ['id' => $job['id']])) ?>" class="btn btn-sm btn-icon btn-outline-secondary" title="პარამეტრები და ჟურნალი" aria-label="პარამეტრები და ჟურნალი"><i class="icon-base bx bx-edit-alt"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer border-top">
        <small class="text-body-secondary">გაშვებამდე მოწმდება, არის თუ არა სამუშაო (მაგ. დამტკიცებული სიახლე) — თუ არა, ჯობი გამოტოვდება, ჟურნალის გარეშე. ჯაჭვის ჯობები მშობლის შემდეგ, იმავე ტიკში ეშვება. თითო ჯობი ცალკე პროცესშია, ერთდროულად — ერთხელ; მაქსიმუმ <?= e(config('schedule.timeout_minutes')) ?> წუთი. ჟურნალი ინახება <?= e(config('schedule.keep_runs_days')) ?> დღე.</small>
    </div>
</div>
