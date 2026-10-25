<?php
/**
 * ერთი არჩევანი ღილაკებად (radio + Bootstrap btn-check) — config/forms.php-ის options-იანი ველისთვის.
 * @var string $form
 * @var string $name
 * @var string $label
 * @var array<string, string> $options მნიშვნელობა => თარგმანის გასაღები
 * @var string $uid
 */
use Core\Support\Session;

$isThis = Session::get('_form') === $form;
$value = $isThis ? (string)old($name, '') : '';
$error = $isThis ? errors($name) : null;
$id = "$form-$name-$uid";
?>
<fieldset class="form-choice"<?= $error ? ' aria-describedby="' . e($id) . '-error"' : '' ?>>
    <legend class="form-choice-legend"><?= e($label) ?></legend>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($options as $option => $key): ?>
            <input type="radio" class="btn-check" name="<?= e($name) ?>" id="<?= e("$id-$option") ?>" value="<?= e($option) ?>" autocomplete="off"<?= $value === $option ? ' checked' : '' ?>>
            <label class="btn btn-outline-primary btn-sm border-2 px-3" for="<?= e("$id-$option") ?>"><?= e(t($key)) ?></label>
        <?php endforeach ?>
    </div>
    <?php if ($error): ?><div class="text-danger small mt-2" id="<?= e($id) ?>-error"><?= e($error) ?></div><?php endif ?>
</fieldset>
