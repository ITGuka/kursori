<?php
/**
 * რამდენიმე ფაილის არჩევა შეკვეთის ფორმებში (ველი attachments[], config/forms.php → attachments; სერვერზე App\Services\Attachments).
 * JS (assets/front/js/order-form.js): ფაილები ემატება რამდენჯერმე არჩევით ან ჩაგდებით, სიაში იშლება ×-ით,
 * ზომა და რაოდენობა მოწმდება გაგზავნამდე. JS-ის გარეშე — ჩვეულებრივი multiple ველი.
 * @var string $form
 * @var string $uid
 * @var string $hint მინიშნება (HTML, უკვე escape-ებული) — ცარიელი: არ ჩანს
 */
use App\Services\Attachments;
use Core\Support\Session;

$config = Attachments::config();
$id = "$form-attachments-$uid";
$isThis = Session::get('_form') === $form;
$error = $isThis ? errors('attachments') : null;
$mb = fn (int $bytes) => Attachments::megabytes($bytes);
$limits = t('form.attachments_limits', ['count' => $config['max_files'], 'max' => $mb((int)$config['max_size']), 'total' => $mb((int)$config['max_total'])]);
?>
<div class="form-files" data-form-files data-max-files="<?= (int)$config['max_files'] ?>" data-max-size="<?= (int)$config['max_size'] ?>" data-max-total="<?= (int)$config['max_total'] ?>"
     data-msg-too-large="<?= e(t('form.upload_too_large', ['max' => $mb((int)$config['max_size'])])) ?>"
     data-msg-too-many="<?= e(t('form.upload_too_many', ['count' => $config['max_files']])) ?>"
     data-msg-total="<?= e(t('form.upload_total', ['max' => $mb((int)$config['max_total'])])) ?>"
     data-msg-remove="<?= e(t('form.attachment_remove')) ?>">
    <span class="form-choice-legend d-block" id="<?= e($id) ?>-label"><?= e(t('form.attachments')) ?></span>
    <input type="file" class="form-files-input" name="attachments[]" id="<?= e($id) ?>" multiple
           accept=".<?= e(implode(',.', array_unique([...array_values($config['allowed']), 'jpeg']))) ?>"
           aria-labelledby="<?= e($id) ?>-label <?= e($id) ?>-drop" aria-describedby="<?= e($id) ?>-hint<?= $error ? ' ' . e($id) . '-error' : '' ?>">
    <label class="form-files-drop" for="<?= e($id) ?>" id="<?= e($id) ?>-drop" data-form-files-drop>
        <i class="fa fa-cloud-upload-alt form-files-icon" aria-hidden="true"></i>
        <span class="form-files-add"><?= e(t('form.attachments_add')) ?></span>
        <span class="form-hint"><?= e(t('form.attachments_drop')) ?></span>
    </label>
    <ul class="form-files-list" data-form-files-list aria-live="polite"></ul>
    <p class="form-hint mt-2 mb-0" id="<?= e($id) ?>-hint"><?= ($hint ?? '') !== '' ? $hint . ' ' : '' ?><?= e($limits) ?></p>
    <?php // ფაილი redirect-ს ვერ გადაურჩება — შეცდომის შემდეგ თავიდან უნდა აირჩიონ ?>
    <?php if ($isThis && errors() !== [] && !$error): ?><p class="form-hint text-warning mt-1 mb-0"><?= e(t('form.upload_again')) ?></p><?php endif ?>
    <div class="text-danger small mt-2<?= $error ? '' : ' d-none' ?>" id="<?= e($id) ?>-error" data-form-files-error<?= $error ? ' data-wizard-server-error' : '' ?>><?= $error ? e($error) : '' ?></div>
</div>
