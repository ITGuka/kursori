<?php
/**
 * ინდივიდუალური შეკვეთა (order_form, პაკეტი „ინდივიდუალური“) — 5 ნაბიჯად: მომსახურება → პროექტი → ბიუჯეტი და ვადა → მასალები → კონტაქტი.
 * JS-ით (assets/front/js/order-form.js) ჩანს ერთი ნაბიჯი, JS-ის გარეშე — ყველა ერთად.
 * ველები, არჩევანი, პროექტზე დამოკიდებულება (for) და ბიუჯეტის/ვადის პროფილები — config/forms.php → custom (App\Services\CustomOrder),
 * იგივე, რითაც სერვერი ამოწმებს. data-wizard-for="web:shop design" — ჩანს, თუ ემთხვევა ერთ-ერთი ნიშანი: მომსახურება (design)
 * ან მომსახურება:ქვეტიპი (web:shop; ქვეტიპი — data-wizard-key ველი, არ არის არჩეული → „-“). დანარჩენი დამალული და disabled.
 * @var Core\View\Template $this
 * @var string $form
 * @var string $uid
 * @var bool $disabled true — სხვა პაკეტია არჩეული (fieldset disabled: ველები არ იგზავნება)
 * @var string $buttonLabel
 */
use App\Services\CustomOrder;
use Core\Support\Session;

$definition = CustomOrder::definition();
$fields = $definition['fields'];
$isThis = Session::get('_form') === $form;
$old = fn (string $name) => $isThis ? old($name, '') : '';
$error = fn (string $name) => $isThis ? errors($name) : null;
$checked = function (string $name, string $value) use ($old): string {
    $current = $old($name);
    return (is_array($current) ? in_array($value, $current, true) : $current === $value) ? ' checked' : '';
};
$id = fn (string $name, string $value = '') => "custom-$name-$uid" . ($value !== '' ? '-' . preg_replace('/[^\w-]/', '-', $value) : '');
$field = fn (string $name, string $type, int $max, bool $required) => $this->insert('front/partials/form-field',
    ['form' => $form, 'name' => $name, 'label' => t($fields[$name]['label']), 'type' => $type, 'required' => $required, 'max' => $max, 'uid' => "$uid-custom"]);
// სერვერის შეცდომა (ვალიდაციის შემდეგ); data-wizard-server-error — JS ხსნის პირველ ნაბიჯს, რომელშიც შეცდომაა
$errorText = fn (string $name) => ($message = $error($name)) ? '<div class="text-danger small mt-2" id="' . e($id($name)) . '-error" data-wizard-server-error>' . e($message) . '</div>' : '';

// პროექტის ნიშნები წინა მნიშვნელობებიდან (ვალიდაციის შეცდომის შემდეგ)
$tokens = CustomOrder::tokens(array_map(fn ($key) => (string)$old($key), ['service' => 'service'] + array_combine($definition['sub_keys'], $definition['sub_keys'])));
$service = $tokens[0];
// სერვერზე — შეუსაბამო ბლოკი დამალული და disabled; მომსახურება არ არის — ყველა ჩანს (JS-ის გარეშე)
$for = function (string $list) use ($service, $tokens): string {
    $hidden = $service !== '' && !CustomOrder::matches($list, $tokens);
    return ' data-wizard-for="' . e($list) . '"' . ($hidden ? ' hidden disabled' : '');
};
$forField = fn (string $name) => $for($fields[$name]['for']);
// ქვეტიპის ველი → data-wizard-key="მომსახურება"
$subKey = fn (string $name) => ($owner = array_search($name, $definition['sub_keys'], true)) !== false ? ' data-wizard-key="' . e($owner) . '"' : '';

