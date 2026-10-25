<?php
/**
 * ბლოგის სტატია: მეტა (თარიღი, კატეგორია, საკითხავი დრო, ავტორი), სურათი, ტექსტი ვიწრო სვეტში,
 * BlogPosting სტრუქტურირებული მონაცემები; ბოლოს — CTA და სიახლეების გამოწერა.
 * @var Core\View\Template $this
 * @var array $item resolve-ის ფორმატი (excerpt, body, author, date)
 * @var list<array> $breadcrumbs
 */
$this->layout('front/layouts/main');
$this->start('header');
echo $this->insert('front/blocks/page_header', ['data' => ['title' => '', 'image' => null, 'breadcrumb' => true], 'page' => ['title' => $item['title']], 'breadcrumbs' => $breadcrumbs]);
$this->stop();

$meta = array_filter([
    $item['date'] !== '' ? '<time datetime="' . e($item['date']) . '">' . e(date_human($item['date'])) . '</time>' : '',
    $item['category'] !== '' ? e($item['category']) : '',
    e(t('blog.min_read', ['min' => reading_minutes($item['body'])])),
    $item['author'] !== '' ? e($item['author']) : '',
]);
$image = media_url($item['image'], 'large');
// url()/media_url() base path-ით (მაგ. /WEBDEV/...) → სრული URL APP_URL-ით, როგორც layout-ში
$basePath = app()->baseUrlPath();
$absolute = function (string $url) use ($basePath): string {
    $path = (string)parse_url($url, PHP_URL_PATH);
    return absolute_url($basePath !== '' && str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath)) : $path);
};
$schema = array_filter([
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => $item['title'],
    'description'   => $item['excerpt'] ?: null,
    'datePublished' => $item['date'] ?: null,
    'image'         => $image !== '' ? $absolute($image) : null,
    'author'        => $item['author'] !== '' ? ['@type' => 'Person', 'name' => $item['author']] : ['@type' => 'Organization', 'name' => (string)setting('general.site_name')],
    'publisher'     => ['@type' => 'Organization', 'name' => (string)setting('general.site_name')],
    'mainEntityOfPage' => $item['url'] !== '' ? $absolute($item['url']) : null,
]);
?>
<article class="container-fluid py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <p class="post-meta"><?= implode('<span aria-hidden="true"> · </span>', $meta) ?></p>
                <?php if ($item['excerpt'] !== ''): ?><p class="fs-4 text-white mb-5"><?= nl2br(e($item['excerpt'])) ?></p><?php endif ?>
                <?php if ($image !== ''): ?>
                    <?= picture($item['image'], 'large', ['class' => 'img-fluid w-100 rounded mb-5', 'alt' => media_alt($item['image']) ?: $item['title'], 'loading' => 'eager'], '(min-width: 992px) 66vw, 100vw') ?>
                <?php endif ?>
                <div class="rich-text post-body"><?= $item['body'] ?></div>
                <?php if (($cta = cta_link()) !== null): ?>
                    <aside class="post-cta">
                        <p class="mb-3"><?= e(t('blog.cta_text')) ?></p>
                        <a href="<?= e($cta['href']) ?>" class="btn btn-primary border-2 px-4 py-3"><?= e($cta['label']) ?></a>
                    </aside>
                <?php endif ?>
                <?php if ($breadcrumbs !== []): ?>
                    <a href="<?= e($breadcrumbs[0]['href']) ?>" class="btn btn-outline-primary border-2 py-3 px-5 mt-5"><i class="fa fa-arrow-left me-2"></i><?= e(t('blog.all_posts')) ?></a>
                <?php endif ?>
            </div>
        </div>
    </div>
</article>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<?= $this->insert('front/blocks/banner_signup', [
    'data' => ['subtitle' => t('blog.newsletter_subtitle'), 'title' => t('blog.newsletter_title'), 'text' => t('blog.newsletter_text'), 'background' => 'secondary'],
]) ?>
