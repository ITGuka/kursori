<?php
/**
 * ინდივიდუალური შეკვეთა (order_form, პაკეტი „ინდივიდუალური“) — 5 ნაბიჯად: მომსახურება → პროექტი → ბიუჯეტი და ვადა → მასალები → კონტაქტი.
 * JS-ით (assets/front/js/order-form.js) ჩანს ერთი ნაბიჯი, JS-ის გარეშე — ყველა ერთად.
 * ჯერ მხოლოდ მარკაპი: ახალ ველებს (services, site_type... contact_method) ბექენდი ჯერ არ ინახავს; name/phone/email/description — ძველებურად.
 * @var Core\View\Template $this
 * @var string $form
 * @var string $uid
 * @var bool $disabled true — სხვა პაკეტია არჩეული (fieldset disabled: ველები არ იგზავნება)
 * @var string $buttonLabel
 */
use Core\Support\Session;

$isThis = Session::get('_form') === $form;
$old = fn (string $name) => $isThis ? old($name, '') : '';
$checked = function (string $name, string $value) use ($old): string {
    $current = $old($name);
    return (is_array($current) ? in_array($value, $current, true) : $current === $value) ? ' checked' : '';
};
$id = fn (string $name, string $value = '') => "custom-$name-$uid" . ($value !== '' ? "-$value" : '');
$field = fn (string $name, string $type, int $max, bool $required, string $label) => $this->insert('front/partials/form-field',
    ['form' => $form, 'name' => $name, 'label' => $label, 'type' => $type, 'required' => $required, 'max' => $max, 'uid' => "$uid-custom"]);

// არჩევანის ბარათები: მნიშვნელობა => [თარგმანის გასაღები, შენიშვნის ტექსტი (ან ''), Font Awesome ხატულა (ან '')]
$services = [
    'web'      => ['custom.service_web', 'Web Development', 'fa-code'],
    'design'   => ['custom.service_design', 'Web Design', 'fa-pencil-ruler'],
    'branding' => ['custom.service_branding', 'Branding & Identity', 'fa-signature'],
    'seo'      => ['custom.service_seo', 'Performance & SEO', 'fa-tachometer-alt'],
    'support'  => ['custom.service_support', 'Maintenance', 'fa-tools'],
];
$siteTypes = [
    'landing'   => ['custom.type_landing', '', 'fa-file'],
    'corporate' => ['custom.type_corporate', '', 'fa-building'],
    'shop'      => ['custom.type_shop', '', 'fa-shopping-cart'],
    'app'       => ['custom.type_app', '', 'fa-cogs'],
];
$budgets = [
    'small'   => ['custom.budget_small', '', ''],
    'medium'  => ['custom.budget_medium', '', ''],
    'large'   => ['custom.budget_large', '', ''],
    'unknown' => ['custom.budget_unknown', '', ''],
];
$timelines = [
    'fast'     => ['custom.timeline_fast', t('custom.timeline_fast_note'), 'fa-bolt'],
    'standard' => ['custom.timeline_standard', t('custom.timeline_standard_note'), 'fa-calendar-alt'],
    'flexible' => ['custom.timeline_flexible', '', 'fa-mug-hot'],
];
$steps = ['service', 'scope', 'budget', 'assets', 'contact'];

