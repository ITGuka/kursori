<?php
/** @var Core\View\Template $this */
use App\Models\Page;

$this->layout('admin/layouts/app');
$pages = app(Page::class);
?>

<div class="bg-light rounded p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0"><?= e($title) ?></h6>
        <a href="<?= e(route('admin.pages.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>ახალი გვერდი</a>
    </div>
    <?php if ($rows === []): ?>
        <p class="text-muted mb-0">გვერდები ჯერ არ არის.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                <tr class="text-dark">
                    <th>სათაური</th>
                    <th>ენები</th>
                    <th>სტატუსი</th>
                    <th>რიგი</th>
                    <th class="text-end">მოქმედება</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $viewUrl = $pages->url($row['id'], $row['langs'][0] ?? config('app.default_locale'), true); ?>
                    <tr>
                        <td>
                            <span class="text-muted"><?= str_repeat('— ', $row['depth']) ?></span>
                            <a href="<?= e(route('admin.pages.edit', ['id' => $row['id']])) ?>"><?= e($row['title']) ?></a>
                            <?php if ($row['is_home']): ?><span class="badge bg-primary ms-1"><i class="fa fa-home"></i> მთავარი</span><?php endif ?>
                        </td>
                        <td>
                            <?php foreach ($languages as $language): ?>
                                <?php $has = in_array($language['code'], $row['langs'], true); ?>
                                <span class="badge <?= $has ? 'bg-success' : 'bg-light text-muted border' ?> text-uppercase" title="<?= e($language['native_name'] . ($has ? '' : ' — თარგმანი არ არის')) ?>"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'published'): ?>
                                <span class="badge bg-success">გამოქვეყნებული</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">მონახაზი</span>
                            <?php endif ?>
                        </td>
                        <td><?= e($row['sort']) ?></td>
                        <td class="text-end text-nowrap">
                            <?php if ($viewUrl !== ''): ?>
                                <a href="<?= e($viewUrl) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" title="საიტზე ნახვა">
                                    <i class="fa fa-external-link-alt"></i><span class="visually-hidden">ნახვა</span>
                                </a>
                            <?php endif ?>
                            <a href="<?= e(route('admin.pages.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-pen"></i><span class="visually-hidden">რედაქტირება</span>
                            </a>
                            <?php if (!$row['is_home']): ?>
                                <form method="post" action="<?= e(route('admin.pages.destroy', ['id' => $row['id']])) ?>" class="d-inline"
                                      data-confirm="წავშალოთ გვერდი „<?= e($row['title']) ?>“ ყველა ენაზე, ბლოკებითურთ? ქვეგვერდები ზედა დონეზე გადავა.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i><span class="visually-hidden">წაშლა</span></button>
                                </form>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted mt-3 mb-0">საიტის მენიუ (ფაზა 5-მდე) — ზედა დონის გამოქვეყნებული გვერდები „რიგის“ მიხედვით, ქვეგვერდები — ჩამოსაშლელად.</p>
    <?php endif ?>
</div>
