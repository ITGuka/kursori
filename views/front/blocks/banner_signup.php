<?php
/**
 * @var Core\View\Template $this @var array $data
 * გამოწერის ფორმის დამუშავება — ფაზა 7 (route "newsletter.subscribe"); მანამდე ფორმა არ იგზავნება.
 */
use Core\Http\Router;

$action = app(Router::class)->has('newsletter.subscribe') ? route('newsletter.subscribe', ['lang' => locale()]) : '';
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <div class="row g-0 justify-content-center">
            <div class="col-lg-7 text-center">
                <?php if ($data['subtitle'] !== '' || $data['title'] !== ''): ?>
                    <div class="title mx-5 px-5 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="title-center">
                            <?php if ($data['subtitle'] !== ''): ?><h5><?= e($data['subtitle']) ?></h5><?php endif ?>
                            <?php if ($data['title'] !== ''): ?><h1><?= e($data['title']) ?></h1><?php endif ?>
                        </div>
                    </div>
                <?php endif ?>
                <?php if ($data['text'] !== ''): ?>
                    <p class="fs-5 mb-5 wow fadeInUp" data-wow-delay="0.2s"><?= nl2br(e($data['text'])) ?></p>
                <?php endif ?>
                <form class="position-relative wow fadeInUp" data-wow-delay="0.3s" method="post" action="<?= e($action) ?>"<?= $action === '' ? ' onsubmit="return false"' : '' ?>>
                    <?= csrf_field() ?>
                    <input class="form-control border-0 bg-dark rounded-pill w-100 py-4 ps-4 pe-5" type="email" name="email" required
                           placeholder="<?= e(t('newsletter.email')) ?>" aria-label="<?= e(t('newsletter.email')) ?>">
                    <button type="submit" class="btn btn-primary py-3 px-4 position-absolute top-0 end-0 me-2" style="margin-top: 7px;"><?= e(t('newsletter.subscribe')) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
