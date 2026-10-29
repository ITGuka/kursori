<?php
/**
 * @var Core\View\Template $this
 * @var array|null $feed
 * @var array $data
 * @var array<int, string> $categories ბლოგის კატეგორიები
 */
$this->layout('admin/layouts/app');
$isEdit = $feed !== null;
$action = $isEdit ? route('admin.news.feeds.update', ['id' => $feed['id']]) : route('admin.news.feeds.store');
$invalid = fn (string $key) => isset($formErrors[$key]) ? ' is-invalid' : '';
$feedback = fn (string $key) => isset($formErrors[$key]) ? '<div class="invalid-feedback">' . e($formErrors[$key]) . '</div>' : '';
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(route('admin.news.feeds.index')) ?>">სიახლეები — feed-ები</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? e($feed['name']) : 'ახალი' ?></li>
    </ol>
</nav>

<form method="post" action="<?= e($action) ?>" class="card form-narrow" novalidate>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif ?>

    <h5 class="card-header"><?= $isEdit ? 'Feed-ის რედაქტირება' : 'ახალი feed' ?></h5>

    <div class="card-body">
        <div class="mb-5">
            <label class="form-label" for="name">სახელი</label>
            <input type="text" class="form-control<?= $invalid('name') ?>" id="name" name="name" maxlength="100" value="<?= e($data['name']) ?>" placeholder="მაგ. PHP">
            <?= $feedback('name') ?>
        </div>
        <div class="mb-5">
            <label class="form-label" for="tags">dev.to ტეგები</label>
            <input type="text" class="form-control<?= $invalid('tags') ?>" id="tags" name="tags" maxlength="500" value="<?= e($data['tags']) ?>" placeholder="php, laravel, symfony">
            <?= $feedback('tags') ?>
            <div class="form-text">მძიმით, მაქსიმუმ 10. ტეგი — dev.to-ს მისამართიდან: dev.to/t/<b>php</b>. სიახლე უნდა ჰქონდეს ერთი მაინც.</div>
        </div>
        <div class="mb-5">
            <label class="form-label" for="exclude_tags">გამოსარიცხი ტეგები</label>
            <input type="text" class="form-control" id="exclude_tags" name="exclude_tags" maxlength="500" value="<?= e($data['exclude_tags']) ?>">
            <div class="form-text">სტატია, რომელსაც რომელიმე ამათგანი აქვს, არ წამოვა (მაგ. სტარტაპის/კარიერის თემები, რეკლამა).</div>
        </div>
        <div class="mb-5">
            <label class="form-label" for="category_id">ბლოგის კატეგორია</label>
            <select class="form-select<?= $invalid('category_id') ?>" id="category_id" name="category_id">
                <option value="">— კატეგორიის გარეშე</option>
                <?php foreach ($categories as $id => $name): ?>
                    <option value="<?= e($id) ?>"<?= (int)$data['category_id'] === $id ? ' selected' : '' ?>><?= e($name) ?></option>
                <?php endforeach ?>
            </select>
            <?= $feedback('category_id') ?>
            <div class="form-text">ახალი კატეგორია — <a href="<?= e(route('admin.categories.index', ['type' => 'blog'])) ?>">ბლოგი → კატეგორიები</a>.</div>
        </div>
        <div class="mb-5">
            <label class="form-label d-block">გამოქვეყნება</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="publish_mode" id="mode_review" value="review"<?= $data['publish_mode'] !== 'auto' ? ' checked' : '' ?>>
                <label class="form-check-label" for="mode_review">მოდერაცია — სიახლე რიგში ჩნდება და ქვეყნდება დამტკიცების შემდეგ</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="publish_mode" id="mode_auto" value="auto"<?= $data['publish_mode'] === 'auto' ? ' checked' : '' ?>>
                <label class="form-check-label" for="mode_auto">ავტომატური — ქვეყნდება მოდერაციის გარეშე (დღიური ლიმიტით)</label>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-sm-4">
                <label class="form-label" for="top_days">ბოლო დღეები</label>
                <input type="number" class="form-control" id="top_days" name="top_days" min="1" max="30" value="<?= e($data['top_days']) ?>">
                <div class="form-text">პოპულარული ამ პერიოდში.</div>
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="min_reactions">მინ. რეაქციები</label>
                <input type="number" class="form-control" id="min_reactions" name="min_reactions" min="<?= App\Models\NewsFeed::MIN_REACTIONS ?>" max="10000" value="<?= e($data['min_reactions']) ?>">
                <div class="form-text">ხარისხის ფილტრი, მინ. <?= App\Models\NewsFeed::MIN_REACTIONS ?>.</div>
            </div>
            <div class="col-sm-4">
                <label class="form-label" for="per_run">თითო წამოღებაზე</label>
                <input type="number" class="form-control" id="per_run" name="per_run" min="1" max="30" value="<?= e($data['per_run']) ?>">
                <div class="form-text">ყველაზე პოპულარული.</div>
            </div>
        </div>
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="require_image" value="0">
            <input class="form-check-input" type="checkbox" id="require_image" name="require_image" value="1" <?= !empty($data['require_image']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="require_image">მხოლოდ ყდის სურათით</label>
        </div>
        <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($data['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">აქტიური</label>
        </div>
    </div>

    <div class="card-footer border-top d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>შენახვა</button>
        <?php if ($isEdit): ?>
            <a href="<?= e(route('admin.news.feeds.test', ['id' => $feed['id']])) ?>" class="btn btn-outline-secondary"><i class="icon-base bx bx-search-alt me-1"></i>ტესტი</a>
        <?php endif ?>
        <a href="<?= e(route('admin.news.feeds.index')) ?>" class="btn btn-outline-secondary">გაუქმება</a>
    </div>
</form>
