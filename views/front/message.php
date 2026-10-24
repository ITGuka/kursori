<?php
/**
 * მოკლე შეტყობინების გვერდი (newsletter-ის დასტური, გაუქმება).
 * @var Core\View\Template $this
 * @var string $heading
 * @var string $text
 * @var array{action: string, button: string}|null $form
 */
$this->layout('front/layouts/main');
$this->start('header');
echo $this->insert('front/blocks/page_header', ['data' => ['title' => '', 'image' => null, 'breadcrumb' => false], 'page' => ['title' => $heading]]);
$this->stop();
?>
<div class="container-fluid py-5 bg-secondary">
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <p class="fs-5 mb-4"><?= e($text) ?></p>
                <?php if ($form !== null): ?>
                    <form method="post" action="<?= e($form['action']) ?>" class="mb-4">
                        <button type="submit" class="btn btn-primary py-3 px-5"><?= e($form['button']) ?></button>
                    </form>
                <?php endif ?>
                <a class="btn btn-outline-primary border-2 py-3 px-5" href="<?= e(url(locale())) ?>"><?= e(t('errors.back_home')) ?></a>
            </div>
        </div>
    </div>
</div>
