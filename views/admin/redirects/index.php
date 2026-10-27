<?php
/** @var Core\View\Template $this */
$this->layout('admin/layouts/app');
?>
<div class="nav-align-top">
    <ul class="nav nav-pills flex-wrap gap-2 mb-6">
        <li class="nav-item"><a class="nav-link<?= $tab === 'redirects' ? ' active' : '' ?>" href="<?= e(route('admin.redirects.index')) ?>"><i class="icon-base bx bx-shuffle icon-sm me-1"></i>გადამისამართებები <span class="badge rounded-pill bg-label-secondary ms-1"><?= count($redirects) ?></span></a></li>
        <li class="nav-item"><a class="nav-link<?= $tab === '404' ? ' active' : '' ?>" href="<?= e(route('admin.redirects.index', ['tab' => '404'])) ?>"><i class="icon-base bx bx-error-circle icon-sm me-1"></i>404 მისამართები <span class="badge rounded-pill bg-label-secondary ms-1"><?= count($notFound) ?></span></a></li>
    </ul>
</div>

<div class="card">
    <?php if ($tab === 'redirects'): ?>
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <form method="get" class="d-flex gap-2 flex-grow-1" style="max-width: 420px">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input type="search" name="q" class="form-control" placeholder="ძებნა მისამართში" value="<?= e($search) ?>" aria-label="ძებნა">
                </div>
                <button class="btn btn-outline-primary">ძებნა</button>
            </form>
            <a href="<?= e(route('admin.redirects.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
        </div>
        <?php if ($redirects === []): ?>
            <div class="card-body pt-0"><p class="text-body-secondary mb-0">გადამისამართებები ჯერ არ არის. გვერდის ან ჩანაწერის მისამართის (slug) შეცვლისას 301 ავტომატურად იქმნება.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>ძველი მისამართი</th><th>ახალი მისამართი</th><th>კოდი</th><th>გამოყენება</th><th class="text-end"></th></tr></thead>
                    <tbody class="table-border-bottom-0">
                    <?php foreach ($redirects as $row): ?>
                        <tr class="<?= $row['is_active'] ? '' : 'opacity-50' ?>">
                            <td class="text-break"><code><?= e($row['from_path']) ?></code>
                                <?php if ($row['is_auto']): ?><span class="badge bg-label-info ms-1" title="შეიქმნა slug-ის შეცვლისას">ავტო</span><?php endif ?>
                                <?php if (!$row['is_active']): ?><span class="badge bg-label-secondary ms-1">გათიშული</span><?php endif ?>
                            </td>
                            <td class="text-break"><i class="icon-base bx bx-right-arrow-alt text-body-secondary me-1"></i><?= e($row['to_url']) ?></td>
                            <td><span class="badge bg-label-<?= (int)$row['code'] === 301 ? 'primary' : 'warning' ?>"><?= e($row['code']) ?></span></td>
                            <td class="small text-nowrap"><?= e($row['hits']) ?><?= $row['last_hit_at'] ? ' · ' . e(date('d.m.Y', strtotime($row['last_hit_at']))) : '' ?></td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(route('admin.redirects.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                                    <form method="post" action="<?= e(route('admin.redirects.destroy', ['id' => $row['id']])) ?>" data-confirm="წავშალოთ გადამისამართება <?= e($row['from_path']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
        <div class="card-footer border-top">
            <small class="text-body-secondary">გადამისამართება მუშაობს მხოლოდ მაშინ, როცა ძველ მისამართზე გვერდი აღარ არსებობს. Query string (?...) ახალ მისამართს გადაეცემა.</small>
        </div>
    <?php else: ?>
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <small class="text-body-secondary">მისამართები, რომლებზეც მომხმარებლებმა ან ბოტებმა 404 მიიღეს (ბოლო <?= 2000 ?>). ხშირი მისამართისთვის შექმენით გადამისამართება.</small>
            <?php if ($notFound !== []): ?>
                <form method="post" action="<?= e(route('admin.redirects.not_found.destroy')) ?>" data-confirm="გავასუფთაოთ 404 ჟურნალი?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-outline-danger"><i class="icon-base bx bx-trash me-1"></i>გასუფთავება</button>
                </form>
            <?php endif ?>
        </div>
        <?php if ($notFound === []): ?>
            <div class="card-body pt-0"><p class="text-body-secondary mb-0">404 მისამართები არ დაფიქსირებულა.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>მისამართი</th><th>რაოდენობა</th><th>ბოლოს</th><th>საიდან</th><th class="text-end"></th></tr></thead>
                    <tbody class="table-border-bottom-0">
                    <?php foreach ($notFound as $row): ?>
                        <tr>
                            <td class="text-break"><code><?= e($row['path']) ?></code></td>
                            <td><span class="badge bg-label-danger"><?= e($row['hits']) ?></span></td>
                            <td class="text-nowrap small"><?= e(date('d.m.Y H:i', strtotime($row['last_seen_at']))) ?></td>
                            <td class="small text-break text-body-secondary"><?= e((string)$row['referer']) ?></td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <a href="<?= e(route('admin.redirects.create', ['from' => $row['path']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="გადამისამართება" aria-label="გადამისამართება"><i class="icon-base bx bx-shuffle"></i></a>
                                    <form method="post" action="<?= e(route('admin.redirects.not_found.destroy')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                        <button class="btn btn-sm btn-icon btn-outline-secondary" title="დამალვა" aria-label="დამალვა"><i class="icon-base bx bx-x"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    <?php endif ?>
</div>
