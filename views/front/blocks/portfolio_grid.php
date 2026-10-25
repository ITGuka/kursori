<?php
/**
 * პორტფოლიოს ბადე (კოლექცია „პორტფოლიო“) კატეგორიების ფილტრით. ბარათი — Poseify-ის team-item სტილით.
 * @var Core\View\Template $this @var array $data @var array $block
 */
$items = array_values(array_filter($data['items'], fn ($item) => $item['title'] !== ''));
if ($items === []) {
    return;
}
$categories = [];
foreach ($items as $item) {
    if ($item['category_id'] !== null && $item['category'] !== '') {
        $categories[$item['category_id']] = $item['category'];
    }
}
$gridId = 'portfolio-' . $block['id'];
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <?php if ($data['show_filter'] && count($categories) > 1): ?>
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-5 wow fadeInUp" data-wow-delay="0.2s" data-portfolio-filter="<?= e($gridId) ?>" role="group">
                <button type="button" class="btn btn-primary border-2 px-4" data-filter="*"><?= e(t('portfolio.all')) ?></button>
                <?php foreach ($categories as $id => $name): ?>
                    <button type="button" class="btn btn-outline-primary border-2 px-4" data-filter="<?= e($id) ?>"><?= e($name) ?></button>
                <?php endforeach ?>
            </div>
        <?php endif ?>
        <div class="row g-4" id="<?= e($gridId) ?>">
            <?php foreach ($items as $i => $item): ?>
                <?php $image = media_url($item['image'], 'medium'); ?>
                <div class="col-lg-4 col-md-6 wow fadeInUp portfolio-item" data-wow-delay="<?= e(0.1 + ($i % 3) * 0.2) ?>s" data-category="<?= e($item['category_id'] ?? '') ?>">
                    <a class="team-item d-block portfolio-card"<?= link_attrs($item['link']) ?>>
                        <div class="team-body">
                            <?php if ($image !== ''): ?>
                                <?= picture($item['image'], 'medium', ['class' => 'img-fluid w-100', 'alt' => media_alt($item['image']) ?: $item['title']], '(min-width: 992px) 33vw, (min-width: 768px) 50vw, 100vw') ?>
                            <?php endif ?>
                        </div>
                        <div class="team-name flex-column">
                            <h5 class="text-uppercase mb-0"><?= e($item['title']) ?></h5>
                            <?php if ($item['category'] !== ''): ?><small class="text-primary"><?= e($item['category']) ?></small><?php endif ?>
                        </div>
                    </a>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
<?php if ($data['show_filter'] && count($categories) > 1): ?>
    <?php // inline: ბლოკი ცალკე იხატება, layout-ის scripts სექციამდე ვერ აღწევს; vanilla JS — jQuery-ს არ ელოდება ?>
    <script>
        // კატეგორიის ფილტრი გვერდის გადატვირთვის გარეშე
        document.querySelectorAll('[data-portfolio-filter="<?= e($gridId) ?>"] [data-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                var filter = button.dataset.filter;
                button.parentNode.querySelectorAll('[data-filter]').forEach(function (b) {
                    b.classList.toggle('btn-primary', b === button);
                    b.classList.toggle('btn-outline-primary', b !== button);
                });
                document.querySelectorAll('#<?= e($gridId) ?> .portfolio-item').forEach(function (item) {
                    item.classList.toggle('d-none', filter !== '*' && item.dataset.category !== filter);
                });
            });
        });
    </script>
<?php endif ?>
