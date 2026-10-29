<?php
/**
 * ინდივიდუალური შეკვეთა (order_form, პაკეტი „ინდივიდუალური“) — 5 ნაბიჯად: მომსახურება → პროექტი → ბიუჯეტი და ვადა → მასალები → კონტაქტი.
 * JS-ით (assets/front/js/order-form.js) ჩანს ერთი ნაბიჯი, JS-ის გარეშე — ყველა ერთად.
 * „პროექტისა“ და „მასალების“ ველები მომსახურებაზეა დამოკიდებული: data-wizard-for="web design" — ჩანს მხოლოდ ამ მომსახურებებზე,
 * დანარჩენზე disabled (არ იგზავნება).
 * ჯერ მხოლოდ მარკაპი: ახალ ველებს (service, site_type... contact_method) ბექენდი ჯერ არ ინახავს; name/phone/email/description — ძველებურად.
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
$service = (string)$old('service');
// მომსახურებაზე დამოკიდებული ბლოკი: სხვა მომსახურებისას (სერვერზე — როცა უკვე არჩეულია) დამალული და disabled
$for = function (string $services) use ($service): string {
    $hidden = $service !== '' && !in_array($service, explode(' ', $services), true);
    return ' data-wizard-for="' . e($services) . '"' . ($hidden ? ' hidden disabled' : '');
};

// არჩევანის ბარათები: მნიშვნელობა => [თარგმანის გასაღები, შენიშვნის ტექსტი (ან ''), Font Awesome ხატულა (ან '')]
$services = [
    'web'      => ['custom.service_web', 'Web Development', 'fa-code'],
    'design'   => ['custom.service_design', 'Web Design', 'fa-pencil-ruler'],
    'branding' => ['custom.service_branding', 'Branding & Identity', 'fa-signature'],
    'seo'      => ['custom.service_seo', 'Performance & SEO', 'fa-tachometer-alt'],
    'support'  => ['custom.service_support', 'Maintenance', 'fa-tools'],
    'other'    => ['custom.service_other', t('custom.service_other_note'), 'fa-ellipsis-h'],
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
$platforms = ['wordpress' => 'custom.platform_wordpress', 'shopify' => 'custom.platform_shopify', 'custom' => 'custom.platform_custom', 'unknown' => 'custom.platform_unknown'];
$yesNo = ['yes' => 'order.yes', 'no' => 'order.no'];
$steps = ['service', 'scope', 'budget', 'assets', 'contact'];

// ბარათები (radio / checkbox): $multiple — name[]; $required — „მინიმუმ ერთი“ (JS, data-wizard-group-required)
$cards = function (string $name, array $options, bool $multiple, bool $required, string $col) use ($id, $checked): string {
    $html = '<div' . ($required ? ' data-wizard-group-required' : '') . '><div class="row g-3">';
    foreach ($options as $value => [$label, $note, $icon]) {
        $inputId = $id($name, $value);
        $html .= '<div class="' . $col . '">'
            . '<input type="' . ($multiple ? 'checkbox' : 'radio') . '" class="btn-check" name="' . e($multiple ? $name . '[]' : $name) . '" id="' . e($inputId) . '" value="' . e($value) . '" autocomplete="off"'
            . $checked($name, $value) . '>'
            . '<label class="package-option custom-option" for="' . e($inputId) . '">'
            . ($icon !== '' ? '<i class="fa ' . e($icon) . ' custom-option-icon" aria-hidden="true"></i>' : '')
            . '<span class="custom-option-name">' . e(t($label)) . '</span>'
            . ($note !== '' ? '<span class="package-option-note">' . e($note) . '</span>' : '')
            . '<span class="package-option-check" aria-hidden="true"><i class="fa fa-check"></i></span>'
            . '</label></div>';
    }
    $html .= '</div>';
    if ($required) {
        $html .= '<div class="text-danger small mt-2 d-none" data-wizard-error>' . e(t($multiple ? 'custom.choose_one' : 'custom.choose_option')) . '</div>';
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
// კითხვა chip-ებით (legend + არჩევანი)
$question = fn (string $name, string $label, array $options, bool $multiple = false) => '<fieldset class="form-choice">'
    . '<legend class="form-choice-legend">' . e(t($label)) . '</legend>' . $chips($name, $options, $multiple) . '</fieldset>';

// ბმულები: რამდენიმე, http/www-ის გარეშეც (google.com); ცარიელი რიგი მაინც ერთია
$references = array_values(array_filter((array)$old('references'), fn ($url) => is_string($url) && $url !== ''));
$references = $references === [] ? [''] : array_slice($references, 0, 5);
// pattern-ს ბრაუზერი v ალმით კითხულობს: კლასში / და - ეკრანირებული უნდა იყოს
$urlPattern = '(https?://)?([\w\-]+\.)+[a-zA-Z]{2,}([\/?#]\S*)?';
$reference = fn (string $value, int $i) => '<div class="custom-reference" data-wizard-reference>'
    . '<input type="text" class="form-control bg-secondary border-0" name="references[]" value="' . e($value) . '" maxlength="500"'
    . ' inputmode="url" autocapitalize="off" autocorrect="off" spellcheck="false" placeholder="google.com"'
    . ' pattern="' . e($urlPattern) . '" title="' . e(t('custom.reference_invalid')) . '" aria-label="' . e(t('custom.reference_url')) . ' ' . ($i + 1) . '">'
    . '<button type="button" class="btn btn-outline-primary border-2 custom-reference-remove" data-wizard-reference-remove aria-label="' . e(t('custom.reference_remove')) . '"' . ($i === 0 ? ' hidden' : '') . '>'
    . '<i class="fa fa-times" aria-hidden="true"></i></button></div>';
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

    <?php // 1. მომსახურება — ერთი ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '1')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '1')) ?>"><span class="order-step">1</span><?= e(t('custom.services')) ?> *</h3>
        <p class="form-hint mb-3"><?= e(t('custom.services_hint')) ?></p>
        <?= $cards('service', $services, false, true, 'col-sm-6 col-xl-4') ?>
    </section>

    <?php // 2. პროექტის დეტალები — მომსახურების მიხედვით ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '2')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '2')) ?>"><span class="order-step">2</span><?= e(t('custom.step_scope_title')) ?></h3>
        <p class="form-hint mb-0" data-wizard-service-name></p>

        <fieldset class="custom-group"<?= $for('web') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_web')) ?></legend>
            <fieldset class="form-choice">
                <legend class="form-choice-legend"><?= e(t('custom.site_type')) ?></legend>
                <?= $cards('site_type', $siteTypes, false, false, 'col-sm-6 col-xl-3') ?>
            </fieldset>
            <?= $question('pages', 'custom.pages', ['1-3' => 'custom.pages_small', '4-10' => 'custom.pages_medium', '10+' => 'custom.pages_large']) ?>
            <?= $question('features', 'custom.features', [
                'cms'        => 'custom.feature_cms',
                'languages'  => 'custom.feature_languages',
                'payments'   => 'custom.feature_payments',
                'animations' => 'custom.feature_animations',
                'api'        => 'custom.feature_api',
            ], true) ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('design') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_design')) ?></legend>
            <?= $question('design_scope', 'custom.design_scope', ['new' => 'custom.design_new', 'redesign' => 'custom.design_redesign']) ?>
            <?= $question('design_platform', 'custom.design_platform', ['website' => 'custom.platform_website', 'webapp' => 'custom.platform_webapp', 'mobile' => 'custom.platform_mobile']) ?>
            <?= $question('screens', 'custom.screens', ['1-5' => 'custom.screens_small', '6-15' => 'custom.screens_medium', '15+' => 'custom.screens_large']) ?>
            <?= $question('design_deliverables', 'custom.deliverables', [
                'wireframes'    => 'custom.deliverable_wireframes',
                'prototype'     => 'custom.deliverable_prototype',
                'design_system' => 'custom.deliverable_design_system',
                'handoff'       => 'custom.deliverable_handoff',
            ], true) ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('branding') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_branding')) ?></legend>
            <?= $question('brand_stage', 'custom.brand_stage', ['new' => 'custom.brand_new', 'rebrand' => 'custom.brand_rebrand']) ?>
            <?= $question('brand_items', 'custom.brand_items', [
                'logo'       => 'custom.brand_logo',
                'identity'   => 'custom.brand_identity',
                'brandbook'  => 'custom.brand_brandbook',
                'print'      => 'custom.brand_print',
                'social'     => 'custom.brand_social',
            ], true) ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('seo') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_seo')) ?></legend>
            <?= $question('seo_goals', 'custom.seo_goals', [
                'audit'    => 'custom.seo_audit',
                'speed'    => 'custom.seo_speed',
                'keywords' => 'custom.seo_keywords',
                'local'    => 'custom.seo_local',
                'links'    => 'custom.seo_links',
            ], true) ?>
            <?= $question('seo_platform', 'custom.site_platform', $platforms) ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('support') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_support')) ?></legend>
            <?= $question('support_items', 'custom.support_items', [
                'updates' => 'custom.support_updates',
                'backups' => 'custom.support_backups',
                'content' => 'custom.support_content',
                'hosting' => 'custom.support_hosting',
                'bugs'    => 'custom.support_bugs',
            ], true) ?>
            <?= $question('support_frequency', 'custom.support_frequency', ['once' => 'custom.support_once', 'monthly' => 'custom.support_monthly']) ?>
            <?= $question('support_platform', 'custom.site_platform', $platforms) ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('other') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_other')) ?></legend>
            <?= $field('other_service', 'text', 200, true, t('custom.other_service')) ?>
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

    <?php // 4. არსებული მასალები და ბრიფი: კითხვები — მომსახურების მიხედვით, ბმულები/აღწერა/ფაილი — ყველასთვის ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '4')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '4')) ?>"><span class="order-step">4</span><?= e(t('custom.step_assets_title')) ?></h3>
        <div class="row g-4">
            <fieldset class="col-12 custom-group custom-group-row"<?= $for('web design seo') ?>>
                <legend class="visually-hidden"><?= e(t('custom.step_assets')) ?></legend>
                <div class="row g-4">
                    <fieldset class="col-md-6 custom-group"<?= $for('web design') ?>><?= $question('has_brandbook', 'custom.has_brandbook', $yesNo) ?></fieldset>
                    <fieldset class="col-md-6 custom-group"<?= $for('web seo') ?>>
                        <?= $question('content_ready', 'custom.content_ready', ['yes' => 'custom.content_yes', 'progress' => 'custom.content_progress', 'help' => 'custom.content_help']) ?>
                    </fieldset>
                    <fieldset class="col-md-6 custom-group"<?= $for('design') ?>><?= $question('has_wireframes', 'custom.has_wireframes', $yesNo) ?></fieldset>
                    <fieldset class="col-md-6 custom-group"<?= $for('seo') ?>>
                        <?= $question('has_analytics', 'custom.has_analytics', ['yes' => 'order.yes', 'no' => 'order.no', 'unknown' => 'custom.dont_know']) ?>
                    </fieldset>
                </div>
            </fieldset>
            <fieldset class="col-12 custom-group custom-group-row"<?= $for('branding') ?>>
                <legend class="visually-hidden"><?= e(t('custom.step_assets')) ?></legend>
                <div class="row g-4">
                    <div class="col-md-6"><?= $question('has_logo', 'custom.has_logo', $yesNo) ?></div>
                    <div class="col-md-6"><?= $question('has_name', 'custom.has_name', $yesNo) ?></div>
                    <div class="col-12">
                        <?= $question('brand_style', 'custom.brand_style', [
                            'minimal' => 'custom.style_minimal',
                            'classic' => 'custom.style_classic',
                            'playful' => 'custom.style_playful',
                            'premium' => 'custom.style_premium',
                            'bold'    => 'custom.style_bold',
                        ], true) ?>
                    </div>
                </div>
            </fieldset>
            <fieldset class="col-12 custom-group custom-group-row"<?= $for('support') ?>>
                <legend class="visually-hidden"><?= e(t('custom.step_assets')) ?></legend>
                <?= $question('has_access', 'custom.has_access', ['yes' => 'order.yes', 'partial' => 'custom.access_partial', 'no' => 'order.no']) ?>
            </fieldset>

            <div class="col-12">
                <?php // ბმულები: + ამატებს რიგს (მაქს. data-max), × შლის ?>
                <fieldset class="form-choice" data-wizard-references data-max="5">
                    <legend class="form-choice-legend"><?= e(t('custom.reference_url')) ?></legend>
                    <div class="d-grid gap-2" data-wizard-reference-list>
                        <?php foreach ($references as $i => $url): ?><?= $reference($url, $i) ?><?php endforeach ?>
                    </div>
                    <template data-wizard-reference-template><?= $reference('', 1) ?></template>
                    <button type="button" class="btn btn-link px-0 mt-2" data-wizard-reference-add><i class="fa fa-plus me-1" aria-hidden="true"></i><?= e(t('custom.reference_add')) ?></button>
                    <p class="form-hint mb-0"><?= e(t('custom.reference_hint')) ?></p>
                </fieldset>
            </div>
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
                <?= $question('contact_method', 'custom.contact_method', [
                    'any'      => 'custom.method_any',
                    'email'    => 'custom.method_email',
                    'phone'    => 'custom.method_phone',
                    'whatsapp' => 'custom.method_whatsapp',
                ]) ?>
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
