<?php
/**
 * @var Core\View\Template $this
 * @var Core\Settings\Settings $settings
 */
$this->layout('admin/layouts/app');

$old = old('settings', null);
$hasErrors = errors() !== [];

// ველის მნიშვნელობა: ვალიდაციის შეცდომისას — გაგზავნილი, სხვაგვარად — შენახული
$value = function (string $key, string $lang = '') use ($old, $settings): string {
    if (is_array($old)) {
        $sent = $old[$key] ?? '';
        return (string)($lang === '' ? (is_array($sent) ? '' : $sent) : ($sent[$lang] ?? ''));
    }
    return $settings->raw($key, $lang);
};

// პირველი ჯგუფი, რომელშიც შეცდომაა — ის ჩანართი გაიხსნება
$activeGroup = array_key_first($groups);
foreach (array_keys(errors()) as $errorKey) {
    $activeGroup = explode('.', $errorKey)[0];
    break;
}

// ჯგუფის აიკონი ჩანართზე (config/settings.php-ში აიკონი არ წერია)
$icons = ['general' => 'bx-cog', 'contact' => 'bx-phone', 'social' => 'bx-share-alt', 'seo' => 'bx-search-alt',
          'legal' => 'bx-shield', 'orders' => 'bx-cart', 'mail' => 'bx-envelope', 'scripts' => 'bx-code-alt'];
?>

<form method="post" action="<?= e(route('admin.settings.update')) ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="nav-align-top">
        <ul class="nav nav-pills flex-wrap gap-2 mb-6" role="tablist">
            <?php foreach ($groups as $group => $definition): ?>
                <?php $groupHasError = $hasErrors && array_filter(array_keys(errors()), fn ($k) => str_starts_with($k, "$group.")); ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $group === $activeGroup ? ' active' : '' ?><?= $groupHasError ? ' text-danger' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-<?= e($group) ?>" type="button" role="tab" aria-controls="tab-<?= e($group) ?>">
                        <i class="icon-base bx <?= e($icons[$group] ?? 'bx-slider-alt') ?> icon-sm me-1"></i><?= e($definition['label'] ?? $group) ?>
                        <?php if ($groupHasError): ?><i class="icon-base bx bx-error-circle icon-sm ms-1"></i><?php endif ?>
                    </button>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h5 class="card-title mb-0"><?= e($title) ?></h5>
            <?= $this->insert('admin/partials/lang-switch') ?>
        </div>
        <div class="card-body">
            <div class="tab-content p-0 shadow-none block-form-fields">
                <?php foreach ($groups as $group => $definition): ?>
                    <div class="tab-pane fade<?= $group === $activeGroup ? ' show active' : '' ?>" id="tab-<?= e($group) ?>" role="tabpanel">
                        <?php foreach ($definition['fields'] as $name => $_): ?>
                            <?= $this->insert('admin/settings/field', [
                                'key'       => "$group.$name",
                                'field'     => $settings->field("$group.$name"),
                                'value'     => $value,
                                'languages' => $languages,
                            ]) ?>
                        <?php endforeach ?>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
        <div class="card-footer border-top">
            <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        </div>
    </div>
</form>