// ბარათის არჩევანი: config-ის options + შენიშვნა და ხატულა (მხოლოდ ფორმაში) → [წარწერა, შენიშვნა, ხატულა]
$withIcons = fn (string $name, array $extra) => array_map(
    fn ($key, $value) => [t($key), $extra[$value][0] ?? '', $extra[$value][1] ?? ''],
    $fields[$name]['options'], array_keys($fields[$name]['options'])
);
$services = array_combine(array_keys($fields['service']['options']), $withIcons('service', [
    'web'      => ['Web Development', 'fa-code'],
    'design'   => ['Web Design', 'fa-pencil-ruler'],
    'branding' => ['Branding & Identity', 'fa-signature'],
    'seo'      => ['Performance & SEO', 'fa-tachometer-alt'],
    'support'  => ['Maintenance', 'fa-tools'],
    'other'    => [t('custom.service_other_note'), 'fa-ellipsis-h'],
]));
$siteTypes = array_combine(array_keys($fields['site_type']['options']), $withIcons('site_type', [
    'landing' => ['', 'fa-file'], 'corporate' => ['', 'fa-building'], 'shop' => ['', 'fa-shopping-cart'], 'app' => ['', 'fa-cogs'],
]));
$timelineIcons = ['fast' => 'fa-bolt', 'asap' => 'fa-bolt', 'standard' => 'fa-calendar-alt', 'month' => 'fa-calendar-alt', 'flexible' => 'fa-mug-hot'];
$steps = array_keys($definition['steps']);

// ბარათები (radio / checkbox): $required — „მინიმუმ ერთი“ (JS, data-wizard-group-required);
// $idKey — id-ის განმასხვავებელი, როცა ერთი name რამდენიმე ჯგუფშია (ბიუჯეტი პროფილების მიხედვით)
$cards = function (string $name, array $options, bool $required, string $col, string $idKey = '', bool $restore = true) use ($id, $checked, $fields, $errorText, $subKey): string {
    $multiple = !empty($fields[$name]['multiple']);
    $html = '<div' . ($required ? ' data-wizard-group-required' : '') . '><div class="row g-3">';
    foreach ($options as $value => [$label, $note, $icon]) {
        $inputId = $id($name, ($idKey !== '' ? "$idKey-" : '') . $value);
        $html .= '<div class="' . $col . '">'
            . '<input type="' . ($multiple ? 'checkbox' : 'radio') . '" class="btn-check" name="' . e($multiple ? $name . '[]' : $name) . '" id="' . e($inputId) . '" value="' . e($value) . '" autocomplete="off"'
            . $subKey($name) . ($restore ? $checked($name, (string)$value) : '') . '>'
            . '<label class="package-option custom-option" for="' . e($inputId) . '">'
            . ($icon !== '' ? '<i class="fa ' . e($icon) . ' custom-option-icon" aria-hidden="true"></i>' : '')
            . '<span class="custom-option-name">' . e($label) . '</span>'
            . ($note !== '' ? '<span class="package-option-note">' . e($note) . '</span>' : '')
            . '<span class="package-option-check" aria-hidden="true"><i class="fa fa-check"></i></span>'
            . '</label></div>';
    }
    $html .= '</div>';
    if ($required) {
        $html .= '<div class="text-danger small mt-2 d-none" data-wizard-error>' . e(t($multiple ? 'custom.choose_one' : 'custom.choose_option')) . '</div>';
    }
    return $html . ($restore ? $errorText($name) : '') . '</div>';
};
// კითხვა chip-ებით (legend + config-ის options)
$question = function (string $name) use ($id, $checked, $fields, $errorText, $subKey): string {
    $field = $fields[$name];
    $multiple = !empty($field['multiple']);
    $html = '<fieldset class="form-choice"><legend class="form-choice-legend">' . e(t($field['label'])) . '</legend><div class="d-flex flex-wrap gap-2">';
    foreach ($field['options'] as $value => $label) {
        $inputId = $id($name, (string)$value);
        $html .= '<input type="' . ($multiple ? 'checkbox' : 'radio') . '" class="btn-check" name="' . e($multiple ? $name . '[]' : $name) . '" id="' . e($inputId) . '" value="' . e($value) . '" autocomplete="off"' . $subKey($name) . $checked($name, (string)$value) . '>'
            . '<label class="btn btn-outline-primary btn-sm border-2 px-3" for="' . e($inputId) . '">'
            . ($multiple ? '<i class="fa fa-check me-1 order-extra-check" aria-hidden="true"></i>' : '') . e(t($label)) . '</label>';
    }
    return $html . '</div>' . $errorText($name) . '</fieldset>';
};
// ტექსტი პროექტის მიხედვით: ნიშნები => თარგმანის გასაღები (ჩანს ერთი)
$variants = function (array $texts) use ($for): string {
    $html = '';
    foreach ($texts as $list => $key) {
        $html .= '<span' . $for($list) . '>' . e(t($key)) . '</span>';
    }
    return $html;
};

