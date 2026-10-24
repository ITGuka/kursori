<?php
/**
 * @var Core\View\Template $this
 * @var string $lang
 * @var string $subject
 * @var string $body HTMLPurifier-ით გაწმენდილი
 * @var string $unsubscribeUrl
 */
$this->layout('emails/layout');
?>
<h1 style="margin:0 0 16px;font-size:22px;"><?= e($subject) ?></h1>
<?= $body ?>
<?php $this->start('footer') ?>
<?= e(t('newsletter.email_footer', ['site' => setting('general.site_name', $lang)], $lang)) ?>
<a href="<?= e($unsubscribeUrl) ?>" style="color:#888;"><?= e(t('newsletter.unsubscribe_link', [], $lang)) ?></a>
<?php $this->stop() ?>
