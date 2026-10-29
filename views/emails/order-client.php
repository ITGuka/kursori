<?php
/**
 * ინდივიდუალური შეკვეთის დადასტურება კლიენტს — მის ენაზე, ბრიფის ასლით (FormSubmission::present).
 * @var Core\View\Template $this @var string $lang @var array $data @var array $definition @var int $id
 */
use App\Models\FormSubmission;

$this->layout('emails/layout');
?>
<p style="margin:0 0 16px;font-size:18px;font-weight:bold;"><?= e(t('custom.confirm_title', ['name' => (string)($data['name'] ?? '')], $lang)) ?></p>
<p style="margin:0 0 8px;"><?= e(t('custom.confirm_text', ['id' => $id], $lang)) ?></p>
<p style="margin:24px 0 0;font-weight:bold;"><?= e(t('custom.confirm_summary', [], $lang)) ?></p>
<?= $this->insert('emails/partials/submission', ['sections' => FormSubmission::present($definition, $data, $lang), 'fileNote' => '']) ?>
<?php $this->start('footer') ?>
<?= e(t('custom.confirm_footer', [], $lang)) ?>
<?php $this->stop() ?>
