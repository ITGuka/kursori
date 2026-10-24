<?php
/** @var Core\View\Template $this @var string $lang @var string $url */
$this->layout('emails/layout');
?>
<p style="margin:0 0 16px;"><?= e(t('newsletter.email_confirm_text', ['site' => setting('general.site_name', $lang)], $lang)) ?></p>
<p style="margin:24px 0;">
    <a href="<?= e($url) ?>" style="display:inline-block;background:#e1a96b;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:50px;font-weight:bold;"><?= e(t('newsletter.email_confirm_button', [], $lang)) ?></a>
</p>
<p style="margin:0;font-size:13px;color:#888;"><?= e(t('newsletter.email_confirm_ignore', [], $lang)) ?></p>
