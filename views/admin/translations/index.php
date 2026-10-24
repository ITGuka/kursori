<?php
/** @var Core\View\Template $this */
use Core\Support\Session;

$this->layout('admin/layouts/app');
$openNew = (bool)Session::get('open_new_key') || errors() !== [];
$newValues = (array)old('new_values', []);
$invalid = fn (string $field) => errors($field) ? ' is-invalid' : '';
$feedback = fn (string $field) => ($error = errors($field)) ? '<div class="invalid-feedback">' . e($error) . '</div>' : '';
?>

<div class="bg-light rounded p-4 mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <h6 class="mb-0"><?= e($title) ?> <span class="text-muted fw-normal">(<?= e($total) ?>)</span></h6>
        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#new-key"
                aria-expanded="<?= $openNew ? 'true' : 'false' ?>"><i class="fa fa-plus me-1"></i>ახალი გასაღები</button>
    </div>

    <div class="collapse<?= $openNew ? ' show' : '' ?>" id="new-key">
        <form method="post" action="<?= e(route('admin.translations.store')) ?>" class="border rounded p-3 mb-3" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="group">ჯგუფი</label>
                    <input class="form-control<?= $invalid('group') ?>" id="group" name="group" list="groups" value="<?= e(old('group', $filters['group'])) ?>" placeholder="მაგ. nav">
                    <?= $feedback('group') ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="key">გასაღები</label>
                    <input class="form-control<?= $invalid('key') ?>" id="key" name="key" value="<?= e(old('key')) ?>" placeholder="მაგ. portfolio">
                    <?= $feedback('key') ?>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="description">აღწერა (არასავალდებულო)</label>
                    <input class="form-control<?= $invalid('description') ?>" id="description" name="description" maxlength="255" value="<?= e(old('description')) ?>" placeholder="სად ჩანს ეს ტექსტი">
                    <?= $feedback('description') ?>
                </div>
                <?php foreach ($languages as $language): ?>
                    <div class="col-md-6">
                        <label class="form-label" for="new-<?= e($language['code']) ?>"><?= e($language['native_name']) ?></label>
                        <input class="form-control" id="new-<?= e($language['code']) ?>" name="new_values[<?= e($language['code']) ?>]"
                               value="<?= e($newValues[$language['code']] ?? '') ?>">
                    </div>
                <?php endforeach ?>
            </div>
            <p class="form-text mb-2">ჯგუფი და გასაღები — მხოლოდ ლათინური პატარა ასოები, ციფრები, „_“ (გასაღებში ასევე „.“). შაბლონში: <code>t('group.key')</code></p>
            <button type="submit" class="btn btn-sm btn-primary">დამატება</button>
        </form>
    </div>
    <datalist id="groups">
        <?php foreach ($groups as $group): ?><option value="<?= e($group) ?>"><?php endforeach ?>
    </datalist>

    <form method="get" action="<?= e(route('admin.translations.index')) ?>" class="row g-2 align-items-center">
        <div class="col-sm-3">
            <select name="group" class="form-select form-select-sm" aria-label="ჯგუფი">
                <option value="">ყველა ჯგუფი</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= e($group) ?>"<?= $group === $filters['group'] ? ' selected' : '' ?>><?= e($group) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-4">
            <input type="search" name="q" class="form-control form-control-sm" placeholder="ძებნა გასაღებში ან ტექსტში" value="<?= e($filters['q']) ?>">
        </div>
        <div class="col-auto">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="missing" value="1" id="missing"<?= $filters['missing'] ? ' checked' : '' ?>>
                <label class="form-check-label" for="missing">მხოლოდ ნაკლული</label>
            </div>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary" type="submit">ფილტრი</button>
            <?php if ($filters['group'] !== '' || $filters['q'] !== '' || $filters['missing']): ?>
                <a class="btn btn-sm btn-link" href="<?= e(route('admin.translations.index')) ?>">გასუფთავება</a>
            <?php endif ?>
        </div>
    </form>
</div>

<div class="bg-light rounded p-4">
    <?php if ($rows === []): ?>
        <p class="mb-0 text-muted">ჩანაწერები ვერ მოიძებნა.</p>
    <?php else: ?>
        <form method="post" action="<?= e(route('admin.translations.update')) ?>" id="translations-form">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">
            <div class="table-responsive">
                <table class="table align-middle table-bordered mb-0">
                    <thead>
                    <tr class="text-dark">
                        <th style="min-width: 180px">გასაღები</th>
                        <?php foreach ($languages as $language): ?>
                            <th style="min-width: 240px"><?= e($language['native_name']) ?><?= $language['is_active'] ? '' : ' <span class="badge bg-secondary">გათიშული</span>' ?></th>
                        <?php endforeach ?>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <code><?= e($row['group'] . '.' . $row['key']) ?></code>
                                <?php if ($row['description']): ?><div class="small text-muted"><?= e($row['description']) ?></div><?php endif ?>
                            </td>
                            <?php foreach ($languages as $language): ?>
                                <?php $value = $row['values'][$language['code']] ?? ''; ?>
                                <td>
                                    <textarea class="form-control form-control-sm<?= $value === '' ? ' border-warning' : '' ?>" rows="1"
                                              name="values[<?= e($row['id']) ?>][<?= e($language['code']) ?>]"
                                              aria-label="<?= e($row['group'] . '.' . $row['key'] . ' — ' . $language['native_name']) ?>"><?= e($value) ?></textarea>
                                </td>
                            <?php endforeach ?>
                            <td class="text-end">
                                <button type="submit" form="delete-<?= e($row['id']) ?>" class="btn btn-sm btn-outline-danger" title="წაშლა">
                                    <i class="fa fa-trash"></i><span class="visually-hidden">წაშლა</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                <button type="submit" class="btn btn-primary">შენახვა</button>
                <?= $this->insert('admin/partials/pagination', ['page' => $page, 'pages' => $pages]) ?>
            </div>
        </form>

        <?php // წაშლის ფორმები ცხრილის ფორმის გარეთ (ჩადგმული ფორმა HTML-ში დაუშვებელია) ?>
        <?php foreach ($rows as $row): ?>
            <form method="post" action="<?= e(route('admin.translations.destroy', ['id' => $row['id']])) ?>" id="delete-<?= e($row['id']) ?>"
                  data-confirm="წავშალოთ გასაღები „<?= e($row['group'] . '.' . $row['key']) ?>“ ყველა ენაზე?" class="d-none">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
            </form>
        <?php endforeach ?>
    <?php endif ?>
</div>