// ბარათები (radio / checkbox): $multiple — name[]
$cards = function (string $name, array $options, bool $multiple, bool $required, string $col) use ($id, $checked): string {
    $html = '<div class="row g-3">';
    foreach ($options as $value => [$label, $note, $icon]) {
        $inputId = $id($name, $value);
        $html .= '<div class="' . $col . '">'
            . '<input type="' . ($multiple ? 'checkbox' : 'radio') . '" class="btn-check" name="' . e($multiple ? $name . '[]' : $name) . '" id="' . e($inputId) . '" value="' . e($value) . '" autocomplete="off"'
            . ($required && !$multiple ? ' required' : '') . $checked($name, $value) . '>'
            . '<label class="package-option custom-option" for="' . e($inputId) . '">'
            . ($icon !== '' ? '<i class="fa ' . e($icon) . ' custom-option-icon" aria-hidden="true"></i>' : '')
            . '<span class="custom-option-name">' . e(t($label)) . '</span>'
            . ($note !== '' ? '<span class="package-option-note">' . e($note) . '</span>' : '')
            . '<span class="package-option-check" aria-hidden="true"><i class="fa fa-check"></i></span>'
            . '</label></div>';
    }
    return $html . '</div>';
};
// პატარა არჩევანი ღილაკებად (chip-ები, როგორც დარგები)
$chips = function (string $name, array $options, bool $multiple) use ($id, $checked): string {
    $html = '<div class="d-flex flex-wrap gap-2">';
    foreach ($options as $value => $label) {
        $inputId = $id($name, $value);
        $html .= '<input type="' . ($multiple ? 'checkbox' : 'radio') . '" class="btn-check" name="' . e($multiple ? $name . '[]' : $name) . '" id="' . e($inputId) . '" value="' . e($value) . '" autocomplete="off"' . $checked($name, $value) . '>'
            . '<label class="btn btn-outline-primary btn-sm border-2 px-3" for="' . e($inputId) . '">'
            . ($multiple ? '<i class="fa fa-check me-1 order-extra-check" aria-hidden="true"></i>' : '') . e(t($label)) . '</label>';
    }
    return $html . '</div>';
};
?>
<fieldset class="custom-wizard" data-custom-wizard<?= $disabled ? ' disabled' : '' ?>>
    <legend class="visually-hidden"><?= e(t('order.custom_title')) ?></legend>

    <?php // ნაბიჯების ზოლი: გავლილ ნაბიჯზე დაბრუნება — დაჭერით; წინ — მხოლოდ „შემდეგით“ (ვალიდაცია) ?>
    <ol class="custom-stepper" data-wizard-stepper>
        <?php foreach ($steps as $i => $step): ?>
            <li class="custom-stepper-item<?= $i === 0 ? ' is-current' : '' ?>" data-wizard-tab="<?= $i ?>">
                <button type="button" class="custom-stepper-button" data-wizard-goto="<?= $i ?>"<?= $i === 0 ? ' aria-current="step"' : '' ?>>
                    <span class="custom-stepper-number"><?= $i + 1 ?></span>
                    <span class="custom-stepper-label"><?= e(t("custom.step_$step")) ?></span>
                </button>
            </li>
        <?php endforeach ?>
    </ol>
    <div class="custom-progress" aria-hidden="true"><span data-wizard-progress></span></div>

    <?php // 1. მომსახურება ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '1')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '1')) ?>"><span class="order-step">1</span><?= e(t('custom.services')) ?> *</h3>
        <p class="form-hint mb-3"><?= e(t('custom.services_hint')) ?></p>
        <div data-wizard-group-required>
            <?= $cards('services', $services, true, true, 'col-sm-6 col-xl-4') ?>
            <div class="text-danger small mt-2 d-none" data-wizard-error><?= e(t('custom.choose_one')) ?></div>
        </div>
    </section>

    <?php // 2. პროექტის დეტალები და მასშტაბი ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '2')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '2')) ?>"><span class="order-step">2</span><?= e(t('custom.step_scope_title')) ?></h3>
        <fieldset class="form-choice mb-4">
            <legend class="form-choice-legend"><?= e(t('custom.site_type')) ?></legend>
            <?= $cards('site_type', $siteTypes, false, false, 'col-sm-6 col-xl-3') ?>
        </fieldset>
        <fieldset class="form-choice mb-4">
            <legend class="form-choice-legend"><?= e(t('custom.pages')) ?></legend>
            <?= $chips('pages', ['1-3' => 'custom.pages_small', '4-10' => 'custom.pages_medium', '10+' => 'custom.pages_large'], false) ?>
        </fieldset>
        <fieldset class="form-choice">
            <legend class="form-choice-legend"><?= e(t('custom.features')) ?></legend>
            <?= $chips('features', [
                'cms'        => 'custom.feature_cms',
                'languages'  => 'custom.feature_languages',
                'payments'   => 'custom.feature_payments',
                'animations' => 'custom.feature_animations',
                'api'        => 'custom.feature_api',
            ], true) ?>
        </fieldset>
    </section>

    <?php // 3. ბიუჯეტი და ვადები ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '3')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '3')) ?>"><span class="order-step">3</span><?= e(t('custom.step_budget')) ?></h3>
        <fieldset class="form-choice mb-4">
            <legend class="form-choice-legend"><?= e(t('custom.budget')) ?> *</legend>
            <?= $cards('budget', $budgets, false, true, 'col-sm-6 col-xl-3') ?>
        </fieldset>
        <fieldset class="form-choice">
            <legend class="form-choice-legend"><?= e(t('custom.timeline')) ?> *</legend>
            <?= $cards('timeline', $timelines, false, true, 'col-sm-6 col-xl-4') ?>
        </fieldset>
    </section>

    <?php // 4. არსებული მასალები და ბრიფი ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '4')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '4')) ?>"><span class="order-step">4</span><?= e(t('custom.step_assets_title')) ?></h3>
        <div class="row g-4">
            <div class="col-md-6">
                <fieldset class="form-choice">
                    <legend class="form-choice-legend"><?= e(t('custom.has_brandbook')) ?></legend>
                    <?= $chips('has_brandbook', ['yes' => 'order.yes', 'no' => 'order.no'], false) ?>
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="form-choice">
                    <legend class="form-choice-legend"><?= e(t('custom.content_ready')) ?></legend>
                    <?= $chips('content_ready', ['yes' => 'custom.content_yes', 'progress' => 'custom.content_progress', 'help' => 'custom.content_help'], false) ?>
                </fieldset>
            </div>
            <div class="col-12"><?= $field('reference_url', 'url', 500, false, t('custom.reference_url')) ?></div>
            <div class="col-12">
                <?= $field('description', 'textarea', 5000, true, t('custom.description')) ?>
                <p class="form-hint mt-2 mb-0"><i class="fa fa-lightbulb me-1" aria-hidden="true"></i><?= e(t('custom.brief_hint')) ?></p>
            </div>
            <div class="col-12">
                <label class="form-choice-legend d-block" for="<?= e($id('attachment')) ?>"><?= e(t('custom.attachment')) ?></label>
                <input type="file" class="form-control bg-secondary border-0" name="attachment" id="<?= e($id('attachment')) ?>"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.zip" aria-describedby="<?= e($id('attachment')) ?>-hint">
                <p class="form-hint mt-2 mb-0" id="<?= e($id('attachment')) ?>-hint"><?= e(t('custom.attachment_hint')) ?></p>
            </div>
        </div>
    </section>

    <?php // 5. საკონტაქტო ინფორმაცია — კომპაქტურად ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '5')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '5')) ?>"><span class="order-step">5</span><?= e(t('custom.step_contact_title')) ?></h3>
        <div class="row g-3">
            <div class="col-12"><?= $field('name', 'text', 100, true, t('custom.name')) ?></div>
            <div class="col-md-6"><?= $field('email', 'email', 190, true, t('order.email')) ?></div>
            <div class="col-md-6" data-wizard-phone>
                <?= $field('phone', 'tel', 50, false, t('order.phone')) ?>
                <p class="form-hint mt-2 mb-0"><?= e(t('custom.phone_hint')) ?></p>
            </div>
            <div class="col-12">
                <fieldset class="form-choice">
                    <legend class="form-choice-legend"><?= e(t('custom.contact_method')) ?></legend>
                    <?= $chips('contact_method', ['email' => 'custom.method_email', 'phone' => 'custom.method_phone', 'whatsapp' => 'custom.method_whatsapp'], false) ?>
                </fieldset>
            </div>
        </div>
    </section>

    <div class="custom-nav" data-wizard-nav>
        <button type="button" class="btn btn-outline-primary border-2 px-4" data-wizard-prev><i class="fa fa-arrow-left me-2" aria-hidden="true"></i><?= e(t('custom.prev')) ?></button>
        <span class="form-hint ms-auto me-3 d-none d-sm-inline" data-wizard-counter data-label="<?= e(t('custom.step_of')) ?>"></span>
        <button type="button" class="btn btn-primary px-4" data-wizard-next><?= e(t('custom.next')) ?><i class="fa fa-arrow-right ms-2" aria-hidden="true"></i></button>
    </div>
    <div class="custom-submit" data-wizard-submit>
        <button class="btn btn-primary w-100 py-3" type="submit"><?= e($buttonLabel) ?></button>
        <p class="form-hint text-center mt-2 mb-0"><?= e(t('form.reply_time')) ?></p>
        <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
    </div>
</fieldset>
