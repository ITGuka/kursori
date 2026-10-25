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
                            </p>
                            <h3 class="h5 mb-2"><a<?= link_attrs($post['link']) ?> class="post-card-title"><?= e($post['title']) ?></a></h3>
                            <?php if ($post['excerpt'] !== ''): ?><p class="mb-0"><?= e($post['excerpt']) ?></p><?php endif ?>
                        </div>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
        <?php if ($data['button_label'] !== '' && $data['button_link']): ?>
            <div class="text-center mt-5">
                <a<?= link_attrs($data['button_link']) ?> class="btn btn-outline-primary border-2 py-3 px-5"><?= e($data['button_label']) ?></a>
            </div>
        <?php endif ?>
    </div>
</div>
