<?php
/**
 * ახალი შეკვეთის შეტყობინება ადმინს (ქართულად — ადმინის ენა).
 * @var Core\View\Template $this @var string $lang @var array $data @var array $definition @var string $orderLang @var string $url
 */
$this->layout('emails/layout');
?>
<p style="margin:0 0 16px;font-size:18px;font-weight:bold;">ახალი შეკვეთა საიტიდან</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:15px;">
    <?php foreach (array_replace(array_intersect_key($definition['fields'], $data), $data) as $name => $value): ?>
        <?php $field = $definition['fields'][$name] ?? []; ?>
        <?php if (isset($field['options'][$value])) { $value = t($field['options'][$value], [], $lang); } ?>
        <tr>
            <td style="padding:8px 12px 8px 0;border-bottom:1px solid #eee;color:#888;vertical-align:top;width:35%;"><?= e(isset($field['label']) ? t($field['label'], [], $lang) : $name) ?></td>
            <td style="padding:8px 0;border-bottom:1px solid #eee;vertical-align:top;"><?= (string)$value === '' ? '—' : nl2br(e((string)$value)) ?></td>
        </tr>
    <?php endforeach ?>
    <tr>
        <td style="padding:8px 12px 8px 0;color:#888;">ენა</td>
        <td style="padding:8px 0;text-transform:uppercase;"><?= e($orderLang) ?></td>
    </tr>
</table>
<p style="margin:24px 0 0;">
    <a href="<?= e($url) ?>" style="display:inline-block;background:#e1a96b;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:50px;font-weight:bold;">Inbox-ში ნახვა</a>
</p>
