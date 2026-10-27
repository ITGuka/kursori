<?php
/**
 * @var Core\View\Template $this
 * @var array|null $design
 * @var array $core image_id, is_active
 * @var array<string, string> $titles
 * @var list<int> $categoryIds
 * @var array<int, string> $categoryOptions
 * @var array<string, string> $formErrors
 */
use App\Models\Design;

$this->layout('admin/layouts/app');
$isEdit = $design !== null;
$action = $isEdit ? route('admin.designs.update', ['id' => $design['id']]) : route('admin.designs.store');
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.designs.index')) ?>">დიზაინები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger">ფორმაში შეცდომებია — შეამოწმეთ მონიშნული ველები.<?= isset($formErrors['template']) ? ' ფაილის ველი თავიდან აირჩიეთ.' : '' ?></div>
<?php endif ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="bg-light rounded p-4" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <h6 class="mb-0"><i class="fa fa-palette text-primary me-2"></i><?= $isEdit ? 'დიზაინი' : 'ახალი დიზაინი' ?></h6>
        <?php if ($previewUrl !== ''): ?>
            <a href="<?= e($previewUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="fa fa-external-link-alt me-1"></i>შაბლონის ნახვა</a>
        <?php endif ?>
    </div>

    <div style="max-width: 1000px">
        <div class="mb-3">
            <label class="form-label d-block">სახელი * <span class="badge bg-info text-dark fw-normal">თარგმნადი</span></label>
            <div class="row g-2">
                <?php foreach ($languages as $language): ?>
                    <?php $code = $language['code']; ?>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1" for="title-<?= e($code) ?>"><?= e($language['native_name']) ?><?= $code === $defaultLang ? ' *' : '' ?></label>
                        <input type="text" class="form-control<?= $invalid("t.$code") ?>" id="title-<?= e($code) ?>" name="t[<?= e($code) ?>]" maxlength="190" value="<?= e($titles[$code] ?? '') ?>">
                        <?= $feedback("t.$code") ?>
                    </div>
                <?php endforeach ?>
            </div>
            <div class="form-text">ცარიელი სახელი ენაზე — საიტზე ძირითადი ენის სახელი ჩანს.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="template">შაბლონი (ZIP)<?= $isEdit ? '' : ' *' ?></label>
            <?php if ($isEdit && $previewUrl !== ''): ?>
                <div class="small mb-2">
                    <i class="fa fa-file-archive text-primary me-1"></i>ამჟამინდელი: <code><?= e($design['entry']) ?></code>
                    · <?= e($design['files']) ?> ფაილი, <?= e(number_format($design['size'] / 1024 / 1024, 1)) ?> MB
                </div>
            <?php endif ?>
            <input type="file" class="form-control<?= $invalid('template') ?>" id="template" name="template" accept=".zip,application/zip">
            <?= $feedback('template') ?>
            <div class="form-text">
                HTML/CSS/JS შაბლონი ZIP არქივად (მაქს. <?= e(round($maxUpload / 1024 / 1024)) ?> MB), რომელშიც არის <code>index.html</code> — ზედა დონეზე ან ერთ საქაღალდეში.
                PHP და სხვა სერვერული ფაილები გამოიტოვება.<?= $isEdit ? ' ახალი არქივი ძველს ჩაანაცვლებს.' : '' ?>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label d-block">კატეგორიები</label>
                <?php if ($categoryOptions === []): ?>
                    <div class="small text-muted">კატეგორიები ჯერ არ არის — <a href="<?= e(route('admin.categories.create', ['type' => Design::CATEGORIES])) ?>">დამატება</a>.</div>
                <?php else: ?>
                    <div class="border rounded p-2 bg-white" style="max-height: 260px; overflow-y: auto">
                        <?php foreach ($categoryOptions as $id => $name): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?= e($id) ?>" id="category-<?= e($id) ?>"<?= in_array($id, $categoryIds, true) ? ' checked' : '' ?>>
                                <label class="form-check-label" for="category-<?= e($id) ?>"><?= e($name) ?></label>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="form-text">ბიზნესის კატეგორიები, რომლებშიც დიზაინი შეკვეთის ფორმაში ჩანს. „ყველა დიზაინში“ ყოველთვის ჩანს.</div>
                <?php endif ?>
            </div>
        </div>

        <div class="form-check form-switch mb-4">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $core['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">აქტიური (ჩანს შეკვეთის ფორმაში)</label>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">შენახვა</button>
        <a href="<?= e(route('admin.designs.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>
