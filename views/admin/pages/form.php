<?php
/**
 * @var Core\View\Template $this
 * @var array|null $page
 * @var array $data
 * @var array<string, array> $translations
 * @var array<string, string> $formErrors
 */
$this->layout('admin/layouts/app');
$isEdit = $page !== null;
$action = $isEdit ? route('admin.pages.update', ['id' => $page['id']]) : route('admin.pages.store');
$err = fn (string $key) => $formErrors[$key] ?? null;
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>
<?php if ($formErrors !== []): ?>
    <div class="alert alert-danger">ფორმაში შეცდომებია — შეამოწმეთ მონიშნული ველები.</div>
<?php endif ?>

<div class="row g-4">
    <div class="col-xl-5">
        <form method="post" action="<?= e($action) ?>" class="bg-light rounded p-4" novalidate>
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h6 class="mb-0"><?= $isEdit ? 'გვერდის პარამეტრები' : e($title) ?></h6>
                <div class="d-flex align-items-center gap-2">
                    <?= $this->insert('admin/partials/lang-switch') ?>
                    <?php if ($viewUrl !== ''): ?>
                        <a href="<?= e($viewUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="fa fa-external-link-alt me-1"></i>ნახვა</a>
                    <?php endif ?>
                </div>
            </div>

            <div class="mb-3">
                <?php foreach ($languages as $language): ?>
                    <?php $code = $language['code']; $t = $translations[$code] ?? []; ?>
                    <fieldset class="border rounded p-3 mb-3" id="lang-<?= e($code) ?>" data-slug-group data-lang="<?= e($code) ?>">
                        <legend class="float-none w-auto px-2 fs-6 mb-0"><?= e($language['native_name']) ?><?= $code === $defaultLang ? ' *' : '' ?></legend>
                        <div class="mb-3">
                            <label class="form-label" for="title-<?= e($code) ?>">სათაური</label>
                            <input type="text" class="form-control<?= $invalid("$code.title") ?>" id="title-<?= e($code) ?>" name="t[<?= e($code) ?>][title]"
                                   maxlength="190" value="<?= e($t['title'] ?? '') ?>" data-slug-source>
                            <?php if ($code !== $defaultLang): ?><div class="form-text">ცარიელი — გვერდი ამ ენაზე არ ჩანს.</div><?php endif ?>
                            <?= $feedback("$code.title") ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="slug-<?= e($code) ?>">მისამართი (slug)</label>
                            <div class="input-group">
                                <span class="input-group-text">/<?= e($code) ?>/</span>
                                <input type="text" class="form-control<?= $invalid("$code.slug") ?>" id="slug-<?= e($code) ?>" name="t[<?= e($code) ?>][slug]"
                                       maxlength="150" value="<?= e($t['slug'] ?? '') ?>" data-slug-target data-touched="<?= ($t['slug'] ?? '') !== '' ? '1' : '0' ?>">
                                <?= $feedback("$code.slug") ?>
                            </div>
                            <div class="form-text">ცარიელი — სათაურიდან ავტომატურად. მთავარ გვერდზე არ გამოიყენება.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="meta-title-<?= e($code) ?>">Meta title</label>
                            <input type="text" class="form-control" id="meta-title-<?= e($code) ?>" name="t[<?= e($code) ?>][meta_title]" maxlength="190" value="<?= e($t['meta_title'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label" for="meta-desc-<?= e($code) ?>">Meta description</label>
                            <textarea class="form-control" id="meta-desc-<?= e($code) ?>" name="t[<?= e($code) ?>][meta_description]" maxlength="300" rows="2"><?= e($t['meta_description'] ?? '') ?></textarea>
                            <div class="form-text">ცარიელი — პარამეტრები → SEO.</div>
                        </div>
                    </fieldset>
                <?php endforeach ?>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-8">
                    <label class="form-label" for="parent_id">მშობელი გვერდი</label>
                    <select class="form-select<?= $invalid('parent_id') ?>" id="parent_id" name="parent_id">
                        <option value="">— ზედა დონე —</option>
                        <?php foreach ($parents as $id => $label): ?>
                            <option value="<?= e($id) ?>"<?= (int)$data['parent_id'] === $id ? ' selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach ?>
                    </select>
                    <?= $feedback('parent_id') ?>
                </div>
                <div class="col-sm-4">
                    <label class="form-label" for="sort">რიგი</label>
                    <input type="number" class="form-control" id="sort" name="sort" value="<?= e($data['sort']) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">OG სურათი (გაზიარებისას)</label>
                <?= $this->insert('admin/partials/media-input', ['name' => 'og_image_id', 'value' => $data['og_image_id'], 'inputId' => 'og_image_id', 'invalid' => (bool)$err('og_image_id')]) ?>
                <?php if ($e = $err('og_image_id')): ?><div class="invalid-feedback d-block"><?= e($e) ?></div><?php endif ?>
            </div>

            <div class="form-check form-switch mb-2">
                <input type="hidden" name="status" value="draft">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="published" <?= $data['status'] === 'published' ? 'checked' : '' ?>>
                <label class="form-check-label" for="status">გამოქვეყნებული</label>
            </div>
            <div class="form-check form-switch mb-4">
                <input type="hidden" name="is_home" value="0">
                <input class="form-check-input" type="checkbox" id="is_home" name="is_home" value="1" <?= $data['is_home'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_home">მთავარი გვერდი (/<?= e($defaultLang) ?>)</label>
            </div>

            <button type="submit" class="btn btn-primary">შენახვა</button>
            <a href="<?= e(route('admin.pages.index')) ?>" class="btn btn-outline-secondary ms-2">სიაში დაბრუნება</a>
        </form>
    </div>

    <div class="col-xl-7">
        <div class="bg-light rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">ბლოკები</h6>
                <?php if ($isEdit): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fa fa-plus me-1"></i>ბლოკის დამატება</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($blockTypes as $type => $definition): ?>
                                <li><a class="dropdown-item" href="<?= e(route('admin.blocks.create', ['id' => $page['id'], 'type' => $type])) ?>">
                                    <i class="<?= e($definition['icon'] ?? 'fa fa-square') ?> me-2 text-primary"></i><?= e($definition['label']) ?></a></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif ?>
            </div>

            <?php if (!$isEdit): ?>
                <p class="text-muted mb-0">ბლოკების დასამატებლად ჯერ შეინახეთ გვერდი.</p>
            <?php elseif ($blocks === []): ?>
                <p class="text-muted mb-0">გვერდზე ბლოკები ჯერ არ არის. დაიწყეთ „სლაიდერით“ ან „გვერდის სათაურით“ — ისინი navbar-ის ქვეშ ჯდება.</p>
            <?php else: ?>
                <ul class="list-unstyled block-list mb-0" data-block-sort="<?= e(route('admin.blocks.reorder', ['id' => $page['id']])) ?>">
                    <?php foreach ($blocks as $block): ?>
                        <li class="block-row<?= $block['is_active'] ? '' : ' is-hidden' ?>" id="block-<?= e($block['id']) ?>" data-id="<?= e($block['id']) ?>">
                            <span class="block-handle" title="გადაადგილება"><i class="fa fa-grip-vertical"></i></span>
                            <i class="<?= e($block['icon']) ?> text-primary"></i>
                            <div class="block-info">
                                <a href="<?= e(route('admin.blocks.edit', ['id' => $block['id']])) ?>" class="fw-semibold"><?= e($block['label']) ?></a>
                                <?php if (!$block['is_active']): ?><span class="badge bg-secondary ms-1">დამალული</span><?php endif ?>
                                <?php if ($block['summary'] !== ''): ?><div class="small text-muted text-truncate"><?= e($block['summary']) ?></div><?php endif ?>
                            </div>
                            <div class="block-actions">
                                <form method="post" action="<?= e(route('admin.blocks.toggle', ['id' => $block['id']])) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-secondary" title="<?= $block['is_active'] ? 'დამალვა' : 'გამოჩენა' ?>"><i class="fa <?= $block['is_active'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i></button>
                                </form>
                                <form method="post" action="<?= e(route('admin.blocks.duplicate', ['id' => $block['id']])) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-secondary" title="კოპირება"><i class="fa fa-clone"></i></button>
                                </form>
                                <a href="<?= e(route('admin.blocks.edit', ['id' => $block['id']])) ?>" class="btn btn-sm btn-outline-primary" title="რედაქტირება"><i class="fa fa-pen"></i></a>
                                <form method="post" action="<?= e(route('admin.blocks.destroy', ['id' => $block['id']])) ?>" data-confirm="წავშალოთ ბლოკი „<?= e($block['label']) ?>“?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-outline-danger" title="წაშლა"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach ?>
                </ul>
                <p class="small text-muted mt-3 mb-0">რიგის შესაცვლელად გადაათრიეთ <i class="fa fa-grip-vertical"></i>-ით. პირველი ბლოკი თუ სლაიდერი ან გვერდის სათაური არ არის, საიტზე ავტომატურად დაემატება გვერდის სათაური.</p>
            <?php endif ?>
        </div>
    </div>
</div>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= asset('admin/js/page-editor.js') ?>"></script>
<?php $this->stop() ?>
