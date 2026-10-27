<?php
/** @var Core\View\Template $this */
use App\Models\Design;

$this->layout('admin/layouts/app');
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-palette"></i></span>
            </div>
            <h5 class="card-title mb-0"><?= e($title) ?> <span class="text-body-secondary fw-normal">(<?= e(count($rows)) ?>)</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= e(route('admin.categories.index', ['type' => Design::CATEGORIES])) ?>" class="btn btn-outline-primary"><i class="icon-base bx bx-folder me-1"></i>კატეგორიები</a>
            <a href="<?= e(route('admin.designs.create')) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>დამატება</a>
        </div>
    </div>

    <div class="card-body border-bottom pt-0">
        <form method="get" class="list-filters d-flex flex-wrap align-items-center gap-3">
            <div class="input-group input-group-merge flex-grow-1" style="max-width: 360px">
                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                <input type="search" name="q" class="form-control" placeholder="ძებნა სათაურში" value="<?= e($filters['q']) ?>" aria-label="ძებნა">
            </div>
            <?php if ($categoryOptions !== []): ?>
                <select name="category" class="form-select w-auto" aria-label="კატეგორია">
                    <option value="">ყველა კატეგორია</option>
                    <?php foreach ($categoryOptions as $id => $name): ?>
                        <option value="<?= e($id) ?>"<?= $filters['category'] === $id ? ' selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach ?>
                </select>
            <?php endif ?>
            <button class="btn btn-outline-primary">ფილტრი</button>
            <?php if (!$sortable): ?><a class="btn btn-link" href="<?= e(route('admin.designs.index')) ?>">გასუფთავება</a><?php endif ?>
        </form>
    </div>

    <?php if ($rows === []): ?>
        <div class="card-body"><p class="text-body-secondary mb-0">დიზაინები ჯერ არ არის.</p></div>
    <?php else: ?>
        <ul class="list-group list-group-flush sortable-list" <?= $sortable ? 'data-sort-url="' . e(route('admin.designs.reorder')) . '"' : '' ?>>
            <?php foreach ($rows as $row): ?>
                <?php $preview = Design::previewUrl($row); ?>
                <li class="list-group-item d-flex align-items-center gap-3<?= $row['is_active'] ? '' : ' is-hidden' ?>" id="design-<?= e($row['id']) ?>" data-id="<?= e($row['id']) ?>">
                    <?php if ($sortable): ?><span class="block-handle" title="გადაადგილება"><i class="icon-base bx bx-grid-vertical"></i></span><?php endif ?>
                    <span class="collection-thumb">
                        <?php if ($thumb = media_url($row['image_id'], 'thumb')): ?><img src="<?= e($thumb) ?>" alt="" loading="lazy"><?php else: ?><i class="icon-base bx bx-palette text-body-secondary"></i><?php endif ?>
                    </span>
                    <div class="flex-grow-1 min-w-0">
                        <a href="<?= e(route('admin.designs.edit', ['id' => $row['id']])) ?>" class="fw-medium text-heading"><?= e($row['title']) ?></a>
                        <?php if (!$row['is_active']): ?><span class="badge bg-label-secondary ms-1">დამალული</span><?php endif ?>
                        <?php if ($preview === ''): ?><span class="badge bg-label-warning ms-1">შაბლონი არ არის</span><?php endif ?>
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                            <?php if ($row['categories']): ?><small class="text-body-secondary me-2"><i class="icon-base bx bx-folder icon-xs me-1"></i><?= e($row['categories']) ?></small><?php endif ?>
                            <?php if ($preview !== ''): ?><small class="text-body-secondary me-2"><?= e($row['files']) ?> ფაილი, <?= e(number_format($row['size'] / 1024 / 1024, 1)) ?> MB</small><?php endif ?>
                            <?php foreach ($languages as $language): ?>
                                <span class="badge <?= in_array($language['code'], $row['langs'], true) ? 'bg-label-success' : 'bg-label-secondary opacity-50' ?> text-uppercase"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="table-actions">
                        <?php if ($preview !== ''): ?>
                            <a href="<?= e($preview) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-outline-secondary" title="შაბლონის ნახვა" aria-label="შაბლონის ნახვა"><i class="icon-base bx bx-link-external"></i></a>
                        <?php endif ?>
                        <form method="post" action="<?= e(route('admin.designs.toggle', ['id' => $row['id']])) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-icon btn-outline-secondary" title="<?= $row['is_active'] ? 'დამალვა' : 'გამოჩენა' ?>" aria-label="<?= $row['is_active'] ? 'დამალვა' : 'გამოჩენა' ?>"><i class="icon-base bx <?= $row['is_active'] ? 'bx-show' : 'bx-hide' ?>"></i></button>
                        </form>
                        <a href="<?= e(route('admin.designs.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>
                        <form method="post" action="<?= e(route('admin.designs.destroy', ['id' => $row['id']])) ?>" data-confirm="წავშალოთ დიზაინი „<?= e($row['title']) ?>“ და მისი შაბლონის ფაილები?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button>
                        </form>
                    </div>
                </li>
            <?php endforeach ?>
        </ul>
        <div class="card-footer border-top">
            <small class="text-body-secondary">
                <?= $sortable ? 'რიგი (შეკვეთის ფორმაშიც ასე ჩანს) იცვლება გადათრევით.' : 'რიგის შესაცვლელად გაასუფთავეთ ფილტრი.' ?>
                საიტზე ჩანს მხოლოდ აქტიური დიზაინი შაბლონით.
            </small>
        </div>
    <?php endif ?>
</div>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    (function ($) {
        var $list = $('[data-sort-url]');
        if (!$list.length) { return; }
        Sortable.create($list[0], {
            handle: '.block-handle', animation: 150,
            onEnd: function () {
                $.ajax({url: $list.data('sort-url'), method: 'POST', contentType: 'application/json', headers: {'Accept': 'application/json'},
                        data: JSON.stringify({ids: $list.children('[data-id]').map(function () { return $(this).data('id'); }).get()})})
                    .fail(function () { alert('რიგის შენახვა ვერ მოხერხდა — განაახლეთ გვერდი.'); });
            }
        });
    })(jQuery);
</script>
<?php $this->stop() ?>
