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
        $target = $item['type'] === 'page' ? '<i class="fa fa-file-alt me-1"></i>გვერდი' : '<i class="fa fa-link me-1"></i>' . e(mb_strimwidth((string)$item['url'], 0, 50, '…'));
        $html .= '<li class="menu-node" data-id="' . (int)$item['id'] . '" id="item-' . (int)$item['id'] . '">'
            . '<div class="block-row' . ($item['is_active'] ? '' : ' is-hidden') . '">'
            . '<span class="block-handle" title="გადაადგილება"><i class="fa fa-grip-vertical"></i></span>'
            . '<div class="block-info"><a class="fw-semibold" href="' . e(route('admin.menus.items.edit', ['id' => $item['id']])) . '">' . e($label($item)) . '</a>'
            . (!$item['is_active'] ? ' <span class="badge bg-secondary ms-1">დამალული</span>' : '')
            . ($item['new_tab'] ? ' <span class="badge bg-light text-muted border ms-1" title="ახალ ფანჯარაში"><i class="fa fa-external-link-alt"></i></span>' : '')
            . '<div class="small text-muted text-truncate">' . $target . '</div></div>'
            . '<div class="block-actions">'
            . ($depth < $maxDepth ? '<a href="' . e(route('admin.menus.items.create', ['id' => $menuId, 'parent' => $item['id']])) . '" class="btn btn-sm btn-outline-secondary" title="ქვეპუნქტის დამატება"><i class="fa fa-plus"></i></a>' : '')
            . '<form method="post" action="' . e(route('admin.menus.items.toggle', ['id' => $item['id']])) . '">' . csrf_field()
            . '<button class="btn btn-sm btn-outline-secondary" title="' . ($item['is_active'] ? 'დამალვა' : 'გამოჩენა') . '"><i class="fa ' . ($item['is_active'] ? 'fa-eye' : 'fa-eye-slash') . '"></i></button></form>'
            . '<a href="' . e(route('admin.menus.items.edit', ['id' => $item['id']])) . '" class="btn btn-sm btn-outline-primary" title="რედაქტირება"><i class="fa fa-pen"></i></a>'
            . '<form method="post" action="' . e(route('admin.menus.items.destroy', ['id' => $item['id']])) . '" data-confirm="წავშალოთ პუნქტი „' . e($label($item)) . '“' . ($item['children'] !== [] ? ' ქვეპუნქტებითურთ' : '') . '?">'
            . csrf_field() . '<input type="hidden" name="_method" value="DELETE"><button class="btn btn-sm btn-outline-danger" title="წაშლა"><i class="fa fa-trash"></i></button></form>'
            . '</div></div>'
            . ($depth < $maxDepth ? '<ul class="menu-list menu-children list-unstyled" data-depth="' . ($depth + 1) . '">' . $renderItems($item['children'], $depth + 1) . '</ul>' : '')
            . '</li>';
    }
    return $html;
};
?>

<ul class="nav nav-tabs mb-0">
    <?php foreach ($locations as $key => $definition): ?>
        <li class="nav-item">
            <a class="nav-link<?= $key === $location ? ' active' : '' ?>" href="<?= e(route('admin.menus.index', ['location' => $key])) ?>"><?= e($definition['label']) ?></a>
        </li>
    <?php endforeach ?>
</ul>

<div class="bg-light rounded-bottom p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0"><?= e($locations[$location]['label']) ?></h6>
        <a href="<?= e(route('admin.menus.items.create', ['id' => $menuId])) ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i>პუნქტის დამატება</a>
    </div>

    <?php if ($items === []): ?>
        <p class="text-muted mb-0">მენიუ ცარიელია.<?= $location === 'header' ? ' სანამ პუნქტებს დაამატებთ, საიტზე ჩანს გამოქვეყნებული გვერდები.' : '' ?></p>
    <?php else: ?>
        <ul class="menu-list list-unstyled mb-0" data-menu-sort="<?= e(route('admin.menus.reorder', ['id' => $menuId])) ?>" data-depth="1" data-max-depth="<?= (int)$maxDepth ?>">
            <?= $renderItems($items, 1) ?>
        </ul>
        <p class="small text-muted mt-3 mb-0">
            რიგის შესაცვლელად გადაათრიეთ <i class="fa fa-grip-vertical"></i>-ით<?= $maxDepth > 1 ? '; ქვეპუნქტად გადასაყვანად — სხვა პუნქტის ქვეშ, მარჯვნივ' : '' ?>.
            გვერდის პუნქტი ავტომატურად იღებს გვერდის სათაურს და მისამართს ყველა ენაზე; გვერდი, რომელიც ენაზე თარგმნილი ან გამოქვეყნებული არ არის, მენიუში არ ჩანს.
        </p>
    <?php endif ?>
</div>

<?php $this->start('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= asset('admin/js/menu-editor.js') ?>"></script>
<?php $this->stop() ?>
