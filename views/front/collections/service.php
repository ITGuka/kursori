<?php
/**
 * სერვისის გვერდი: აღწერა + ფასი/ვადა, რა შედის, ქეისები, ხშირი კითხვები, შეთავაზების ფორმა.
 * @var Core\View\Template $this
 * @var array $item resolve-ის ფორმატი (excerpt, body, price_from, duration, deliverables, cases, faq)
 * @var string $type
 * @var array $definition
 * @var list<array> $breadcrumbs
 */
use App\Models\Collection;
use App\Models\Language;

$this->layout('front/layouts/main');
$this->start('header');
echo $this->insert('front/blocks/page_header', ['data' => ['title' => '', 'image' => $item['header_image'] ?? null, 'breadcrumb' => true], 'page' => ['title' => $item['title']], 'breadcrumbs' => $breadcrumbs]);
$this->stop();

$deliverables = array_filter(array_map('trim', explode("\n", (string)$item['deliverables'])));
$cases = $item['cases'] ? app(Collection::class)->list('portfolio', (int)$item['cases'], 3, locale(), app(Language::class)->defaultCode()) : [];
$formId = 'service-' . $item['id'];
?>
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-7 wow fadeInUp" data-wow-delay="0.1s">
                <?php // სათაური უკვე page_header-შია (H1) — აქ მხოლოდ შესავალი ?>
                <?php if ($item['excerpt'] !== ''): ?><p class="fs-4 text-white mb-4"><?= nl2br(e($item['excerpt'])) ?></p><?php endif ?>
                <?php if ($item['body'] !== ''): ?><div class="rich-text"><?= $item['body'] ?></div><?php endif ?>
            </div>
            <div class="col-lg-5 wow fadeInUp" data-wow-delay="0.3s">
                <aside class="service-summary">
                    <?php if ($item['price_from'] !== ''): ?>
                        <p class="service-summary-label"><?= e(t('service.price')) ?></p>
                        <p class="service-summary-value"><?= e($item['price_from']) ?></p>
                    <?php endif ?>
                    <?php if ($item['duration'] !== ''): ?>
                        <p class="service-summary-label"><?= e(t('service.duration')) ?></p>
                        <p class="service-summary-value"><?= e($item['duration']) ?></p>
                    <?php endif ?>
                    <?php if ($item['show_form']): ?>
                        <a href="#form-application" class="btn btn-primary border-2 w-100 py-3"><?= e(t('service.cta')) ?></a>
                        <p class="form-hint text-center mt-2 mb-0"><?= e(t('form.reply_time')) ?></p>
                    <?php endif ?>
                </aside>
            </div>
        </div>
    </div>
</div>

<?php if ($deliverables !== []): ?>
    <div class="container-fluid py-5 bg-secondary">
        <div class="container py-5">
            <?= $this->insert('front/partials/title', ['subtitle' => t('service.included_subtitle'), 'heading' => t('service.included')]) ?>
            <ul class="service-deliverables wow fadeInUp" data-wow-delay="0.1s">
                <?php foreach ($deliverables as $line): ?>
                    <li><i class="fa fa-check text-primary me-2" aria-hidden="true"></i><?= e($line) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
<?php endif ?>

<?php if ($cases !== []): ?>
    <div class="container-fluid py-5">
        <div class="container py-5">
            <?= $this->insert('front/partials/title', ['subtitle' => t('service.cases_subtitle'), 'heading' => t('service.cases')]) ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($cases as $case): ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <a class="team-item d-block portfolio-card" href="<?= e($case['url']) ?>">
                            <div class="team-body">
                                <?= picture($case['image'], 'medium', ['class' => 'img-fluid w-100', 'alt' => media_alt($case['image']) ?: $case['title']], '(min-width: 992px) 33vw, (min-width: 768px) 50vw, 100vw') ?>
                            </div>
                            <div class="team-name flex-column">
                                <h3 class="h5 text-uppercase mb-0"><?= e($case['title']) ?></h3>
                                <?php if (($case['client'] ?? '') !== ''): ?><small class="text-primary"><?= e($case['client']) ?></small><?php endif ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if (array_filter($item['faq'], fn ($faq) => $faq['question'] !== '') !== []): ?>
    <div class="container-fluid py-5 bg-secondary">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <?= $this->insert('front/partials/title', ['subtitle' => t('service.faq_subtitle'), 'heading' => t('service.faq')]) ?>
                    <?= $this->insert('front/partials/faq', ['items' => $item['faq']]) ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($item['show_form']): ?>
    <?= $this->insert('front/blocks/application_form', [
        'data'  => ['subtitle' => t('service.form_subtitle'), 'title' => t('service.form_title'), 'text' => '<p>' . e(t('service.form_text')) . '</p>',
                    'message_label' => t('service.form_message'), 'button_label' => t('service.cta'), 'background' => 'dark'],
        'block' => ['id' => $formId],
    ]) ?>
<?php endif ?>
