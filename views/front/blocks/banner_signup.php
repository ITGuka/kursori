<?php
/**
 * @var Core\View\Template $this @var array $data
 * გამოწერა → POST /{lang}/newsletter (double opt-in: დასტურის წერილი).
 */
use Core\Support\Session;

$isThis = Session::get('_form') === 'newsletter';
$error = $isThis ? errors('email') : null;
?>
<div class="container-fluid py-5<?= $data['background'] === 'secondary' ? ' bg-secondary' : '' ?>">
    <div class="container py-5">
        <div class="row g-0 justify-content-center">
            <div class="col-lg-7 text-center">
                <?php if ($data['subtitle'] !== '' || $data['title'] !== ''): ?>
                    <div class="title mx-5 px-5 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="title-center">
                            <?php if ($data['subtitle'] !== ''): ?><h5><?= e($data['subtitle']) ?></h5><?php endif ?>
                            <?php if ($data['title'] !== ''): ?><h2 class="h1"><?= e($data['title']) ?></h2><?php endif ?>
                        </div>
                    </div>
                <?php endif ?>
                <?php if ($data['text'] !== ''): ?>
                    <p class="fs-5 mb-5 wow fadeInUp" data-wow-delay="0.2s"><?= nl2br(e($data['text'])) ?></p>
                <?php endif ?>
                <form method="post" action="<?= e(route('newsletter.subscribe', ['lang' => locale()])) ?>" id="form-newsletter" novalidate class="wow fadeInUp" data-wow-delay="0.3s">
                    <?= csrf_field() ?>
                    <?= $this->insert('front/partials/form-status', ['form' => 'newsletter']) ?>
                    <div class="position-relative">
                        <input class="form-control border-0 bg-dark rounded-pill w-100 py-4 ps-4 pe-5<?= $error ? ' is-invalid' : '' ?>" type="email" name="email" required maxlength="190"
                               value="<?= e($isThis ? (string)old('email', '') : '') ?>"
                               placeholder="<?= e(t('newsletter.email')) ?>" aria-label="<?= e(t('newsletter.email')) ?>">
                        <button type="submit" class="btn btn-primary py-3 px-4 position-absolute top-0 end-0 me-2" style="margin-top: 7px;"><?= e(t('newsletter.subscribe')) ?></button>
                    </div>
                    <?php if ($error): ?><div class="text-danger small mt-2"><?= e($error) ?></div><?php endif ?>
                    <?= $this->insert('front/partials/form-consent', ['class' => 'mt-3']) ?>
                </form>
            </div>
        </div>
    </div>
</div>
