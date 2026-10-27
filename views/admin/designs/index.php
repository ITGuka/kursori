<?php
/** @var Core\View\Template $this */
use App\Models\Design;

$this->layout('admin/layouts/app');
?>

<div class="bg-light rounded p-4 mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <h6 class="mb-0"><i class="fa fa-palette text-primary me-2"></i><?= e($title) ?> <span class="text-muted fw-normal">(<?= e(count($rows)) ?>)</span></h6>
        <div class="d-flex gap-2">
            <a href="<?= e(route('admin.categories.index', ['type' => Design::CATEGORIES])) ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-folder me-1"></i>კატეგორიები</a>
            <a href="<?= e(route('admin.designs.create')) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>დამატება</a>
        </div>
    </div>
    <form method="get" class="row g-2 align-items-center">
        <div class="col-sm-5"><input type="search" name="q" class="form-control form-control-sm" placeholder="ძებნა სათაურში" value="<?= e($filters['q']) ?>"></div>
        <?php if ($categoryOptions !== []): ?>
            <div class="col-sm-4">
                <select name="category" class="form-select form-select-sm" aria-label="კატეგორია">
                    <option value="">ყველა კატეგორია</option>
                    <?php foreach ($categoryOptions as $id => $name): ?>
                        <option value="<?= e($id) ?>"<?= $filters['category'] === $id ? ' selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        <?php endif ?>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary">ფილტრი</button>
            <?php if (!$sortable): ?><a class="btn btn-sm btn-link" href="<?= e(route('admin.designs.index')) ?>">გასუფთავება</a><?php endif ?>
        </div>
    </form>
</div>

<div class="bg-light rounded p-4">
    <?php if ($rows === []): ?>
        <p class="text-muted mb-0">დიზაინები ჯერ არ არის.</p>
    <?php else: ?>
        <ul class="list-unstyled mb-0" <?= $sortable ? 'data-sort-url="' . e(route('admin.designs.reorder')) . '"' : '' ?>>
            <?php foreach ($rows as $row): ?>
                <?php $preview = Design::previewUrl($row); ?>
                <li class="block-row<?= $row['is_active'] ? '' : ' is-hidden' ?>" id="design-<?= e($row['id']) ?>" data-id="<?= e($row['id']) ?>">
                    <?php if ($sortable): ?><span class="block-handle" title="გადაადგილება"><i class="fa fa-grip-vertical"></i></span><?php endif ?>
                    <span class="collection-thumb">
                        <?php if ($thumb = media_url($row['image_id'], 'thumb')): ?><img src="<?= e($thumb) ?>" alt="" loading="lazy"><?php else: ?><i class="fa fa-palette text-muted"></i><?php endif ?>
                    </span>
                    <div class="block-info">
                        <a href="<?= e(route('admin.designs.edit', ['id' => $row['id']])) ?>" class="fw-semibold"><?= e($row['title']) ?></a>
                        <?php if (!$row['is_active']): ?><span class="badge bg-secondary ms-1">დამალული</span><?php endif ?>
                        <?php if ($preview === ''): ?><span class="badge bg-warning text-dark ms-1">შაბლონი არ არის</span><?php endif ?>
                        <div class="small text-muted">
                            <?php if ($row['categories']): ?><i class="fa fa-folder me-1"></i><?= e($row['categories']) ?> · <?php endif ?>
                            <?php if ($preview !== ''): ?><?= e($row['files']) ?> ფაილი, <?= e(number_format($row['size'] / 1024 / 1024, 1)) ?> MB · <?php endif ?>
                            <?php foreach ($languages as $language): ?>
                                <span class="badge <?= in_array($language['code'], $row['langs'], true) ? 'bg-success' : 'bg-light text-muted border' ?> text-uppercase"><?= e($language['code']) ?></span>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="block-actions">
                        <?php if ($preview !== ''): ?>
                            <a href="<?= e($preview) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="შაბლონის ნახვა"><i class="fa fa-external-link-alt"></i></a>
                        <?php endif ?>
                        <form method="post" action="<?= e(route('admin.designs.toggle', ['id' => $row['id']])) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-secondary" title="<?= $row['is_active'] ? 'დამალვა' : 'გამოჩენა' ?>"><i class="fa <?= $row['is_active'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i></button>
                        </form>
                        <a href="<?= e(route('admin.designs.edit', ['id' => $row['id']])) ?>" class="btn btn-sm btn-outline-primary" title="რედაქტირება"><i class="fa fa-pen"></i></a>
                        <form method="post" action="<?= e(route('admin.designs.destroy', ['id' => $row['id']])) ?>" data-confirm="წავშალოთ დიზაინი „<?= e($row['title']) ?>“ და მისი შაბლონის ფაილები?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="btn btn-sm btn-outline-danger" title="წაშლა"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </li>
            <?php endforeach ?>
        </ul>
        <p class="small text-muted mt-3 mb-0">
            <?= $sortable ? 'რიგი (შეკვეთის ფორმაშიც ასე ჩანს) იცვლება გადათრევით.' : 'რიგის შესაცვლელად გაასუფთავეთ ფილტრი.' ?>
            საიტზე ჩანს მხოლოდ აქტიური დიზაინი შაბლონით.
        </p>
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
