<?php
/**
 * @var Core\View\Template $this
 * @var array|null $campaign
 * @var array $data subject, body, lang
 * @var array{total: int, sent: int, failed: int, pending: int}|null $stats
 */
$this->layout('admin/layouts/app');
$isEdit = $campaign !== null;
$editable = !$isEdit || $campaign['status'] === 'draft';
$action = $isEdit ? route('admin.newsletter.update', ['id' => $campaign['id']]) : route('admin.newsletter.store');
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
?>
<?php $this->start('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/jodit@3.24.9/build/jodit.min.css" rel="stylesheet">
<?php $this->stop() ?>
<?= $this->insert('admin/newsletter/tabs') ?>

<div class="row g-4">
    <div class="col-xl-8">
        <form method="post" action="<?= e($action) ?>" class="bg-light rounded-bottom p-4 block-form" novalidate>
            <?= csrf_field() ?>
            <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>
            <fieldset<?= $editable ? '' : ' disabled' ?>>
                <div class="mb-3">
                    <label class="form-label" for="subject">თემა</label>
                    <input type="text" class="form-control<?= $invalid('subject') ?>" id="subject" name="subject" maxlength="190" value="<?= e($data['subject']) ?>">
                    <?php if (isset($formErrors['subject'])): ?><div class="invalid-feedback"><?= e($formErrors['subject']) ?></div><?php endif ?>
                </div>
                <div class="mb-3" style="max-width: 320px">
                    <label class="form-label" for="lang">ადრესატები</label>
                    <select class="form-select" id="lang" name="lang">
                        <option value="">ყველა აქტიური გამომწერი</option>
                        <?php foreach ($languages as $language): ?>
                            <option value="<?= e($language['code']) ?>"<?= $data['lang'] === $language['code'] ? ' selected' : '' ?>>მხოლოდ: <?= e($language['native_name']) ?></option>
                        <?php endforeach ?>
                    </select>
                    <div class="form-text">გამომწერის ენა — ის ენა, რომელზეც საიტზე გამოიწერა.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="body">ტექსტი</label>
                    <textarea class="form-control<?= $invalid('body') ?>" id="body" name="body" rows="10" data-wysiwyg><?= e($data['body']) ?></textarea>
                    <?php if (isset($formErrors['body'])): ?><div class="invalid-feedback d-block"><?= e($formErrors['body']) ?></div><?php endif ?>
                    <div class="form-text">წერილს ავტომატურად ემატება საიტის სახელი და გამოწერის გაუქმების ბმული.</div>
                </div>
            </fieldset>
            <?php if ($editable): ?>
                <button type="submit" class="btn btn-primary">შენახვა</button>
            <?php endif ?>
            <a href="<?= e(route('admin.newsletter.campaigns')) ?>" class="btn btn-outline-secondary ms-2">სიაში დაბრუნება</a>
        </form>
    </div>

    <?php if ($isEdit): ?>
        <div class="col-xl-4">
            <div class="bg-light rounded p-4 mb-4">
                <h6 class="mb-3">ტესტური წერილი</h6>
                <form method="post" action="<?= e(route('admin.newsletter.test', ['id' => $campaign['id']])) ?>" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="email" class="form-control form-control-sm" name="email" value="<?= e($adminEmail) ?>" required aria-label="ელ-ფოსტა">
                    <button class="btn btn-sm btn-outline-primary text-nowrap"><i class="fa fa-paper-plane me-1"></i>ტესტი</button>
                </form>
            </div>

            <div class="bg-light rounded p-4" data-campaign data-status="<?= e($campaign['status']) ?>" data-batch-url="<?= e(route('admin.newsletter.batch', ['id' => $campaign['id']])) ?>">
                <h6 class="mb-3">გაგზავნა</h6>
                <?php if ($campaign['status'] === 'draft'): ?>
                    <p class="small">წერილს მიიღებს <strong><?= e($recipients) ?></strong> აქტიური გამომწერი<?= $campaign['lang'] !== '' ? ' (მხოლოდ ' . e(strtoupper($campaign['lang'])) . ')' : '' ?>. გაგზავნის შემდეგ კამპანია აღარ შეიცვლება.</p>
                    <form method="post" action="<?= e(route('admin.newsletter.start', ['id' => $campaign['id']])) ?>" data-confirm="გავგზავნოთ კამპანია „<?= e($campaign['subject']) ?>“?">
                        <?= csrf_field() ?>
                        <button class="btn btn-primary w-100"><i class="fa fa-paper-plane me-1"></i>გაგზავნა</button>
                    </form>
                <?php else: ?>
                    <div class="progress mb-2" style="height: 22px">
                        <div class="progress-bar" role="progressbar" data-progress style="width: <?= $stats['total'] ? round(($stats['sent'] + $stats['failed']) / $stats['total'] * 100) : 0 ?>%"></div>
                    </div>
                    <p class="small mb-2" data-progress-text>
                        გაგზავნილი: <?= e($stats['sent']) ?> / <?= e($stats['total']) ?><?= $stats['failed'] ? ' · შეცდომა: ' . (int)$stats['failed'] : '' ?>
                    </p>
                    <?php if ($campaign['status'] === 'sending'): ?>
                        <p class="small text-muted mb-0" data-progress-note>იგზავნება — ნუ დახურავთ გვერდს. დახურვის შემთხვევაში გააგრძელეთ ამ გვერდიდან ან: <code>php bin/console newsletter:send</code></p>
                    <?php else: ?>
                        <p class="small text-success mb-0">დასრულდა <?= e(date('d.m.Y H:i', strtotime((string)$campaign['sent_at']))) ?>.</p>
                    <?php endif ?>
                    <?php if ($failures !== []): ?>
                        <details class="small mt-2"><summary class="text-danger">შეცდომები</summary>
                            <ul class="mb-0 ps-3"><?php foreach ($failures as $failure): ?><li><?= e($failure['email']) ?>: <?= e($failure['error']) ?></li><?php endforeach ?></ul>
                        </details>
                    <?php endif ?>
                <?php endif ?>
            </div>

            <?php if ($campaign['status'] !== 'sending'): ?>
                <form method="post" action="<?= e(route('admin.newsletter.destroy', ['id' => $campaign['id']])) ?>" class="mt-3" data-confirm="წავშალოთ კამპანია (გაგზავნის სტატისტიკითურთ)?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash me-1"></i>კამპანიის წაშლა</button>
                </form>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/jodit@3.24.9/build/jodit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= asset('admin/js/block-editor.js') ?>"></script>
<script>
    // „იგზავნება“ სტატუსში — პარტიების გაგზავნა რიგრიგობით, სანამ არ დასრულდება
    (function ($) {
        var $box = $('[data-campaign][data-status="sending"]');
        if (!$box.length) { return; }
        var next = function () {
            $.ajax({url: $box.data('batch-url'), method: 'POST', headers: {'Accept': 'application/json'}})
                .done(function (stats) {
                    var done = stats.sent + stats.failed;
                    $box.find('[data-progress]').css('width', (stats.total ? Math.round(done / stats.total * 100) : 100) + '%');
                    $box.find('[data-progress-text]').text('გაგზავნილი: ' + stats.sent + ' / ' + stats.total + (stats.failed ? ' · შეცდომა: ' + stats.failed : ''));
                    if (stats.done) { window.location.reload(); } else { setTimeout(next, 300); }
                })
                .fail(function () {
                    $box.find('[data-progress-note]').addClass('text-danger').text('კავშირი გაწყდა — განაახლეთ გვერდი გასაგრძელებლად.');
                });
        };
        next();
    })(jQuery);
</script>
<?php $this->stop() ?>
