<?php
/**
 * ფორმის honeypot ველი. გამოიყენება ფორმის შიგნით:
 *   <?= $this->insert('front/partials/form-status', ['form' => 'contact']) ?>
 * შედეგი (flash) აქ აღარ ჩანს — toast-ად, layout-ში (front/partials/toasts).
 * @var string $form
 */
$honeypot = (string)config('forms.honeypot', 'website');
?>
<?php // honeypot: ადამიანი ვერ ხედავს და არ ავსებს; ბოტი ავსებს → გაგზავნა იგნორირდება ?>
<div class="form-hp" aria-hidden="true">
    <label for="<?= e("$form-$honeypot") ?>">Website</label>
    <input type="text" id="<?= e("$form-$honeypot") ?>" name="<?= e($honeypot) ?>" tabindex="-1" autocomplete="off">
</div>
