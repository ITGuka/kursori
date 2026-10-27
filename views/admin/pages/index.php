<?php
/** @var Core\View\Template $this */
use App\Models\Page;

$this->layout('admin/layouts/app');
$pages = app(Page::class);
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= count($rows) ?>)</span></h5>
        <a href="<?= e(route('admin.pages.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>ახალი გვერდი</a>
    </div>
    <?php if ($rows === []): ?>
        <div class="card-body pt-0"><p class="text-body-secondary mb-0">გვერდები ჯერ არ არის.</p></div>
    <?php else: ?>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>სათაური</th>
                    <th>ენები</th>
                    <th>სტატუსი</th>
                    <th>რიგი</th>
                    <th class="text-end">მოქმედება</th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                <?php foreach ($rows as $row): ?>
                    <?php $viewUrl = $pages->url($row['id'], $row['langs'][0] ?? config('app.default_locale'), true); ?>
                    <tr>
                        <td>
                            <?php if ($row['depth'] > 0): ?><span class="text-body-secondary" style="padding-left: <?= (int)$row['depth'] * 1.25 ?>rem">↳ </span><?php endif ?>
                            <a href="<?= e(route('admin.pages.edit', ['id' => $row['id']])) ?>" class="fw-medium text-heading"><?= e($row['title']) ?></a>
                            <?php if ($row['is_home']): ?><span class="badge bg-label-primary ms-1"><i class="icon-base bx bx-home icon-xs me-1"></i>მთავარი</span><?php endif ?>
                        </td>
                        <td>
                            <?php foreach ($languages as $language): ?>
                                <?php $has = in_array($language['code'], $row['langs'], true); ?>
                                <span class="badge <?= $has ? 'bg-label-success' : 'bg-label-secondary opacity-50' ?> text-uppercase" title="<?= e($language['native_name'] . ($has ? '' : ' — თარგმანი არ არის')) ?>"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'published'): ?>
                                <span class="badge bg-label-success">გამოქვეყნებული</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">მონახაზი</span>
                            <?php endif ?>
                        </td>
                        <td><?= e($row['sort']) ?></td>
                        <td class="text-end">
                            <div class="table-actions">
                                <?php if ($viewUrl !== ''): ?>
                                    <a href="<?= e($viewUrl) ?>" class="btn btn-sm btn-icon btn-outline-secondary" target="_blank" rel="noopener" title="საიტზე ნახვა" aria-label="საიტზე ნახვა">
                                        <i class="icon-base bx bx-link-external"></i>
                                    </a>
                                <?php endif ?>
                                <a href="<?= e(route('admin.pages.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება">
                                    <i class="icon-base bx bx-edit-alt"></i>
                                </a>
                                <?php if (!$row['is_home']): ?>
                                    <form method="post" action="<?= e(route('admin.pages.destroy', ['id' => $row['id']])) ?>"
                                          data-confirm="წავშალოთ გვერდი „<?= e($row['title']) ?>“ ყველა ენაზე, ბლოკებითურთ? ქვეგვერდები ზედა დონეზე გადავა.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                <?php endif ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
