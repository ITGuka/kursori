<?php
/**
 * ბლოკის ერთი ველი სქემიდან (რეკურსიული repeater-ისთვის).
 * @var Core\View\Template $this
 * @var string $name ველის სახელი
 * @var array $field სქემა
 * @var mixed $settingsVal არათარგმნადი მნიშვნელობა
 * @var array<string, mixed> $contentVals თარგმნადი: lang => მნიშვნელობა
 * @var string $path შეცდომის ბილიკი (მაგ. "slides.0.title")
 * @var string $sName input-ის სახელი არათარგმნადისთვის
 * @var Closure(string): string $cName input-ის სახელი თარგმნადისთვის ენის მიხედვით
 * @var array<string, string> $formErrors
 * @var list<array> $languages
 * @var array<int, string> $pageOptions
 */
$type = $field['type'] ?? 'text';
$label = $field['label'] ?? $name;
$domId = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $path) . '-' . substr(md5($sName . $path), 0, 6);

$control = function (string $inputName, mixed $value, string $id, string $errorKey) use ($type, $field, $pageOptions, $formErrors): string {
    $invalid = isset($formErrors[$errorKey]) ? ' is-invalid' : '';
    $max = isset($field['max']) && in_array($type, ['text', 'textarea'], true) ? ' maxlength="' . (int)$field['max'] . '"' : '';
    $html = match ($type) {
        'text'     => '<input type="text" class="form-control' . $invalid . '" id="' . e($id) . '" name="' . e($inputName) . '" value="' . e((string)$value) . '"' . $max . '>',
        'textarea' => '<textarea class="form-control' . $invalid . '" id="' . e($id) . '" name="' . e($inputName) . '" rows="3"' . $max . '>' . e((string)$value) . '</textarea>',
        'wysiwyg'  => '<textarea class="form-control' . $invalid . '" id="' . e($id) . '" name="' . e($inputName) . '" rows="6" data-wysiwyg>' . e((string)$value) . '</textarea>',
        'number'   => '<input type="number" class="form-control' . $invalid . '" id="' . e($id) . '" name="' . e($inputName) . '" value="' . e((string)($value ?? $field['default'] ?? '')) . '"'
                      . (isset($field['min']) ? ' min="' . (int)$field['min'] . '"' : '') . (isset($field['max']) ? ' max="' . (int)$field['max'] . '"' : '') . ' style="max-width: 200px">',
        'select'   => '<select class="form-select' . $invalid . '" id="' . e($id) . '" name="' . e($inputName) . '" style="max-width: 320px">'
                      . implode('', array_map(fn ($v, $l) => '<option value="' . e($v) . '"' . ((string)($value ?? $field['default'] ?? '') === (string)$v ? ' selected' : '') . '>' . e($l) . '</option>',
                          array_keys($field['options'] ?? []), $field['options'] ?? []))
                      . '</select>',
        'toggle'   => '<div class="form-check form-switch"><input type="hidden" name="' . e($inputName) . '" value="0">'
                      . '<input class="form-check-input" type="checkbox" id="' . e($id) . '" name="' . e($inputName) . '" value="1"'
                      . (($value ?? $field['default'] ?? false) ? ' checked' : '') . '><label class="form-check-label" for="' . e($id) . '">' . e($field['label'] ?? '') . '</label></div>',
        'image'    => $this->insert('admin/partials/media-input', ['name' => $inputName, 'value' => $value, 'inputId' => $id, 'invalid' => $invalid !== '']),
        'link'     => $this->insert('admin/blocks/link', ['inputName' => $inputName, 'value' => is_array($value) ? $value : [], 'id' => $id, 'invalid' => $invalid !== '', 'pageOptions' => $pageOptions]),
        default    => '',
    };
    if (isset($formErrors[$errorKey])) {
        $html .= '<div class="invalid-feedback d-block">' . e($formErrors[$errorKey]) . '</div>';
    }
    return $html;
};
?>

