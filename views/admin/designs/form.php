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

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.designs.index')) ?>">დიზაინები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'რედაქტირება' : 'ახალი' ?></li>
    </ol>
</nav>

<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger" role="alert">ფორმაში შეცდომებია — შეამოწმეთ მონიშნული ველები.<?= isset($formErrors['template']) ? ' ფაილის ველი თავიდან აირჩიეთ.' : '' ?></div>
<?php endif ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="card" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-palette"></i></span>
            </div>
            <h5 class="card-title mb-0"><?= $isEdit ? 'დიზაინი' : 'ახალი დიზაინი' ?></h5>
        </div>
        <?php if ($previewUrl !== ''): ?>
            <a href="<?= e($previewUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-link-external me-1"></i>შაბლონის ნახვა</a>
        <?php endif ?>
    </div>

    <div class="card-body">
        <div class="block-form-fields">
            <div class="mb-5">
                <label class="form-label d-block">სახელი * <span class="badge bg-label-info ms-1">თარგმნადი</span></label>
                <div class="row g-4">
                    <?php foreach ($languages as $language): ?>
                        <?php $code = $language['code']; ?>
                        <div class="col-md-6">
                            <label class="form-label small text-body-secondary mb-1" for="title-<?= e($code) ?>"><?= e($language['native_name']) ?><?= $code === $defaultLang ? ' *' : '' ?></label>
                            <input type="text" class="form-control<?= $invalid("t.$code") ?>" id="title-<?= e($code) ?>" name="t[<?= e($code) ?>]" maxlength="190" value="<?= e($titles[$code] ?? '') ?>">
                            <?= $feedback("t.$code") ?>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="form-text">ცარიელი სახელი ენაზე — საიტზე ძირითადი ენის სახელი ჩანს.</div>
            </div>

            <div class="mb-5">
                <label class="form-label" for="template">შაბლონი (ZIP)<?= $isEdit ? '' : ' *' ?></label>
                <?php if ($isEdit && $previewUrl !== ''): ?>
                    <div class="d-flex align-items-center gap-2 small mb-2">
                        <i class="icon-base bx bx-archive text-primary"></i>
                        <span>ამჟამინდელი: <code><?= e($design['entry']) ?></code> · <?= e($design['files']) ?> ფაილი, <?= e(number_format($design['size'] / 1024 / 1024, 1)) ?> MB</span>
                    </div>
                <?php endif ?>
                <input type="file" class="form-control<?= $invalid('template') ?>" id="template" name="template" accept=".zip,application/zip">
                <?= $feedback('template') ?>
                <div class="form-text">
                    HTML/CSS/JS შაბლონი ZIP არქივად (მაქს. <?= e(round($maxUpload / 1024 / 1024)) ?> MB), რომელშიც არის <code>index.html</code> — ზედა დონეზე ან ერთ საქაღალდეში.
                    PHP და სხვა სერვერული ფაილები გამოიტოვება.<?= $isEdit ? ' ახალი არქივი ძველს ჩაანაცვლებს.' : '' ?>
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label d-block">კატეგორიები</label>
                <?php if ($categoryOptions === []): ?>
                    <small class="text-body-secondary">კატეგორიები ჯერ არ არის — <a href="<?= e(route('admin.categories.create', ['type' => Design::CATEGORIES])) ?>">დამატება</a>.</small>
                <?php else: ?>
                    <div class="row g-2 border rounded p-3 mx-0 design-categories">
                        <?php foreach ($categoryOptions as $id => $name): ?>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="<?= e($id) ?>" id="category-<?= e($id) ?>"<?= in_array($id, $categoryIds, true) ? ' checked' : '' ?>>
                                    <label class="form-check-label" for="category-<?= e($id) ?>"><?= e($name) ?></label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="form-text">ბიზნესის კატეგორიები, რომლებშიც დიზაინი შეკვეთის ფორმაში ჩანს. „ყველა დიზაინში“ ყოველთვის ჩანს.</div>
                <?php endif ?>
            </div>

            <div class="form-check form-switch mb-0">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $core['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">აქტიური (ჩანს შეკვეთის ფორმაში)</label>
            </div>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <a href="<?= e(route('admin.designs.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>
