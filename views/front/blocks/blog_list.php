<?php
/** @var Core\View\Template $this @var array $data — ბლოგის სტატიები ბარათებად (უახლესი პირველი) */
$posts = array_values(array_filter($data['posts'], fn ($post) => $post['title'] !== ''));
if ($posts === []) {
    return;
}
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <?= $this->insert('front/partials/title', ['subtitle' => $data['subtitle'], 'heading' => $data['title']]) ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <article class="post-card h-100">
                        <?php if (media_url($post['image'], 'medium') !== ''): ?>
                            <a<?= link_attrs($post['link']) ?> class="post-card-img" tabindex="-1" aria-hidden="true">
                                <?= picture($post['image'], 'medium', ['class' => 'img-fluid w-100', 'alt' => ''], '(min-width: 992px) 33vw, (min-width: 768px) 50vw, 100vw') ?>
                            </a>
                        <?php endif ?>
                        <div class="post-card-body">
                            <p class="post-meta mb-2">
                                <?php if ($post['date'] !== ''): ?><time datetime="<?= e($post['date']) ?>"><?= e(date_human($post['date'])) ?></time><?php endif ?>
                                <?php if ($post['category'] !== ''): ?><span aria-hidden="true"> · </span><?= e($post['category']) ?><?php endif ?>
                                <span aria-hidden="true"> · </span><?= e(t('blog.min_read', ['min' => reading_minutes((string)$post['body'])])) ?>
                                <?php if (($post['source'] ?? '') !== ''): ?><span aria-hidden="true"> · </span><span class="post-card-source"><?= e($post['source']) ?></span><?php endif ?>
                            </p>
                            <h3 class="h5 mb-2"><a<?= link_attrs($post['link']) ?> class="post-card-title"><?= e($post['title']) ?></a></h3>
                            <?php if ($post['excerpt'] !== ''): ?><p class="mb-0"><?= e($post['excerpt']) ?></p><?php endif ?>
                        </div>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
        <?php if (($data['pagination']['pages'] ?? 1) > 1): ?>
            <?php
            ['page' => $current, 'pages' => $pages] = $data['pagination'];
            $base = url(ltrim((string)app()->request()?->path(), '/'));
            $href = fn (int $n) => e($n > 1 ? $base . '?page=' . $n : $base);
            ?>
            <nav class="blog-pagination mt-5" aria-label="<?= e(t('blog.pages')) ?>">
                <ul class="pagination justify-content-center flex-wrap mb-0">
                    <?php if ($current > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $href($current - 1) ?>" rel="prev" aria-label="<?= e(t('blog.prev')) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></li>
                    <?php endif ?>
                    <?php for ($n = max(1, $current - 2); $n <= min($pages, $current + 2); $n++): ?>
                        <li class="page-item<?= $n === $current ? ' active' : '' ?>">
                            <a class="page-link" href="<?= $href($n) ?>"<?= $n === $current ? ' aria-current="page"' : '' ?> aria-label="<?= e(t('blog.page', ['n' => $n])) ?>"><?= $n ?></a>
                        </li>
                    <?php endfor ?>
                    <?php if ($current < $pages): ?>
                        <li class="page-item"><a class="page-link" href="<?= $href($current + 1) ?>" rel="next" aria-label="<?= e(t('blog.next')) ?>"><i class="fa fa-arrow-right" aria-hidden="true"></i></a></li>
                    <?php endif ?>
                </ul>
            </nav>
        <?php endif ?>
        <?php if ($data['button_label'] !== '' && $data['button_link']): ?>
            <div class="text-center mt-5">
                <a<?= link_attrs($data['button_link']) ?> class="btn btn-outline-primary border-2 py-3 px-5"><?= e($data['button_label']) ?></a>
            </div>
        <?php endif ?>
    </div>
</div>
