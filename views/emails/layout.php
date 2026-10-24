<?php
/**
 * წერილის საერთო HTML (inline სტილებით — ფოსტის კლიენტები გარე CSS-ს არ კითხულობენ).
 * @var Core\View\Template $this
 * @var string $lang
 */
$siteName = (string)setting('general.site_name', $lang);
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($siteName) ?></title></head>
<body style="margin:0;padding:0;background:#f2f2f2;font-family:Arial,'Noto Sans Georgian',sans-serif;color:#222;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f2f2;padding:24px 12px;">
    <tr><td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:6px;overflow:hidden;">
            <tr><td style="background:#131313;padding:20px 28px;color:#e1a96b;font-size:22px;font-weight:bold;text-transform:uppercase;"><?= e($siteName) ?></td></tr>
            <tr><td style="padding:28px;font-size:16px;line-height:1.6;"><?= $this->section('content') ?></td></tr>
            <?php if ($this->hasSection('footer')): ?>
                <tr><td style="padding:16px 28px;border-top:1px solid #eee;font-size:12px;color:#888;"><?= $this->section('footer') ?></td></tr>
            <?php endif ?>
        </table>
    </td></tr>
</table>
</body>
</html>
