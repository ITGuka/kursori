<?php
/**
 * ერთი პარამეტრის ველი config/settings.php-ის განსაზღვრებიდან.
 * @var string $key "group.name"
 * @var array $field
 * @var Closure(string, string=): string $value
 * @var list<array> $languages
 */
$id = 'setting-' . str_replace('.', '-', $key);
$name = 'settings[' . $key . ']';
$type = $field['type'];
$max = $field['max'] ?? (in_array($type, ['textarea', 'code'], true) ? null : 255);

$input = function (string $inputName, string $inputId, string $current, string $errorKey) use ($type, $field, $max): string {
    $invalid = errors($errorKey) ? ' is-invalid' : '';
    $maxAttr = $max ? ' maxlength="' . (int)$max . '"' : '';
    $common = 'class="form-control' . $invalid . '" id="' . e($inputId) . '" name="' . e($inputName) . '"' . $maxAttr;

    $html = match ($type) {
        'textarea' => '<textarea ' . $common . ' rows="3">' . e($current) . '</textarea>',
        'code'     => '<textarea ' . $common . ' rows="5" spellcheck="false" style="font-family: monospace; font-size: 13px">' . e($current) . '</textarea>',
        'select'   => '<select class="form-select' . $invalid . '" id="' . e($inputId) . '" name="' . e($inputName) . '">'
                      . implode('', array_map(fn ($v, $l) => '<option value="' . e($v) . '"' . ((string)$v === $current ? ' selected' : '') . '>' . e($l) . '</option>',
                          array_keys($field['options'] ?? []), $field['options'] ?? []))
                      . '</select>',
        default    => '<input type="' . match ($type) { 'email' => 'email', 'url' => 'url', 'tel' => 'tel', 'number' => 'number', default => 'text' } . '" '
                      . $common . ' value="' . e($current) . '"' . ($type === 'image' ? ' placeholder="/uploads/... ან https://..."' : '') . '>',
    };
    if ($error = errors($errorKey)) {
        $html .= '<div class="invalid-feedback">' . e($error) . '</div>';
    }
    return $html;
};
?>
<div class="mb-4">
    <?php if ($type === 'image'): ?>
        <label class="form-label" for="<?= e($id) ?>"><?= e($field['label'] ?? $key) ?></label>
        <?= $this->insert('admin/partials/media-input', ['name' => $name, 'value' => $value($key), 'inputId' => $id, 'invalid' => (bool)errors($key)]) ?>
        <?php if ($error = errors($key)): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif ?>
    <?php elseif ($type === 'toggle'): ?>
        <?php $checked = $value($key) === '' ? (bool)($field['default'] ?? false) : $value($key) === '1'; ?>
        <div class="form-check form-switch">
            <input type="hidden" name="<?= e($name) ?>" value="0">
            <input class="form-check-input" type="checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" value="1"<?= $checked ? ' checked' : '' ?>>
            <label class="form-check-label" for="<?= e($id) ?>"><?= e($field['label'] ?? $key) ?></label>
        </div>
    <?php elseif (!empty($field['translatable'])): ?>
        <label class="form-label d-block"><?= e($field['label'] ?? $key) ?> <span class="badge bg-info text-dark fw-normal">თარგმნადი</span></label>
        <div class="row g-2">
            <?php foreach ($languages as $language): ?>
                <?php $code = $language['code']; ?>
                <div class="col-md-6">
                    <label class="small text-muted mb-1" for="<?= e("$id-$code") ?>"><?= e($language['native_name']) ?></label>
                    <?= $input($name . '[' . $code . ']', "$id-$code", $value($key, $code), "$key.$code") ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <label class="form-label" for="<?= e($id) ?>"><?= e($field['label'] ?? $key) ?></label>
        <?= $input($name, $id, $value($key), $key) ?>
    <?php endif ?>

    <?php
    $default = $field['default'] ?? null;
    $defaultText = is_array($default) ? ($default[config('app.default_locale')] ?? reset($default)) : (is_string($default) ? $default : null);
    ?>
    <?php if (!empty($field['help']) || ($defaultText && $type !== 'toggle')): ?>
        <div class="form-text">
            <?= e($field['help'] ?? '') ?>
            <?php if ($defaultText && $type !== 'toggle'): ?>
                <span class="d-block">ცარიელის შემთხვევაში: „<?= e($defaultText) ?>“</span>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>
