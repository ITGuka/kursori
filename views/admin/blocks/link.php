<?php
/**
 * ბლოკის link ველი: გვერდი (URL ენის მიხედვით ავტომატურად) ან ხელით ბმული.
 * @var string $inputName
 * @var array{type?: string, page_id?: ?int, url?: string, new_tab?: bool} $value
 * @var string $id
 * @var bool $invalid
 * @var array<int, string> $pageOptions
 */
$linkType = $value['type'] ?? '';
?>
<div class="link-input<?= $invalid ? ' is-invalid' : '' ?>" data-link-input>
    <div class="row g-2 align-items-center">
        <div class="col-sm-3">
            <select class="form-select form-select-sm" name="<?= e($inputName) ?>[type]" id="<?= e($id) ?>" data-link-type aria-label="ბმულის ტიპი">
                <option value=""<?= $linkType === '' ? ' selected' : '' ?>>— არ არის —</option>
                <option value="page"<?= $linkType === 'page' ? ' selected' : '' ?>>გვერდი</option>
                <option value="url"<?= $linkType === 'url' ? ' selected' : '' ?>>ბმული</option>
            </select>
        </div>
        <div class="col-sm-6" data-link-show="page">
            <select class="form-select form-select-sm" name="<?= e($inputName) ?>[page_id]" aria-label="გვერდი">
                <?php foreach ($pageOptions as $pageId => $pageTitle): ?>
                    <option value="<?= e($pageId) ?>"<?= (int)($value['page_id'] ?? 0) === $pageId ? ' selected' : '' ?>><?= e($pageTitle) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-6" data-link-show="url">
            <input type="text" class="form-control form-control-sm" name="<?= e($inputName) ?>[url]" value="<?= e($value['url'] ?? '') ?>"
                   placeholder="https://..., /ka/..., #contact, mailto:, tel:" aria-label="ბმული">
        </div>
        <div class="col-sm-3" data-link-show="page url">
            <div class="form-check mb-0">
                <input type="hidden" name="<?= e($inputName) ?>[new_tab]" value="0">
                <input class="form-check-input" type="checkbox" name="<?= e($inputName) ?>[new_tab]" value="1" id="<?= e($id) ?>-tab"<?= !empty($value['new_tab']) ? ' checked' : '' ?>>
                <label class="form-check-label small" for="<?= e($id) ?>-tab">ახალ ფანჯარაში</label>
            </div>
        </div>
    </div>
</div>
