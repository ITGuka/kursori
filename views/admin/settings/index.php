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
?>

<form method="post" action="<?= e(route('admin.settings.update')) ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="bg-light rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0"><?= e($title) ?></h6>
            <div class="d-flex align-items-center gap-2">
                <?= $this->insert('admin/partials/lang-switch') ?>
                <button type="submit" class="btn btn-sm btn-primary">შენახვა</button>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <?php foreach ($groups as $group => $definition): ?>
                <?php $groupHasError = $hasErrors && array_filter(array_keys(errors()), fn ($k) => str_starts_with($k, "$group.")); ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $group === $activeGroup ? ' active' : '' ?><?= $groupHasError ? ' text-danger' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-<?= e($group) ?>" type="button" role="tab">
                        <?= e($definition['label'] ?? $group) ?><?= $groupHasError ? ' <i class="fa fa-exclamation-circle"></i>' : '' ?>
                    </button>
                </li>
            <?php endforeach ?>
        </ul>

        <div class="tab-content" style="max-width: 900px">
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

        <button type="submit" class="btn btn-primary">შენახვა</button>
    </div>
</form>
