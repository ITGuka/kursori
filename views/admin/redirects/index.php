<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
?>
<ul class="nav nav-tabs mb-0">
    <li class="nav-item"><a class="nav-link<?= $tab === 'redirects' ? ' active' : '' ?>" href="<?= e(route('admin.redirects.index')) ?>">გადამისამართებები (<?= count($redirects) ?>)</a></li>
    <li class="nav-item"><a class="nav-link<?= $tab === '404' ? ' active' : '' ?>" href="<?= e(route('admin.redirects.index', ['tab' => '404'])) ?>">404 მისამართები (<?= count($notFound) ?>)</a></li>
</ul>

<div class="bg-light rounded-bottom p-4">
    <?php if ($tab === 'redirects'): ?>
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
            <form method="get" class="d-flex gap-2">
                <input type="search" name="q" class="form-control form-control-sm" placeholder="ძებნა მისამართში" value="<?= e($search) ?>">
                <button class="btn btn-sm btn-outline-primary">ძებნა</button>
            </form>
            <a href="<?= e(route('admin.redirects.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>დამატება</a>
        </div>
        <?php if ($redirects === []): ?>
            <p class="text-muted mb-0">გადამისამართებები ჯერ არ არის. გვერდის ან ჩანაწერის მისამართის (slug) შეცვლისას 301 ავტომატურად იქმნება.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead><tr class="text-dark"><th>ძველი მისამართი</th><th>ახალი მისამართი</th><th>კოდი</th><th>გამოყენება</th><th class="text-end"></th></tr></thead>
                    <tbody>
                    <?php foreach ($redirects as $row): ?>
                        <tr class="<?= $row['is_active'] ? '' : 'text-muted' ?>">
                            <td class="text-break"><code><?= e($row['from_path']) ?></code>
                                <?php if ($row['is_auto']): ?><span class="badge bg-light text-muted border ms-1" title="შეიქმნა slug-ის შეცვლისას">ავტო</span><?php endif ?>
                                <?php if (!$row['is_active']): ?><span class="badge bg-secondary ms-1">გათიშული</span><?php endif ?>
                            </td>
                            <td class="text-break"><i class="fa fa-long-arrow-alt-right text-muted me-1"></i><?= e($row['to_url']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td class="small"><?= e($row['hits']) ?><?= $row['last_hit_at'] ? ' · ' . e(date('d.m.Y', strtotime($row['last_hit_at']))) : '' ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= e(route('admin.redirects.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-pen"></i><span class="visually-hidden">რედაქტირება</span></a>
                                <form method="post" action="<?= e(route('admin.redirects.destroy', ['id' => $row['id']])) ?>" class="d-inline" data-confirm="წავშალოთ გადამისამართება <?= e($row['from_path']) ?>?">
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
        <?php endif ?>
        <p class="small text-muted mt-3 mb-0">გადამისამართება მუშაობს მხოლოდ მაშინ, როცა ძველ მისამართზე გვერდი აღარ არსებობს. Query string (?...) ახალ მისამართს გადაეცემა.</p>
    <?php else: ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="small text-muted mb-0">მისამართები, რომლებზეც მომხმარებლებმა ან ბოტებმა 404 მიიღეს (ბოლო <?= 2000 ?>). ხშირი მისამართისთვის შექმენით გადამისამართება.</p>
            <?php if ($notFound !== []): ?>
                <form method="post" action="<?= e(route('admin.redirects.not_found.destroy')) ?>" data-confirm="გავასუფთაოთ 404 ჟურნალი?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-sm btn-outline-danger">გასუფთავება</button>
                </form>
            <?php endif ?>
        </div>
        <?php if ($notFound === []): ?>
            <p class="text-muted mb-0">404 მისამართები არ დაფიქსირებულა.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead><tr class="text-dark"><th>მისამართი</th><th>რაოდენობა</th><th>ბოლოს</th><th>საიდან</th><th class="text-end"></th></tr></thead>
                    <tbody>
                    <?php foreach ($notFound as $row): ?>
                        <tr>
                            <td class="text-break"><code><?= e($row['path']) ?></code></td>
                            <td><?= e($row['hits']) ?></td>
                            <td class="text-nowrap small"><?= e(date('d.m.Y H:i', strtotime($row['last_seen_at']))) ?></td>
                            <td class="small text-break text-muted"><?= e((string)$row['referer']) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= e(route('admin.redirects.create', ['from' => $row['path']])) ?>" class="btn btn-sm btn-outline-primary" title="გადამისამართება"><i class="fa fa-random"></i></a>
                                <form method="post" action="<?= e(route('admin.redirects.not_found.destroy')) ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                    <button class="btn btn-sm btn-outline-secondary" title="დამალვა"><i class="fa fa-times"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    <?php endif ?>
</div>