// ბმულები: რამდენიმე, http/www-ის გარეშეც (google.com); ცარიელი რიგი მაინც ერთია
$linkField = $fields['references'];
$references = array_values(array_filter((array)$old('references'), fn ($url) => is_string($url) && $url !== ''));
$references = $references === [] ? [''] : array_slice($references, 0, (int)$linkField['max']);
// pattern-ს ბრაუზერი v ალმით კითხულობს: კლასში / და - ეკრანირებული უნდა იყოს (სერვერზე — CustomOrder::URL_PATTERN)
$urlPattern = '(https?://)?([\w\-]+\.)+[a-zA-Z]{2,}([\/?#]\S*)?';
$reference = fn (string $value, int $i) => '<div class="custom-reference" data-wizard-reference>'
    . '<input type="text" class="form-control bg-secondary border-0" name="references[]" value="' . e($value) . '" maxlength="500"'
    . ' inputmode="url" autocapitalize="off" autocorrect="off" spellcheck="false" placeholder="google.com"'
    . ' pattern="' . e($urlPattern) . '" title="' . e(t('custom.reference_invalid')) . '" aria-label="' . e(t('custom.reference_url')) . ' ' . ($i + 1) . '">'
    . '<button type="button" class="btn btn-outline-primary border-2 custom-reference-remove" data-wizard-reference-remove aria-label="' . e(t('custom.reference_remove')) . '"' . ($i === 0 ? ' hidden' : '') . '>'
    . '<i class="fa fa-times" aria-hidden="true"></i></button></div>';
