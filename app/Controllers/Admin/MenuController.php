<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Menu;
use App\Models\Page;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Url;

final class MenuController
{
    public function __construct(
        private readonly Menu $menus,
        private readonly Page $pages,
        private readonly Language $languages,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(Request $request): Response
    {
        $locations = Menu::locations();
        $location = $request->string('location', (string)array_key_first($locations));
        if (!isset($locations[$location])) {
            abort(404);
        }
        $menuId = $this->menus->idFor($location);
        $default = $this->languages->defaultCode();

        return view('admin/menus/index', [
            'title'       => 'მენიუები',
            'locations'   => $locations,
            'location'    => $location,
            'menuId'      => $menuId,
            'maxDepth'    => (int)$locations[$location]['max_depth'],
            'items'       => $this->menus->adminTree($menuId),
            'pageTitles'  => $this->pages->options($default),
            'defaultLang' => $default,
        ]);
    }

    public function create(Request $request, int $id): Response
    {
        $menu = $this->menus->find($id) ?? abort(404);
        return $this->form($menu, null, ['type' => 'page', 'page_id' => null, 'url' => '', 'new_tab' => false, 'is_active' => true,
                                         'parent_id' => ((int)$request->query('parent', 0)) ?: null], []);
    }

    public function store(Request $request, int $id): Response
    {
        $menu = $this->menus->find($id) ?? abort(404);
        [$data, $translations, $errors] = $this->input($request, $menu, null);
        if ($errors !== []) {
            return $this->form($menu, null, $data, $translations, $errors);
        }
        $itemId = $this->menus->saveItem(null, $id, $data, $translations);
        $this->log->log('menu.item_created', 'menu', $id, ['label' => $this->label($data, $translations)]);
        return redirect(route('admin.menus.index', ['location' => $menu['location']]) . '#item-' . $itemId)->with('success', 'პუნქტი დაემატა.');
    }

    public function edit(int $id): Response
    {
        $item = $this->menus->findItem($id) ?? abort(404);
        $menu = $this->menus->find((int)$item['menu_id']) ?? abort(404);
        return $this->form($menu, $item, $item, $item['translations']);
    }

    public function update(Request $request, int $id): Response
    {
        $item = $this->menus->findItem($id) ?? abort(404);
        $menu = $this->menus->find((int)$item['menu_id']) ?? abort(404);
        [$data, $translations, $errors] = $this->input($request, $menu, $id);
        if ($errors !== []) {
            return $this->form($menu, $item, $data, $translations, $errors);
        }
        $this->menus->saveItem($id, (int)$menu['id'], $data, $translations);
        $this->log->log('menu.item_updated', 'menu', (int)$menu['id'], ['label' => $this->label($data, $translations)]);
        return redirect(route('admin.menus.index', ['location' => $menu['location']]) . '#item-' . $id)->with('success', 'პუნქტი შენახულია.');
    }

    public function toggle(int $id): Response
    {
        $item = $this->menus->findItem($id) ?? abort(404);
        $menu = $this->menus->find((int)$item['menu_id']) ?? abort(404);
        $this->menus->saveItem($id, (int)$menu['id'], ['is_active' => !$item['is_active'], 'new_tab' => (bool)$item['new_tab'],
            'parent_id' => $item['parent_id'] !== null ? (int)$item['parent_id'] : null] + $item, []);
        return redirect(route('admin.menus.index', ['location' => $menu['location']]) . '#item-' . $id)
            ->with('success', $item['is_active'] ? 'პუნქტი დაიმალა.' : 'პუნქტი გამოჩნდა.');
    }

    public function destroy(int $id): Response
    {
        $item = $this->menus->findItem($id) ?? abort(404);
        $menu = $this->menus->find((int)$item['menu_id']) ?? abort(404);
        $this->menus->deleteItem($id);
        $this->log->log('menu.item_deleted', 'menu', (int)$menu['id'], ['label' => $this->label($item, $item['translations'])]);
        return redirect(route('admin.menus.index', ['location' => $menu['location']]))->with('success', 'პუნქტი წაიშალა (ქვეპუნქტებითურთ).');
    }

    /** AJAX: {"tree": [{"id": 1, "children": [{"id": 2}]}, ...]} */
    public function reorder(Request $request, int $id): array
    {
        $menu = $this->menus->find($id) ?? abort(404);
        $maxDepth = (int)(Menu::locations()[$menu['location']]['max_depth'] ?? 1);
        $flattened = $this->menus->reorder($id, (array)$request->input('tree', []), $maxDepth);
        $this->log->log('menu.reordered', 'menu', $id, ['label' => Menu::locations()[$menu['location']]['label'] ?? $menu['location']]);
        return ['ok' => true, 'flattened' => $flattened];
    }

    /** @return array{0: array, 1: array<string, array{label: string, url: string}>, 2: array<string, string>} */
    private function input(Request $request, array $menu, ?int $itemId): array
    {
        $errors = [];
        $type = $request->input('type') === 'url' ? 'url' : 'page';
        $pageId = (int)$request->input('page_id', 0) ?: null;
        $url = trim($request->string('url', ''));
        $parentId = (int)$request->input('parent_id', 0) ?: null;
        $data = ['type' => $type, 'page_id' => $pageId, 'url' => mb_substr($url, 0, 2000), 'parent_id' => $parentId,
                 'new_tab' => $request->input('new_tab') === '1', 'is_active' => $request->input('is_active') === '1'];

        if ($type === 'page' && $this->pages->find((int)$pageId) === null) {
            $errors['page_id'] = 'აირჩიეთ გვერდი.';
        }
        if ($type === 'url' && !Url::isSafeHref($url)) {
            $errors['url'] = 'ბმული: https://..., /გვერდი, #ღუზა, mailto: ან tel:';
        }

        // მშობელი: იმავე მენიუს ზედა დონის პუნქტი (max_depth = 2), არა თავად ეს პუნქტი
        $maxDepth = (int)(Menu::locations()[$menu['location']]['max_depth'] ?? 1);
        if ($parentId !== null) {
            $parent = $this->menus->findItem($parentId);
            if ($maxDepth < 2 || $parent === null || (int)$parent['menu_id'] !== (int)$menu['id'] || $parent['parent_id'] !== null || $parentId === $itemId) {
                $errors['parent_id'] = 'არასწორი მშობელი პუნქტი.';
            } elseif ($itemId !== null && $this->menus->hasChildren($itemId)) {
                $errors['parent_id'] = 'პუნქტს ქვეპუნქტები აქვს — ის მხოლოდ ზედა დონეზე შეიძლება იყოს.';
            }
        }

        $translations = [];
        $input = (array)$request->input('t', []);
        $default = $this->languages->defaultCode();
        foreach (array_column($this->languages->all(), 'code') as $lang) {
            $label = trim(is_string($input[$lang]['label'] ?? null) ? $input[$lang]['label'] : '');
            $langUrl = trim(is_string($input[$lang]['url'] ?? null) ? $input[$lang]['url'] : '');
            $translations[$lang] = ['label' => mb_substr($label, 0, 100), 'url' => $type === 'url' ? mb_substr($langUrl, 0, 2000) : ''];
            if ($type === 'url' && $lang === $default && $label === '') {
                $errors["$lang.label"] = 'ბმულს ძირითად ენაზე წარწერა სჭირდება.';
            }
            if ($type === 'url' && $langUrl !== '' && !Url::isSafeHref($langUrl)) {
                $errors["$lang.url"] = 'არასწორი ბმული.';
            }
        }
        return [$data, $translations, $errors];
    }

    private function form(array $menu, ?array $item, array $data, array $translations, array $errors = []): Response
    {
        $location = Menu::locations()[$menu['location']] ?? ['label' => $menu['location'], 'max_depth' => 1];
        $parents = [];
        if ($location['max_depth'] >= 2) {
            foreach ($this->menus->adminTree((int)$menu['id']) as $top) {
                if ($item === null || (int)$top['id'] !== (int)$item['id']) {
                    $parents[(int)$top['id']] = $this->label($top, $top['translations']);
                }
            }
        }
        $default = $this->languages->defaultCode();

        return view('admin/menus/item', [
            'title'        => ($item === null ? 'ახალი პუნქტი' : 'პუნქტის რედაქტირება') . ' · ' . $location['label'],
            'menu'         => $menu,
            'item'         => $item,
            'data'         => $data,
            'translations' => $translations,
            'formErrors'   => $errors,
            'languages'    => $this->languages->all(),
            'defaultLang'  => $default,
            'pageOptions'  => $this->pages->options($default),
            'parents'      => $parents,
            'hasChildren'  => $item !== null && $this->menus->hasChildren((int)$item['id']),
        ], $errors === [] ? 200 : 422);
    }

    private function label(array $item, array $translations): string
    {
        $default = $this->languages->defaultCode();
        $label = (string)($translations[$default]['label'] ?? '');
        if ($label === '' && ($item['type'] ?? '') === 'page') {
            $label = (string)($this->pages->translations((int)$item['page_id'])[$default]['title'] ?? '');
        }
        return $label !== '' ? $label : (string)($item['url'] ?? '—');
    }
}
