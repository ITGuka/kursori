<?php
/** @var array $data */
$src = $data['embed_url'] !== '' ? $data['embed_url'] : (string)setting('contact.map_embed_url');
// მხოლოდ Google Maps-ის embed — iframe-ში სხვა საიტი არ ჩაიტვირთება
if (!preg_match('~^https://(www\.)?google\.[a-z.]+/maps/embed\?~i', $src)) {
    return;
}
?>
<div class="container-fluid p-0 wow fadeIn" data-wow-delay="0.3s">
    <iframe src="<?= e($src) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="<?= e(t('contact.address')) ?>"
            style="width: 100%; height: <?= (int)$data['height'] ?>px; filter: grayscale(100%) invert(92%) contrast(83%); border: 0;"></iframe>
</div>
