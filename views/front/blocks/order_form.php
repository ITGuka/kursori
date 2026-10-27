<?php
/**
 * საიტის შეკვეთის ფორმა → POST /{lang}/order → ადმინის Inbox + წერილი.
 * ბიზნესის კატეგორიის არჩევისას ჩანს ამ კატეგორიის დიზაინები (JS: assets/front/js/order-form.js);
 * შაბლონის preview — მოდალში, sandbox-იან iframe-ში.
 * @var Core\View\Template $this @var array $data @var array $block
 */
use App\Models\Design;
use App\Models\Language;
use Core\Support\Session;

$form = 'order';
$uid = $block['id'];
$default = app(Language::class)->defaultCode();
$designs = app(Design::class)->published(locale(), $default);
// ცარიელი კატეგორიები (დიზაინების გარეშე) არ ჩანს; „სხვა“ — ყოველთვის
$categories = app(Design::class)->categoryOptions(locale(), $default, $designs);

$isThis = Session::get('_form') === $form;
$old = fn (string $name) => $isThis ? (string)old($name, '') : '';
$error = fn (string $name) => $isThis ? errors($name) : null;
$field = fn (string $name, string $type, int $max, bool $required = true) => $this->insert('front/partials/form-field',
    ['form' => $form, 'name' => $name, 'label' => t("order.$name"), 'type' => $type, 'required' => $required, 'max' => $max, 'uid' => $uid]);
$selectedCategory = $old('category');
$selectedDesign = $old('design');
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php if ($data['text'] !== ''): ?>
            <div class="rich-text text-center fs-5 mb-5 wow fadeInUp" data-wow-delay="0.2s"><?= $data['text'] ?></div>
        <?php endif ?>
        <form method="post" action="<?= e(route('order.submit', ['lang' => locale()])) ?>" id="form-order" novalidate class="order-form" data-order-form>
            <?= csrf_field() ?>
            <?= $this->insert('front/partials/form-status', ['form' => $form]) ?>
            <div class="row g-3">
                <div class="col-12"><?= $field('name', 'text', 100) ?></div>
                <div class="col-md-6"><?= $field('phone', 'tel', 50) ?></div>
                <div class="col-md-6"><?= $field('email', 'email', 190) ?></div>

                <div class="col-12">
                    <?php // ბიზნესის კატეგორია — ღილაკებად (radio): არჩევისას დიზაინები იფილტრება ?>
                    <fieldset class="form-choice order-categories"<?= $error('category') ? ' aria-describedby="order-category-error-' . e($uid) . '"' : '' ?>>
                        <legend class="form-choice-legend"><?= e(t('order.category')) ?></legend>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ([['id' => '', 'name' => t('order.all_designs'), 'ids' => []], ...$categories] as $category): ?>
                                <?php $chipId = "order-category-$uid-" . ($category['id'] === '' ? 'all' : $category['id']); ?>
                                <input type="radio" class="btn-check" name="category" id="<?= e($chipId) ?>" value="<?= e($category['id']) ?>" autocomplete="off"
                                       data-order-category data-ids="<?= e(json_encode($category['ids'])) ?>"<?= $selectedCategory === (string)$category['id'] ? ' checked' : '' ?>>
                                <label class="btn btn-outline-primary btn-sm border-2 px-3" for="<?= e($chipId) ?>"><?= e($category['name']) ?></label>
                            <?php endforeach ?>
                        </div>
                        <?php if ($error('category')): ?><div class="text-danger small mt-2" id="order-category-error-<?= e($uid) ?>"><?= e($error('category')) ?></div><?php endif ?>
                    </fieldset>
                </div>

                <div class="col-12">
                    <fieldset class="order-designs"<?= $error('design') ? ' aria-describedby="order-design-error-' . e($uid) . '"' : '' ?>>
                        <legend class="form-choice-legend"><?= e(t('order.design')) ?> *</legend>
                        <?php if ($designs === []): ?>
                            <p class="form-hint mb-0"><?= e(t('order.no_designs')) ?></p>
                        <?php else: ?>
                            <p class="form-hint mb-3"><i class="fa fa-hand-pointer me-1" aria-hidden="true"></i><?= e(t('order.design_hint')) ?></p>
                            <?php // ბარათზე დაკლიკება → preview მოდალი; არჩევა — მოდალის ღილაკით (რადიო მოინიშნება) ?>
                            <div class="row g-3" data-order-designs>
                                <?php foreach ($designs as $design): ?>
                                    <?php $inputId = "order-design-$uid-{$design['id']}"; ?>
                                    <div class="col-sm-6 col-lg-4" data-order-design data-categories="<?= e(json_encode($design['categories'])) ?>">
                                        <div class="design-card">
                                            <input type="radio" class="btn-check" name="design" id="<?= e($inputId) ?>" value="<?= e($design['id']) ?>" autocomplete="off" tabindex="-1"
                                                   data-title="<?= e($design['title']) ?>"<?= $selectedDesign === (string)$design['id'] ? ' checked' : '' ?>>
                                            <button type="button" class="design-card-select" data-order-preview
                                                    data-url="<?= e($design['preview']) ?>" data-title="<?= e($design['title']) ?>" data-input="<?= e($inputId) ?>">
                                                <span class="design-card-image">
                                                    <?php if ($design['image'] !== null): ?>
                                                        <?= picture($design['image'], 'small', ['alt' => $design['title']], '(min-width: 992px) 33vw, (min-width: 576px) 50vw, 100vw') ?>
                                                    <?php else: ?>
                                                        <i class="fa fa-palette" aria-hidden="true"></i>
                                                    <?php endif ?>
                                                    <span class="design-card-hint"><i class="fa fa-eye me-2" aria-hidden="true"></i><?= e(t('order.click_to_view')) ?></span>
                                                </span>
                                                <span class="design-card-title"><?= e($design['title']) ?></span>
                                                <span class="design-card-check" aria-hidden="true"><i class="fa fa-check"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <p class="form-hint mt-3 mb-0 d-none" data-order-empty><?= e(t('order.no_designs_category')) ?></p>
                            <?php // „მეტის ჩვენება“: თავიდან 6, ყოველ დაჭერაზე +6 (JS); data-label-ში :count — დარჩენილი ?>
                            <div class="order-more text-center mt-4 d-none" data-order-more data-per-page="6">
                                <button type="button" class="btn btn-outline-primary border-2 px-4" data-order-more-button data-label="<?= e(t('order.show_more')) ?>"></button>
                                <p class="form-hint mt-2 mb-0" data-order-more-count data-label="<?= e(t('order.shown_of')) ?>"></p>
                            </div>
                            <p class="order-selected mt-3 mb-0<?= $selectedDesign === '' ? ' d-none' : '' ?>" data-order-selected>
                                <i class="fa fa-check-circle text-primary me-1" aria-hidden="true"></i><?= e(t('order.selected')) ?>: <strong data-order-selected-title></strong>
                            </p>
                        <?php endif ?>
                        <?php if ($error('design')): ?><div class="text-danger small mt-2" id="order-design-error-<?= e($uid) ?>"><?= e($error('design')) ?></div><?php endif ?>
                    </fieldset>
                </div>

                <div class="col-12">
                    <?= $field('description', 'textarea', 5000) ?>
                    <p class="form-hint mt-2 mb-0"><i class="fa fa-lightbulb me-1" aria-hidden="true"></i><?= e(t('order.description_hint')) ?></p>
                </div>

                <div class="col-12">
                    <fieldset class="form-choice">
                        <legend class="form-choice-legend"><?= e(t('order.extras')) ?></legend>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (['has_domain', 'has_hosting'] as $name): ?>
                                <input type="checkbox" class="btn-check" name="<?= e($name) ?>" id="<?= e("order-$name-$uid") ?>" value="yes" autocomplete="off"<?= $old($name) === 'yes' ? ' checked' : '' ?>>
                                <label class="btn btn-outline-primary btn-sm border-2 px-3" for="<?= e("order-$name-$uid") ?>"><i class="fa fa-check me-1 order-extra-check" aria-hidden="true"></i><?= e(t("order.$name")) ?></label>
                            <?php endforeach ?>
                        </div>
                    </fieldset>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary w-100 py-3" type="submit"><?= e($data['button_label'] !== '' ? $data['button_label'] : t('order.submit')) ?></button>
                    <p class="form-hint text-center mt-2 mb-0"><?= e(t('form.reply_time')) ?></p>
                    <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php // შაბლონის preview: sandbox — შაბლონის JS მუშაობს, მაგრამ ჩვენი საიტის origin-ზე (cookie, DOM) წვდომა არ აქვს ?>
