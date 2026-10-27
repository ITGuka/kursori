<?php
/**
 * @var Core\View\Template $this
 * @var array<string, array{label: string, max_depth: int}> $locations
 * @var list<array> $items ხე (children)
 * @var array<int, string> $pageTitles
 */
$this->layout('admin/layouts/app');

$label = function (array $item) use ($pageTitles, $defaultLang): string {
    $text = (string)($item['translations'][$defaultLang]['label'] ?? '');
    if ($text === '' && $item['type'] === 'page') {
        $text = ltrim((string)($pageTitles[(int)$item['page_id']] ?? ''), '— ');
    }
    return $text !== '' ? $text : (string)$item['url'];
};

$renderItems = function (array $items, int $depth) use (&$renderItems, $label, $maxDepth, $menuId): string {
    $html = '';
    foreach ($items as $item) {
        $isPage = $item['type'] === 'page';
        $target = $isPage ? 'გვერდი' : e(mb_strimwidth((string)$item['url'], 0, 50, '…'));
        $html .= '<li class="menu-node" data-id="' . (int)$item['id'] . '" id="item-' . (int)$item['id'] . '">'
            . '<div class="block-row' . ($item['is_active'] ? '' : ' is-hidden') . '">'
            . '<span class="block-handle" title="გადაადგილება"><i class="icon-base bx bx-grid-vertical"></i></span>'
            . '<span class="avatar avatar-sm flex-shrink-0"><span class="avatar-initial rounded bg-label-' . ($isPage ? 'primary' : 'info') . '"><i class="icon-base bx ' . ($isPage ? 'bx-file' : 'bx-link') . '"></i></span></span>'
            . '<div class="block-info"><a class="fw-medium text-heading" href="' . e(route('admin.menus.items.edit', ['id' => $item['id']])) . '">' . e($label($item)) . '</a>'
            . (!$item['is_active'] ? ' <span class="badge bg-label-secondary ms-1">დამალული</span>' : '')
            . ($item['new_tab'] ? ' <span class="badge bg-label-secondary ms-1" title="ახალ ფანჯარაში"><i class="icon-base bx bx-link-external icon-xs"></i></span>' : '')
            . '<div class="small text-body-secondary text-truncate">' . $target . '</div></div>'
            . '<div class="table-actions">'
            . ($depth < $maxDepth ? '<a href="' . e(route('admin.menus.items.create', ['id' => $menuId, 'parent' => $item['id']])) . '" class="btn btn-sm btn-icon btn-outline-secondary" title="ქვეპუნქტის დამატება" aria-label="ქვეპუნქტის დამატება"><i class="icon-base bx bx-plus"></i></a>' : '')
            . '<form method="post" action="' . e(route('admin.menus.items.toggle', ['id' => $item['id']])) . '">' . csrf_field()
            . '<button class="btn btn-sm btn-icon btn-outline-secondary" title="' . ($item['is_active'] ? 'დამალვა' : 'გამოჩენა') . '" aria-label="' . ($item['is_active'] ? 'დამალვა' : 'გამოჩენა') . '"><i class="icon-base bx ' . ($item['is_active'] ? 'bx-show' : 'bx-hide') . '"></i></button></form>'
            . '<a href="' . e(route('admin.menus.items.edit', ['id' => $item['id']])) . '" class="btn btn-sm btn-icon btn-outline-primary" title="რედაქტირება" aria-label="რედაქტირება"><i class="icon-base bx bx-edit-alt"></i></a>'
            . '<form method="post" action="' . e(route('admin.menus.items.destroy', ['id' => $item['id']])) . '" data-confirm="წავშალოთ პუნქტი „' . e($label($item)) . '“' . ($item['children'] !== [] ? ' ქვეპუნქტებითურთ' : '') . '?">'
            . csrf_field() . '<input type="hidden" name="_method" value="DELETE"><button class="btn btn-sm btn-icon btn-outline-danger" title="წაშლა" aria-label="წაშლა"><i class="icon-base bx bx-trash"></i></button></form>'
            . '</div></div>'
            . ($depth < $maxDepth ? '<ul class="menu-list menu-children list-unstyled" data-depth="' . ($depth + 1) . '">' . $renderItems($item['children'], $depth + 1) . '</ul>' : '')
            . '</li>';
    }
    return $html;
};
?>

<div class="nav-align-top">
    <ul class="nav nav-pills flex-wrap gap-2 mb-6" role="tablist">
        <?php foreach ($locations as $key => $definition): ?>
            <li class="nav-item">
                <a class="nav-link<?= $key === $location ? ' active' : '' ?>" href="<?= e(route('admin.menus.index', ['location' => $key])) ?>">
                    <i class="icon-base bx <?= $key === 'footer' ? 'bx-dock-bottom' : 'bx-dock-top' ?> icon-sm me-1"></i><?= e($definition['label']) ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="card-title mb-0"><?= e($locations[$location]['label']) ?></h5>
        <a href="<?= e(route('admin.menus.items.create', ['id' => $menuId])) ?>" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i>პუნქტის დამატება</a>
    </div>
    <div class="card-body">
        <?php if ($items === []): ?>
            <p class="text-body-secondary mb-0">მენიუ ცარიელია.<?= $location === 'header' ? ' სანამ პუნქტებს დაამატებთ, საიტზე ჩანს გამოქვეყნებული გვერდები.' : '' ?></p>
        <?php else: ?>
            <ul class="menu-list list-unstyled mb-0" data-menu-sort="<?= e(route('admin.menus.reorder', ['id' => $menuId])) ?>" data-depth="1" data-max-depth="<?= (int)$maxDepth ?>">
                <?= $renderItems($items, 1) ?>
            </ul>
        <?php endif ?>
    </div>
    <?php if ($items !== []): ?>
        <div class="card-footer border-top">
            <small class="text-body-secondary">
                რიგის შესაცვლელად გადაათრიეთ <i class="icon-base bx bx-grid-vertical icon-xs"></i>-ით<?= $maxDepth > 1 ? '; ქვეპუნქტად გადასაყვანად — სხვა პუნქტის ქვეშ, მარჯვნივ' : '' ?>.
                გვერდის პუნქტი ავტომატურად იღებს გვერდის სათაურს და მისამართს ყველა ენაზე; გვერდი, რომელიც ენაზე თარგმნილი ან გამოქვეყნებული არ არის, მენიუში არ ჩანს.
            </small>
        </div>
    <?php endif ?>
</div>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= asset('admin/js/menu-editor.js') ?>"></script>
<?php $this->stop() ?>
