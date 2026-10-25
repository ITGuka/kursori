<?php
/**
 * თანხმობის ტექსტი ფორმის ქვეშ, კონფიდენციალურობის პოლიტიკის ბმულით (პარამეტრები → სამართლებრივი).
 * @var string $class დამატებითი კლასები
 */
$pageId = (int)setting('legal.privacy_page');
$href = $pageId > 0 ? app(App\Models\Page::class)->url($pageId, locale()) : '';
if ($href === '') {
    return;
}
$link = '<a href="' . e($href) . '" target="_blank" rel="noopener">' . e(t('form.consent_link')) . '</a>';
?>
<p class="form-consent small mb-0 <?= e($class ?? '') ?>"><?= str_replace(':policy', $link, e(t('form.consent'))) ?></p>
