<?php
/**
 * Kursori-ის სტილის ველი (form-floating). წინა მნიშვნელობა და შეცდომა — მხოლოდ თუ ბოლოს ეს ფორმა გაიგზავნა.
 * @var string $form ფორმის სახელი (config/forms.php)
 * @var string $name
 * @var string $label
 * @var string $type text | email | tel | textarea
 * @var bool $required
 * @var int $max
 * @var string $uid უნიკალური სუფიქსი id-სთვის (ერთ გვერდზე რამდენიმე ფორმა)
 */
use Core\Support\Session;

$isThis = Session::get('_form') === $form;
$value = $isThis ? (string)old($name, '') : '';
$error = $isThis ? errors($name) : null;
$id = "$form-$name-$uid";
$class = 'form-control bg-secondary border-0' . ($error ? ' is-invalid' : '');
$attrs = ' id="' . e($id) . '" name="' . e($name) . '" placeholder="' . e($label) . '" maxlength="' . (int)$max . '"' . (!empty($required) ? ' required' : '')
    . ($error ? ' aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '');
?>
<div class="form-floating">
    <?php if ($type === 'textarea'): ?>
        <textarea class="<?= $class ?>"<?= $attrs ?> style="height: 150px"><?= e($value) ?></textarea>
    <?php else: ?>
        <input type="<?= e($type) ?>" class="<?= $class ?>"<?= $attrs ?> value="<?= e($value) ?>">
    <?php endif ?>
    <label for="<?= e($id) ?>"><?= e($label) ?><?= !empty($required) ? ' *' : '' ?></label>
    <?php if ($error): ?><div class="invalid-feedback" id="<?= e($id) ?>-error"><?= e($error) ?></div><?php endif ?>
</div>
