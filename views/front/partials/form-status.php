<?php
/**
 * ფორმის შედეგი (flash) და honeypot ველი. გამოიყენება ფორმის შიგნით:
 *   <?= $this->insert('front/partials/form-status', ['form' => 'contact']) ?>
 * @var string $form
 */
use Core\Support\Session;

$isThis = Session::get('_form') === $form;
$success = $isThis ? Session::get('_form_success') : null;
$error = $isThis ? Session::get('_form_error') : null;
$honeypot = (string)config('forms.honeypot', 'website');
?>
<?php if (is_string($success)): ?>
    <div class="alert alert-success border-0" role="status"><?= e($success) ?></div>
<?php elseif (is_string($error)): ?>
    <div class="alert alert-danger border-0" role="alert"><?= e($error) ?></div>
<?php endif ?>
<?php // honeypot: ადამიანი ვერ ხედავს და არ ავსებს; ბოტი ავსებს → გაგზავნა იგნორირდება ?>
<div class="form-hp" aria-hidden="true">
    <label for="<?= e("$form-$honeypot") ?>">Website</label>
    <input type="text" id="<?= e("$form-$honeypot") ?>" name="<?= e($honeypot) ?>" tabindex="-1" autocomplete="off">
</div>
