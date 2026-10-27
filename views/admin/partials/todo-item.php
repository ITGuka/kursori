<?php
/** @var array{id: int|string, text: string, is_done: int|string} $todo */
$done = (bool)$todo['is_done'];
?>
<div class="d-flex align-items-center border-bottom py-2" data-todo-item data-id="<?= e($todo['id']) ?>">
    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" aria-label="შესრულებულია" data-todo-toggle<?= $done ? ' checked' : '' ?>>
    <div class="w-100 ms-3 min-w-0">
        <div class="d-flex w-100 align-items-center justify-content-between">
            <span class="text-break<?= $done ? ' text-decoration-line-through text-muted' : '' ?>" data-todo-text><?= e($todo['text']) ?></span>
            <button type="button" class="btn btn-sm btn-icon btn-outline-secondary border-0<?= $done ? ' text-primary' : '' ?>" title="წაშლა" aria-label="წაშლა" data-todo-delete><i class="icon-base bx bx-x"></i></button>
        </div>
    </div>
</div>
