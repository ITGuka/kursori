<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Blocks\BlockInput;
use App\Blocks\BlockRegistry;
use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageBlock;
use Core\Http\Request;
use Core\Http\Response;

final class BlockController
{
    public function __construct(
        private readonly PageBlock $blocks,
        private readonly Page $pages,
        private readonly BlockRegistry $registry,
        private readonly BlockInput $input,
        private readonly Language $languages,
        private readonly ActivityLog $log,
    ) {
    }

    public function create(Request $request, int $id): Response
    {
        $page = $this->pages->find($id) ?? abort(404);
        $type = $request->string('type', '');
        if (!$this->registry->has($type)) {
            abort(404);
        }
        // საერთო ბლოკი — სხვა გვერდზე არსებული შიგთავსით
        $shared = !empty($this->registry->get($type)['shared']) ? $this->blocks->firstOfType($type) : null;
        return $this->form($page, null, $type, $shared['settings'] ?? [], $shared['content'] ?? [], [], 200);
    }

    public function store(Request $request, int $id): Response
    {
        $page = $this->pages->find($id) ?? abort(404);
        $type = $request->string('type', '');
        $definition = $this->registry->get($type) ?? abort(422, 'Unknown block type.');

        $result = $this->parse($request, $definition);
        if ($result['errors'] !== []) {
            return $this->form($page, null, $type, $result['settings'], $result['content'], $result['errors'], 422);
        }

        $blockId = $this->blocks->save(null, $id, $type, $result['settings'], $result['content'], $result['media']);
        if (!empty($definition['shared'])) {
            $this->blocks->syncShared($blockId);
        }
        $this->log->log('block.created', 'page', $id, ['block' => $this->registry->label($type)]);
        return redirect(route('admin.pages.edit', ['id' => $id]) . '#block-' . $blockId)->with('success', 'ბლოკი დაემატა.');
    }

    public function edit(int $id): Response
    {
        $block = $this->blocks->find($id) ?? abort(404);
        $page = $this->pages->find((int)$block['page_id']) ?? abort(404);
        return $this->form($page, $block, $block['type'], $block['settings'], $block['content'], [], 200);
    }

    public function update(Request $request, int $id): Response
    {
        $block = $this->blocks->find($id) ?? abort(404);
        $page = $this->pages->find((int)$block['page_id']) ?? abort(404);
        $definition = $this->registry->get($block['type']) ?? abort(404);

        $result = $this->parse($request, $definition);
        if ($result['errors'] !== []) {
            return $this->form($page, $block, $block['type'], $result['settings'], $result['content'], $result['errors'], 422);
        }

        $this->blocks->save($id, (int)$page['id'], $block['type'], $result['settings'], $result['content'], $result['media']);
        if (!empty($definition['shared'])) {
            $this->blocks->syncShared($id);
        }
        $this->log->log('block.updated', 'page', (int)$page['id'], ['block' => $this->registry->label($block['type'])]);

        if ($request->input('stay') === '1') {
            return redirect(route('admin.blocks.edit', ['id' => $id]))->with('success', 'ბლოკი შენახულია.');
        }
        return redirect(route('admin.pages.edit', ['id' => $page['id']]) . '#block-' . $id)->with('success', 'ბლოკი შენახულია.');
    }

    public function toggle(int $id): Response
    {
        $block = $this->blocks->find($id) ?? abort(404);
        $this->blocks->setActive($id, !$block['is_active']);
        return redirect(route('admin.pages.edit', ['id' => $block['page_id']]) . '#block-' . $id)
            ->with('success', $block['is_active'] ? 'ბლოკი დაიმალა.' : 'ბლოკი გამოჩნდა.');
    }

    public function duplicate(int $id): Response
    {
        $block = $this->blocks->find($id) ?? abort(404);
        $newId = $this->blocks->duplicate($id);
        $this->log->log('block.created', 'page', (int)$block['page_id'], ['block' => $this->registry->label($block['type'])]);
        return redirect(route('admin.pages.edit', ['id' => $block['page_id']]) . '#block-' . $newId)
            ->with('success', 'ბლოკი დაკოპირდა (დამალულია — შეამოწმეთ და გამოაჩინეთ).');
    }

    public function destroy(int $id): Response
    {
        $block = $this->blocks->find($id) ?? abort(404);
        $this->blocks->delete($id);
        $this->log->log('block.deleted', 'page', (int)$block['page_id'], ['block' => $this->registry->label($block['type'])]);
        return redirect(route('admin.pages.edit', ['id' => $block['page_id']]))->with('success', 'ბლოკი წაიშალა.');
    }

    /** AJAX: {"ids": [3, 1, 2]} */
    public function reorder(Request $request, int $id): array
    {
        $this->pages->find($id) ?? abort(404);
        $ids = array_filter(array_map('intval', (array)$request->input('ids', [])));
        $this->blocks->reorder($id, $ids);
        return ['ok' => true];
    }

    private function parse(Request $request, array $definition): array
    {
        return $this->input->parse(
            $definition['fields'],
            (array)$request->input('settings', []),
            (array)$request->input('content', []),
            array_column($this->languages->all(), 'code'),
            $this->languages->defaultCode(),
        );
    }

    private function form(array $page, ?array $block, string $type, array $settings, array $content, array $errors, int $status): Response
    {
        $default = $this->languages->defaultCode();
        $pageTitle = $this->pages->translations((int)$page['id'])[$default]['title'] ?? '#' . $page['id'];
        // საერთო ბლოკი: ყველა გვერდი, სადაც ცვლილება აისახება (ახალი ბლოკისას — მიმდინარეც)
        $sharedPages = [];
        if (!empty($this->registry->get($type)['shared'])) {
            foreach (array_unique([...$this->blocks->pageIdsOfType($type), (int)$page['id']]) as $pageId) {
                $sharedPages[] = $this->pages->translations($pageId)[$default]['title'] ?? '#' . $pageId;
            }
        }
        return view('admin/blocks/form', [
            'sharedPages' => $sharedPages,
            'title'       => $this->registry->label($type) . ' · ' . $pageTitle,
            'page'        => $page,
            'pageTitle'   => $pageTitle,
            'block'       => $block,
            'type'        => $type,
            'definition'  => $this->registry->get($type),
            'settings'    => $settings,
            'content'     => $content,
            'formErrors'  => $errors,
            'languages'   => $this->languages->all(),
            'defaultLang' => $default,
            'pageOptions' => $this->pages->options($default),
        ], $status);
    }
}