<?php if ($type === 'repeater'): ?>
    <?php
    $items = is_array($settingsVal) ? array_values($settingsVal) : [];
    $renderItem = function (array $item, string $index, string $itemId) use ($field, $name, $contentVals, $path, $sName, $languages): string {
        $html = '<div class="repeater-item" data-repeater-item><div class="repeater-item-head">'
            . '<span class="repeater-handle" title="გადაადგილება"><i class="fa fa-grip-vertical"></i></span>'
            . '<span class="repeater-title small text-muted" data-repeater-number></span>'
            . '<button type="button" class="btn btn-sm btn-link text-danger ms-auto" data-repeater-remove><i class="fa fa-times me-1"></i>წაშლა</button></div>'
            . '<input type="hidden" name="' . e($sName . "[$index][_id]") . '" value="' . e($itemId) . '">';
        foreach ($field['fields'] as $sub => $subField) {
            $html .= $this->insert('admin/blocks/field', [
                'name'        => $sub,
                'field'       => $subField,
                'settingsVal' => $item[$sub] ?? null,
                'contentVals' => array_combine(
                    array_column($languages, 'code'),
                    array_map(fn ($l) => $contentVals[$l['code']][$itemId][$sub] ?? null, $languages),
                ),
                'path'        => "$path.$index.$sub",
                'sName'       => $sName . "[$index][$sub]",
                'cName'       => fn (string $lang) => "content[$lang][$name][$itemId][$sub]",
            ]);
        }
        return $html . '</div>';
    };
    ?>
    <fieldset class="repeater mb-4<?= isset($formErrors[$path]) ? ' is-invalid' : '' ?>" data-repeater data-max="<?= (int)($field['max_items'] ?? 50) ?>">
        <legend class="fs-6 fw-semibold"><?= e($label) ?></legend>
        <?php if (isset($formErrors[$path])): ?><div class="text-danger small mb-2"><?= e($formErrors[$path]) ?></div><?php endif ?>
        <div class="repeater-items" data-repeater-items>
            <?php foreach ($items as $i => $item): ?>
                <?= $renderItem((array)$item, (string)$i, (string)($item['_id'] ?? bin2hex(random_bytes(5)))) ?>
            <?php endforeach ?>
        </div>
        <template data-repeater-template><?= $renderItem([], '__INDEX__', '__ID__') ?></template>
        <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add><i class="fa fa-plus me-1"></i>დამატება</button>
    </fieldset>

<?php elseif ($type === 'toggle'): ?>
    <div class="mb-3">
        <?= $control($sName, $settingsVal, $domId, $path) ?>
        <?php if (!empty($field['help'])): ?><div class="form-text"><?= e($field['help']) ?></div><?php endif ?>
    </div>

<?php elseif (!empty($field['translatable'])): ?>
    <div class="mb-3">
        <label class="form-label d-block"><?= e($label) ?><?= !empty($field['required']) ? ' *' : '' ?> <span class="badge bg-info text-dark fw-normal">თარგმნადი</span></label>
        <div class="row g-2">
            <?php foreach ($languages as $language): ?>
                <?php $code = $language['code']; ?>
                <div class="<?= $type === 'wysiwyg' ? 'col-12' : 'col-md-6' ?>">
                    <label class="small text-muted mb-1" for="<?= e("$domId-$code") ?>"><?= e($language['native_name']) ?></label>
                    <?= $control($cName($code), $contentVals[$code] ?? '', "$domId-$code", "$path.$code") ?>
                </div>
            <?php endforeach ?>
        </div>
        <?php if (!empty($field['help'])): ?><div class="form-text"><?= e($field['help']) ?></div><?php endif ?>
    </div>

<?php else: ?>
    <div class="mb-3">
        <label class="form-label" for="<?= e($domId) ?>"><?= e($label) ?><?= !empty($field['required']) ? ' *' : '' ?></label>
        <?= $control($sName, $settingsVal, $domId, $path) ?>
        <?php if (!empty($field['help'])): ?><div class="form-text"><?= e($field['help']) ?></div><?php endif ?>
    </div>
<?php endif ?>
