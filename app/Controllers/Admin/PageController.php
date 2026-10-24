<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Blocks\BlockRegistry;
use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Redirect;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Str;

final class PageController
{
    private const SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private readonly Page $pages,
        private readonly PageBlock $blocks,
        private readonly BlockRegistry $registry,
        private readonly Language $languages,
        private readonly Media $media,
        private readonly ActivityLog $log,
        private readonly Redirect $redirects,
    ) {
    }

    public function index(): Response
    {
        return view('admin/pages/index', [
            'title'     => 'გვერდები',
            'rows'      => $this->pages->tree($this->languages->defaultCode()),
            'languages' => $this->languages->all(),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, [], []);
    }

    public function store(Request $request): Response
    {
        [$data, $translations, $errors] = $this->input($request, null);
        if ($errors !== []) {
            return $this->form(null, $data, $translations, $errors);
        }

        $id = $this->pages->save(null, $data, $translations);
        $this->log->log('page.created', 'page', $id, ['title' => $this->title($translations)]);
        return redirect(route('admin.pages.edit', ['id' => $id]))->with('success', 'გვერდი შეიქმნა. ახლა დაამატეთ ბლოკები.');
    }

    public function edit(int $id): Response
    {
        $page = $this->pages->find($id) ?? abort(404);
        return $this->form($page, $page, $this->pages->translations($id));
    }

    public function update(Request $request, int $id): Response
    {
        $page = $this->pages->find($id) ?? abort(404);
        [$data, $translations, $errors] = $this->input($request, $id);
        if ($errors !== []) {
            return $this->form($page, $data, $translations, $errors);
        }

        $old = $this->pages->translations($id);
        $this->pages->save($id, $data, $translations);
        // გამოქვეყნებული გვერდის slug შეიცვალა → ძველი მისამართიდან 301 (ბმულები და საძიებო სისტემები არ იკარგება)
        if ($page['status'] === 'published' && !$page['is_home'] && !$data['is_home']) {
            foreach ($translations as $lang => $t) {
                $oldSlug = $old[$lang]['slug'] ?? null;
                if ($oldSlug !== null && $t['title'] !== '' && $oldSlug !== $t['slug']) {
                    $this->redirects->addAuto("/$lang/$oldSlug", "/$lang/{$t['slug']}");
                }
            }
        }
        $this->log->log('page.updated', 'page', $id, ['title' => $this->title($translations)]);
        return redirect(route('admin.pages.edit', ['id' => $id]))->with('success', 'გვერდი შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $page = $this->pages->find($id) ?? abort(404);
        if ($page['is_home']) {
            return back(route('admin.pages.index'))->with('error', 'მთავარ გვერდს ვერ წაშლით. ჯერ სხვა გვერდი მონიშნეთ მთავარად.');
        }
        $title = $this->title($this->pages->translations($id));
        $this->pages->delete($id);
        $this->log->log('page.deleted', 'page', $id, ['title' => $title]);
        return redirect(route('admin.pages.index'))->with('success', "გვერდი „{$title}“ წაიშალა.");
    }

    private function form(?array $page, array $data, array $translations, array $errors = []): Response
    {
        $default = $this->languages->defaultCode();
        $blocks = [];
        if ($page !== null) {
            foreach ($this->blocks->forPage((int)$page['id']) as $block) {
                $block['label'] = $this->registry->label($block['type']);
                $block['icon'] = $this->registry->get($block['type'])['icon'] ?? 'fa fa-square';
                $block['summary'] = self::summary($block, $default);
                $blocks[] = $block;
            }
        }

        return view('admin/pages/form', [
            'title'        => $page === null ? 'ახალი გვერდი' : 'გვერდი: ' . $this->title($this->pages->translations((int)$page['id'])),
            'page'         => $page,
            'data'         => $data + ['parent_id' => null, 'status' => 'draft', 'is_home' => 0, 'sort' => 0, 'og_image_id' => null],
            'translations' => $translations,
            'formErrors'   => $errors,
            'languages'    => $this->languages->all(),
            'defaultLang'  => $default,
            'parents'      => $this->pages->options($default, $page !== null ? (int)$page['id'] : null),
            'blocks'       => $blocks,
            'blockTypes'   => $this->registry->all(),
            'viewUrl'      => $page !== null ? $this->pages->url((int)$page['id'], $default, true) : '',
        ], $errors === [] ? 200 : 422);
    }

    /** @return array{0: array, 1: array<string, array>, 2: array<string, string>} */
    private function input(Request $request, ?int $id): array
    {
        $errors = [];
        $parentId = (int)$request->input('parent_id', 0) ?: null;
        $ogImage = trim((string)$request->input('og_image_id', ''));
        $data = [
            'parent_id'   => $parentId,
            'status'      => $request->input('status') === 'published' ? 'published' : 'draft',
            'is_home'     => $request->input('is_home') === '1',
            'sort'        => (int)$request->input('sort', 0),
            'og_image_id' => ctype_digit($ogImage) ? (int)$ogImage : null,
        ];

        if ($parentId !== null && ($this->pages->find($parentId) === null || ($id !== null && $this->isDescendant($parentId, $id)))) {
            $errors['parent_id'] = 'არასწორი მშობელი გვერდი (გვერდი თავის თავში ან შვილში ვერ ჩაჯდება).';
        }
        if ($data['og_image_id'] !== null && (($m = $this->media->find($data['og_image_id'])) === null || !Media::isImage($m))) {
            $errors['og_image_id'] = 'აირჩიეთ სურათი მედია ბიბლიოთეკიდან.';
        }

        $translations = [];
        $input = (array)$request->input('t', []);
        $default = $this->languages->defaultCode();
        foreach (array_column($this->languages->all(), 'code') as $lang) {
            $row = is_array($input[$lang] ?? null) ? $input[$lang] : [];
            $title = trim(is_string($row['title'] ?? null) ? $row['title'] : '');
            $slug = trim(is_string($row['slug'] ?? null) ? $row['slug'] : '');
            $slug = $slug === '' ? Str::slug($title) : strtolower($slug);
            $translations[$lang] = [
                'title'            => mb_substr($title, 0, 190),
                'slug'             => $slug,
                'meta_title'       => mb_substr(trim(is_string($row['meta_title'] ?? null) ? $row['meta_title'] : ''), 0, 190),
                'meta_description' => mb_substr(trim(is_string($row['meta_description'] ?? null) ? $row['meta_description'] : ''), 0, 300),
            ];

            if ($title === '') {
                if ($lang === $default) {
                    $errors["$lang.title"] = 'ძირითად ენაზე სათაური სავალდებულოა.';
                }
                continue;
            }
            if ($slug === '' || !preg_match(self::SLUG, $slug) || strlen($slug) > 150) {
                $errors["$lang.slug"] = 'მხოლოდ ლათინური პატარა ასოები, ციფრები და „-“.';
            } elseif ($this->pages->slugExists($lang, $slug, $id)) {
                $errors["$lang.slug"] = 'ეს მისამართი ამ ენაზე უკვე დაკავებულია.';
            }
        }
        return [$data, $translations, $errors];
    }

    private function isDescendant(int $candidate, int $pageId): bool
    {
        for ($current = $candidate, $guard = 0; $current !== null && $guard < 20; $guard++) {
            if ($current === $pageId) {
                return true;
            }
            $current = ($parent = $this->pages->find($current)) !== null && $parent['parent_id'] !== null ? (int)$parent['parent_id'] : null;
        }
        return false;
    }

    private function title(array $translations): string
    {
        $default = $this->languages->defaultCode();
        foreach ([$translations[$default] ?? null, ...array_values($translations)] as $t) {
            if (is_array($t) && ($t['title'] ?? '') !== '') {
                return $t['title'];
            }
        }
        return '—';
    }

    /** ბლოკის მოკლე აღწერა სიაში: პირველი შევსებული სათაური/ტექსტი ძირითად ენაზე */
    private static function summary(array $block, string $lang): string
    {
        $content = $block['content'][$lang] ?? [];
        foreach (['title', 'subtitle', 'text', 'body'] as $field) {
            if (is_string($content[$field] ?? null) && trim(strip_tags($content[$field])) !== '') {
                return Str::limit($content[$field], 80);
            }
        }
        foreach ($content as $value) {
            if (is_array($value) && $value !== []) {
                $first = reset($value);
                return count($value) . ' ელემენტი' . (is_array($first) && ($first['title'] ?? $first['name'] ?? '') !== '' ? ': ' . Str::limit($first['title'] ?? $first['name'], 60) : '');
            }
        }
        return '';
    }
}
