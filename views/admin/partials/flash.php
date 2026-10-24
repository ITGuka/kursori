<?php
use Core\Support\Session;

$messages = ['success' => Session::get('success'), 'danger' => Session::get('error')];
?>
<?php foreach ($messages as $type => $message): ?>
    <?php if (is_string($message) && $message !== ''): ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="დახურვა"></button>
        </div>
    <?php endif ?>
<?php endforeach ?>
