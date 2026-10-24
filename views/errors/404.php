<?php
/**
 * Poseify-ის 404 გვერდი. ენის route-ის გარეთ (მაგ. /admin/...) ან ენების ცხრილის გარეშე — მარტივი ვერსია.
 * @var Core\View\Template $this
 */
use App\Models\Language;

try {
    $isAdmin = str_starts_with((string)app()->request()?->path(), '/admin');
    $languages = $isAdmin ? [] : app(Language::class)->active();
} catch (Throwable) {
    $languages = [];
}
if ($languages === []) {
    echo $this->insert('errors/default', ['status' => 404, 'message' => $message ?? 'Not Found']);
    return;
}
$this->layout('front/layouts/main', [
    'meta'      => ['title' => t('errors.not_found_title') . ' · ' . setting('general.site_name'), 'noindex' => true,
                    'language_links' => array_combine(array_column($languages, 'code'), array_map(fn ($l) => url($l['code']), $languages))],
    'languages' => $languages,
]);
$this->start('header');
echo $this->insert('front/blocks/page_header', ['data' => ['title' => '404', 'image' => null, 'breadcrumb' => false], 'page' => ['title' => '404']]);
$this->stop();
?>
<div class="container-fluid py-5 bg-secondary">
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <i class="fa fa-triangle-exclamation display-1 text-primary wow fadeInUp" data-wow-delay="0.1s"></i>
                <h1 class="display-1 wow fadeInUp" data-wow-delay="0.2s">404</h1>
                <h3 class="mb-4 text-uppercase wow fadeInUp" data-wow-delay="0.3s"><?= e(t('errors.not_found_title')) ?></h3>
                <p class="mb-4 wow fadeInUp" data-wow-delay="0.4s"><?= e(t('errors.not_found_text')) ?></p>
                <a class="btn btn-outline-primary border-2 py-3 px-5 wow fadeInUp" data-wow-delay="0.5s" href="<?= e(url(locale())) ?>"><?= e(t('errors.back_home')) ?></a>
            </div>
        </div>
    </div>
</div>
