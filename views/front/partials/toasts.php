<?php
/**
 * საიტის შეტყობინებები (toast) — ქვედა მარცხენა კუთხეში, layout-ის დონეზე (ფორმის ადგილს არ ეძებენ).
 * წყარო — ფორმების flash: _form_success, _form_error; ველების შეცდომებზე — ზოგადი „შეამოწმეთ ველები“ (ველთან — დეტალი).
 * JS (assets/front/js/main.js): რამდენიმე წამში ქრება (hover/focus-ზე ჩერდება), X-ით იხურება.
 */
use Core\Support\Session;

$toasts = [];
if (Session::get('_form') !== null) {
    $success = Session::get('_form_success');
    $error = Session::get('_form_error');
    if (is_string($success)) {
        $toasts[] = ['success', $success];
    } elseif (is_string($error)) {
        $toasts[] = ['error', $error];
    } elseif (errors() !== []) {
        $toasts[] = ['error', t('form.fix_errors')];
    }
}
$icons = ['success' => 'fa-check-circle', 'error' => 'fa-exclamation-circle'];
?>
<div class="site-toasts" data-site-toasts>
    <?php foreach ($toasts as [$type, $message]): ?>
        <div class="site-toast is-<?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>" data-site-toast data-duration="<?= $type === 'error' ? 9000 : 7000 ?>">
            <i class="fa <?= e($icons[$type]) ?> site-toast-icon" aria-hidden="true"></i>
            <p class="site-toast-text"><?= e($message) ?></p>
            <button type="button" class="site-toast-close" data-site-toast-close aria-label="<?= e(t('common.close')) ?>"><i class="fa fa-times" aria-hidden="true"></i></button>
            <span class="site-toast-timer" aria-hidden="true"></span>
        </div>
    <?php endforeach ?>
</div>
