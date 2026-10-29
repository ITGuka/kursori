<?php
/**
 * შემოსული ფორმის ცხრილი წერილში (FormSubmission::present-ის სექციები).
 * @var Core\View\Template $this
 * @var array $sections
 * @var string $fileNote ფაილის მინიშნება (მაგ. „ჩამოტვირთვა — Inbox-იდან“; ცარიელი — მხოლოდ სახელი)
 */
use App\Models\FormSubmission;

$cell = 'padding:8px 0;border-bottom:1px solid #eee;vertical-align:top;';
?>
<?php foreach ($sections as $section): ?>
    <?php if ($section['title'] !== null): ?>
        <p style="margin:20px 0 4px;font-size:13px;font-weight:bold;letter-spacing:.5px;text-transform:uppercase;color:#e1a96b;"><?= e($section['title']) ?></p>
    <?php endif ?>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:15px;">
        <?php foreach ($section['rows'] as $row): ?>
            <tr>
                <td style="<?= $cell ?>padding-right:12px;color:#888;width:35%;"><?= e($row['label']) ?></td>
                <td style="<?= $cell ?>">
                    <?php if ($row['file'] !== null): ?>
                        <?= e($row['file']['name']) ?> <span style="color:#888;">(<?= e(FormSubmission::fileSize((int)($row['file']['size'] ?? 0))) ?><?= $fileNote !== '' ? ' · ' . e($fileNote) : '' ?>)</span>
                    <?php elseif ($row['links'] !== []): ?>
                        <?php foreach ($row['links'] as $link): ?>
                            <a href="<?= e($link) ?>" style="color:#c0185f;word-break:break-all;"><?= e($link) ?></a><br>
                        <?php endforeach ?>
                    <?php else: ?>
                        <?= $row['value'] === '' ? '—' : nl2br(e($row['value'])) ?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endforeach ?>
