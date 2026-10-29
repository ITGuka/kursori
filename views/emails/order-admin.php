<?php
/**
 * ახალი შეკვეთის შეტყობინება ადმინს (ქართულად — ადმინის ენა). ბრიფი — ნაბიჯებად, ცარიელი ველების გარეშე (FormSubmission::present).
 * @var Core\View\Template $this @var string $lang @var array $data @var array $definition @var string $orderLang @var string $url
 */
use App\Models\FormSubmission;

$this->layout('emails/layout');
?>
<p style="margin:0 0 16px;font-size:18px;font-weight:bold;"><?= e($definition['label'] ?? 'ახალი შეკვეთა საიტიდან') ?></p>
<?= $this->insert('emails/partials/submission', ['sections' => FormSubmission::present($definition, $data, $lang), 'fileNote' => 'ჩამოტვირთვა — Inbox-იდან']) ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:15px;">
    <tr>
        <td style="padding:8px 12px 8px 0;color:#888;width:35%;">ენა</td>
        <td style="padding:8px 0;text-transform:uppercase;"><?= e($orderLang) ?></td>
    </tr>
</table>
<p style="margin:24px 0 0;">
    <a href="<?= e($url) ?>" style="display:inline-block;background:#e1a96b;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:50px;font-weight:bold;">Inbox-ში ნახვა</a>
</p>
