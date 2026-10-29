<?php
/**
 * ინდივიდუალური შეკვეთა → POST /{lang}/forms/custom → ადმინის Inbox. დროებითი ფორმა (მოგვიანებით შეიცვლება — მისამართი იგივე რჩება).
 * პაკეტი — არასავალდებულო; ?package=კოდი (შეკვეთის ფორმიდან, „ვერ იპოვეთ შესაფერისი?“) წინასწარ მოინიშნება.
 * Inbox-ში ინახება პაკეტის სახელი ძირითად ენაზე.
 * @var Core\View\Template $this @var array $data @var array $block
 */
use App\Models\Language;
use App\Models\Package;
use Core\Support\Session;

$form = 'custom';
$uid = $block['id'];
$default = app(Language::class)->defaultCode();
$model = app(Package::class);
$names = array_column($model->published($default, $default), 'name', 'code');
$packages = array_filter($model->published(locale(), $default), fn (array $package) => isset($names[$package['code']]));

$isThis = Session::get('_form') === $form;
$query = app()->request()?->query('package', '');
$selected = $isThis ? (string)old('package', '') : (is_string($query) ? ($names[$query] ?? '') : '');
$error = $isThis ? errors('package') : null;
$field = fn (string $name, string $label, string $type, int $max, bool $required = true) => $this->insert('front/partials/form-field',
    ['form' => $form, 'name' => $name, 'label' => $label, 'type' => $type, 'required' => $required, 'max' => $max, 'uid' => $uid]);
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-8">
                <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
                <?php if ($data['text'] !== ''): ?>
                    <div class="rich-text text-center fs-5 mb-5 wow fadeInUp" data-wow-delay="0.2s"><?= $data['text'] ?></div>
                <?php endif ?>
                <form method="post" action="<?= e(route('form.submit', ['lang' => locale(), 'form' => $form])) ?>" id="form-custom" novalidate class="order-form">
                    <?= csrf_field() ?>
                    <?= $this->insert('front/partials/form-status', ['form' => $form]) ?>
                    <div class="row g-3">
                        <div class="col-12"><?= $field('name', t('order.name'), 'text', 100) ?></div>
                        <div class="col-md-6"><?= $field('phone', t('order.phone'), 'tel', 50) ?></div>
                        <div class="col-md-6"><?= $field('email', t('order.email'), 'email', 190) ?></div>
                        <?php if ($packages !== []): ?>
                            <div class="col-12">
                                <fieldset class="form-choice">
                                    <legend class="form-choice-legend"><?= e(t('custom.package')) ?></legend>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ([['code' => 'none', 'name' => t('custom.package_none'), 'value' => ''], ...$packages] as $package): ?>
                                            <?php $value = $package['value'] ?? $names[$package['code']]; $id = "custom-package-$uid-{$package['code']}"; ?>
                                            <input type="radio" class="btn-check" name="package" id="<?= e($id) ?>" value="<?= e($value) ?>" autocomplete="off"<?= $selected === $value ? ' checked' : '' ?>>
                                            <label class="btn btn-outline-primary btn-sm border-2 px-3" for="<?= e($id) ?>"><?= e($package['name']) ?></label>
                                        <?php endforeach ?>
                                    </div>
                                    <?php if ($error): ?><div class="text-danger small mt-2"><?= e($error) ?></div><?php endif ?>
                                </fieldset>
                            </div>
                        <?php endif ?>
                        <div class="col-12">
                            <?= $field('description', t('custom.description'), 'textarea', 5000) ?>
                            <p class="form-hint mt-2 mb-0"><i class="fa fa-lightbulb me-1" aria-hidden="true"></i><?= e(t('custom.description_hint')) ?></p>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit"><?= e($data['button_label'] !== '' ? $data['button_label'] : t('custom.submit')) ?></button>
                            <p class="form-hint text-center mt-2 mb-0"><?= e(t('form.reply_time')) ?></p>
                            <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