$maxMb = round($definition['attachment']['max_size'] / 1048576);
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
        <?= $cards('service', $services, true, 'col-sm-6 col-xl-4') ?>
    </section>

    <?php // 2. პროექტის დეტალები — მომსახურების მიხედვით; data-wizard-key — ქვეტიპი, რომელზეც ნაბიჯები 3–4 იცვლება ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '2')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '2')) ?>"><span class="order-step">2</span><?= e(t('custom.step_scope_title')) ?></h3>
        <p class="form-hint mb-0" data-wizard-service-name></p>

        <fieldset class="custom-group"<?= $for('web') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_web')) ?></legend>
            <fieldset class="form-choice">
                <legend class="form-choice-legend"><?= e(t($fields['site_type']['label'])) ?></legend>
                <?= $cards('site_type', $siteTypes, false, 'col-sm-6 col-xl-3') ?>
            </fieldset>
            <?= $question('pages') ?>
            <?= $question('features') ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('design') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_design')) ?></legend>
            <?= $question('design_scope') ?>
            <?= $question('design_platform') ?>
            <?= $question('screens') ?>
            <?= $question('design_deliverables') ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('branding') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_branding')) ?></legend>
            <?= $question('brand_stage') ?>
            <?= $question('brand_items') ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('seo') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_seo')) ?></legend>
            <?= $question('seo_goals') ?>
            <?= $question('seo_platform') ?>
        </fieldset>

        <fieldset class="custom-group"<?= $for('support') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_support')) ?></legend>
            <?= $question('support_items') ?>
            <?= $question('support_frequency') ?>
            <?= $question('support_platform') ?>
        </fieldset>

        <fieldset class="custom-group"<?= $forField('other_service') ?>>
            <legend class="visually-hidden"><?= e(t('custom.service_other')) ?></legend>
            <?= $field('other_service', 'text', 200, true) ?>
        </fieldset>
    </section>

    <?php // 3. ბიუჯეტი და ვადა — პროექტის მიხედვით (profiles): ჩანს ერთი ჯგუფი, დანარჩენი disabled ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '3')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '3')) ?>"><span class="order-step">3</span><?= e(t('custom.step_budget')) ?></h3>
        <p class="form-hint mb-0" data-wizard-project-name></p>
        <?php foreach ($definition['profiles'] as $list => $profile): ?>
            <?php
            $key = str_replace([' ', ':'], '_', $list);
            // ერთი name რამდენიმე ჯგუფშია: წინა არჩევანი და შეცდომა — მხოლოდ აქტიურ ჯგუფში (თორემ ბოლო ჯგუფი „წაართმევს“)
            $active = $service !== '' && CustomOrder::matches($list, $tokens);
            $budgets = array_map(fn ($option) => [$option[0], $option[1], ''], CustomOrder::budgetOptions($profile));
            $timelines = [];
            foreach (CustomOrder::timelineOptions($profile) as $value => [$label, $note]) {
                $timelines[$value] = [$label, $note, $timelineIcons[$value] ?? ''];
            }
            ?>
            <fieldset class="custom-group"<?= $for($list) ?>>
                <legend class="visually-hidden"><?= e(t('custom.step_budget')) ?></legend>
                <fieldset class="form-choice">
                    <legend class="form-choice-legend"><?= e(t($profile['monthly'] ? 'custom.budget_monthly' : 'custom.budget')) ?> *</legend>
                    <?= $cards('budget', $budgets, true, 'col-sm-6 col-xl-3', $key, $active) ?>
                </fieldset>
                <fieldset class="form-choice">
                    <legend class="form-choice-legend"><?= e(t($profile['timeline'] === 'start' ? 'custom.start' : 'custom.timeline')) ?> *</legend>
                    <?= $cards('timeline', $timelines, true, 'col-sm-6 col-xl-4', $key, $active) ?>
                </fieldset>
            </fieldset>
        <?php endforeach ?>
    </section>

    <?php // 4. არსებული მასალები და ბრიფი — კითხვები, ბმულის წარწერა და მინიშნებები პროექტის მიხედვით ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '4')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '4')) ?>"><span class="order-step">4</span><?= e(t('custom.step_assets_title')) ?></h3>
        <div class="row g-4 mt-0">
            <?php foreach (['has_brandbook', 'content_ready', 'has_photos', 'has_catalog', 'products', 'has_spec', 'has_wireframes', 'has_analytics_data', 'has_analytics', 'has_logo', 'has_name'] as $name): ?>
                <fieldset class="col-md-6 custom-group"<?= $forField($name) ?>><?= $question($name) ?></fieldset>
            <?php endforeach ?>
            <fieldset class="col-12 custom-group"<?= $forField('brand_style') ?>><?= $question('brand_style') ?></fieldset>
            <fieldset class="col-md-6 custom-group"<?= $forField('has_access') ?>><?= $question('has_access') ?></fieldset>

            <div class="col-12">
                <?php // ბმულები: + ამატებს რიგს (მაქს. data-max), × შლის; data-required-for — პირველი ბმული სავალდებულოა ?>
                <fieldset class="form-choice" data-wizard-references data-max="<?= (int)$linkField['max'] ?>" data-required-for="<?= e($linkField['required_for']) ?>">
                    <legend class="form-choice-legend">
                        <?= $variants([
                            'web other'           => 'custom.reference_url',
                            'design:- design:new' => 'custom.reference_design',
                            'design:redesign'     => 'custom.reference_redesign',
                            'branding'            => 'custom.reference_branding',
                            'seo support'         => 'custom.reference_site',
                        ]) ?><span data-wizard-required-mark<?= $service !== '' && CustomOrder::matches($linkField['required_for'], $tokens) ? '' : ' hidden' ?>> *</span>
                    </legend>
                    <div class="d-grid gap-2" data-wizard-reference-list>
                        <?php foreach ($references as $i => $url): ?><?= $reference($url, $i) ?><?php endforeach ?>
                    </div>
                    <template data-wizard-reference-template><?= $reference('', 1) ?></template>
                    <button type="button" class="btn btn-link px-0 mt-2" data-wizard-reference-add><i class="fa fa-plus me-1" aria-hidden="true"></i><?= e(t('custom.reference_add')) ?></button>
                    <p class="form-hint mb-0"><?= e(t('custom.reference_hint')) ?></p>
                    <?= $errorText('references') ?>
                </fieldset>
            </div>
            <div class="col-12">
                <?= $field('description', 'textarea', 5000, true) ?>
                <p class="form-hint mt-2 mb-0"><i class="fa fa-lightbulb me-1" aria-hidden="true"></i><?= $variants([
                    'web'      => 'custom.brief_web',
                    'design'   => 'custom.brief_design',
                    'branding' => 'custom.brief_branding',
                    'seo'      => 'custom.brief_seo',
                    'support'  => 'custom.brief_support',
                    'other'    => 'custom.brief_other',
                ]) ?></p>
            </div>
            <div class="col-12">
                <label class="form-choice-legend d-block" for="<?= e($id('attachment')) ?>"><?= e(t('custom.attachment')) ?></label>
                <input type="file" class="form-control bg-secondary border-0<?= $error('attachment') ? ' is-invalid' : '' ?>" name="attachment" id="<?= e($id('attachment')) ?>"
                       accept=".<?= e(implode(',.', array_unique([...array_values($definition['attachment']['allowed']), 'jpeg']))) ?>"
                       data-max-size="<?= (int)$definition['attachment']['max_size'] ?>" data-too-large="<?= e(t('custom.upload_too_large', ['max' => $maxMb])) ?>"
                       aria-describedby="<?= e($id('attachment')) ?>-hint">
                <p class="form-hint mt-2 mb-0" id="<?= e($id('attachment')) ?>-hint"><?= $variants([
                    'web'      => 'custom.attachment_web',
                    'design'   => 'custom.attachment_design',
                    'branding' => 'custom.attachment_branding',
                    'seo'      => 'custom.attachment_seo',
                    'support'  => 'custom.attachment_support',
                    'other'    => 'custom.attachment_other',
                ]) ?> <?= e(t('custom.attachment_formats', ['max' => $maxMb])) ?></p>
                <?php // ფაილი redirect-ს ვერ გადაურჩება — შეცდომის შემდეგ თავიდან უნდა აირჩიონ ?>
                <?php if ($isThis && errors() !== [] && !$error('attachment')): ?><p class="form-hint text-warning mt-1 mb-0"><?= e(t('custom.upload_again')) ?></p><?php endif ?>
                <?= $errorText('attachment') ?>
            </div>
        </div>
    </section>

    <?php // 5. საკონტაქტო ინფორმაცია — კომპაქტურად ?>
    <section class="custom-panel" data-wizard-panel aria-labelledby="<?= e($id('step', '5')) ?>">
        <h3 class="custom-panel-title h5" id="<?= e($id('step', '5')) ?>"><span class="order-step">5</span><?= e(t('custom.step_contact_title')) ?></h3>
        <div class="row g-3">
            <div class="col-12"><?= $field('name', 'text', 100, true) ?></div>
            <div class="col-md-6"><?= $field('email', 'email', 190, true) ?></div>
            <div class="col-md-6" data-wizard-phone>
                <?= $field('phone', 'tel', 50, in_array((string)$old('contact_method'), $fields['phone']['required_if']['contact_method'], true)) ?>
                <p class="form-hint mt-2 mb-0"><?= e(t('custom.phone_hint')) ?></p>
            </div>
            <div class="col-12"><?= $question('contact_method') ?></div>
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
