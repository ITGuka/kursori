<?php
/**
 * კოლექციის ჩანაწერის გვერდი (Poseify-ის სტილში). საკუთარი შაბლონი: config/collections.php → view.
 * @var Core\View\Template $this
 * @var array $item resolve-ის ფორმატი (title, image, category, url + config-ის ველები)
 * @var array $definition
 */
$this->layout('front/layouts/main');
$this->start('header');
echo $this->insert('front/blocks/page_header', ['data' => ['title' => '', 'image' => null, 'breadcrumb' => true], 'page' => ['title' => $item['title']]]);
$this->stop();

$image = media_url($item['image'], 'large');
// დამატებითი მოკლე ველები (ტექსტი/რიცხვი/ბმული) — მონაცემების ცხრილში; გრძელი ტექსტები — ქვემოთ
$facts = [];
foreach ($definition['fields'] as $name => $field) {
    $value = $item[$name] ?? null;
    if (in_array($field['type'], ['text', 'number'], true) && $value !== '' && $value !== null) {
        $facts[] = [$field['label'], e((string)$value)];
    } elseif ($field['type'] === 'link' && $value) {
        $facts[] = [$field['label'], '<a' . link_attrs($value) . '>' . e(preg_replace('~^https?://(www\.)?~', '', $value['href'])) . '</a>'];
    }
}
?>
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-5">
            <?php if ($image !== ''): ?>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <?= picture($item['image'], 'large', ['class' => 'img-fluid w-100', 'alt' => media_alt($item['image']) ?: $item['title'], 'loading' => 'eager'], '(min-width: 992px) 50vw, 100vw') ?>
                </div>
            <?php endif ?>
            <div class="<?= $image !== '' ? 'col-lg-6' : 'col-12' ?> wow fadeInUp" data-wow-delay="0.3s">
                <div class="title">
                    <div class="title-left">
                        <?php if ($item['category'] !== ''): ?><h5><?= e($item['category']) ?></h5><?php endif ?>
                        <h1><?= e($item['title']) ?></h1>
                    </div>
                </div>
                <?php if (($item['excerpt'] ?? '') !== ''): ?>
                    <p class="fs-5 mb-4"><?= nl2br(e($item['excerpt'])) ?></p>
                <?php endif ?>
                <?php if ($facts !== []): ?>
                    <table class="table table-dark mb-4">
                        <?php foreach ($facts as [$label, $value]): ?>
                            <tr><td class="text-uppercase"><?= e($label) ?></td><td><?= $value ?></td></tr>
                        <?php endforeach ?>
                    </table>
                <?php endif ?>
            </div>
        </div>
        <?php if (($item['body'] ?? '') !== ''): ?>
            <div class="rich-text fs-5 mt-5 wow fadeInUp" data-wow-delay="0.2s"><?= $item['body'] ?></div>
        <?php endif ?>
        <?php if ($breadcrumbs !== []): ?>
            <a href="<?= e($breadcrumbs[0]['href']) ?>" class="btn btn-outline-primary border-2 py-3 px-5 mt-5"><i class="fa fa-arrow-left me-2"></i><?= e($breadcrumbs[0]['title']) ?></a>
        <?php endif ?>
    </div>
</div>