<div class="modal fade design-preview" id="design-preview-<?= e($uid) ?>" tabindex="-1" aria-labelledby="design-preview-title-<?= e($uid) ?>" aria-hidden="true" data-order-modal>
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary flex-wrap gap-2">
                <h2 class="modal-title h5 mb-0 me-auto" id="design-preview-title-<?= e($uid) ?>" data-order-modal-title></h2>
                <div class="btn-group d-none d-md-inline-flex" role="group" aria-label="<?= e(t('order.device')) ?>">
                    <button type="button" class="btn btn-sm btn-outline-primary active" data-order-device="100%" aria-pressed="true"><i class="fa fa-desktop" aria-hidden="true"></i><span class="visually-hidden"><?= e(t('order.desktop')) ?></span></button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-order-device="768px" aria-pressed="false"><i class="fa fa-tablet-alt" aria-hidden="true"></i><span class="visually-hidden"><?= e(t('order.tablet')) ?></span></button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-order-device="390px" aria-pressed="false"><i class="fa fa-mobile-alt" aria-hidden="true"></i><span class="visually-hidden"><?= e(t('order.mobile')) ?></span></button>
                </div>
                <button type="button" class="btn btn-sm btn-primary" data-order-choose><i class="fa fa-check me-1" aria-hidden="true"></i><?= e(t('order.choose')) ?></button>
                <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="<?= e(t('order.close')) ?>"></button>
            </div>
            <div class="modal-body p-0 d-flex justify-content-center">
                <iframe class="design-preview-frame" title="<?= e(t('order.preview')) ?>" sandbox="allow-scripts allow-forms allow-popups allow-modals" referrerpolicy="no-referrer" data-order-frame></iframe>
            </div>
        </div>
    </div>
</div>
<script src="<?= asset('front/js/order-form.js') ?>" defer></script>
