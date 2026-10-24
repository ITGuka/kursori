<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Blocks\BlockInput;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Language;
use App\Models\Media;
use App\Models\Page;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Str;

/**
 * კოლექციის ჩანაწერები: /admin/collections/{type}. ფორმის დამატებითი ველები — config/collections.php (ბლოკების ფორმატი).
 */
final class CollectionController
{
    private const PER_PAGE = 50;
    private const SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private readonly Collection $items,
        private readonly Category $categories,
        private readonly BlockInput $input,
        private readonly Language $languages,
        private readonly Media $media,
        private readonly Page $pages,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(Request $request, string $type): Response
    {
        $definition = $this->definition($type);
        $default = $this->languages->defaultCode();
        $filters = ['q' => trim((string)$request->query('q', '')), 'category' => (int)$request->query('category', 0)];
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->items->paginate($type, $filters, $default, $page, self::PER_PAGE);

        return view('admin/collections/index', [
            'title'          => $definition['label'],
            'type'           => $type,
            'definition'     => $definition,
            'collectionType' => $type,
            'rows'           => $result['rows'],
            'total'          => $result['total'],
            'page'           => $page,
            'pages'          => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'offset'         => ($page - 1) * self::PER_PAGE,
            'filters'        => $filters,
            'categoryOptions'=> $definition['categories'] ? $this->categories->options($type, $default) : [],
            'languages'      => $this->languages->all(),
            'defaultLang'    => $default,
            // რიგის შეცვლა — მხოლოდ ფილტრის გარეშე (სხვაგვარად ნაწილობრივი სია არეულად დალაგდება)
            'sortable'       => $filters['q'] === '' && $filters['category'] === 0,
        ]);
    }

    public function create(string $type): Response
    {
        $this->definition($type);
        return $this->form($type, null, ['category_id' => null, 'image_id' => null, 'is_active' => true], [], [], []);
    }

    public function store(Request $request, string $type): Response
    {
        $definition = $this->definition($type);
        [$core, $settings, $translations, $content, $media, $errors] = $this->parse($request, $type, $definition, null);
        if ($errors !== []) {
            return $this->form($type, null, $core, $settings, $translations, $content, $errors);
        }
        $id = $this->items->save(null, $type, $core, $settings, $translations, $content, $media);
        $this->log->log('collection.created', $type, $id, ['title' => $this->title($translations)]);
        return redirect(route('admin.collections.index', ['type' => $type]))->with('success', $definition['singular'] . ' დაემატა.');
    }

    public function edit(int $id): Response
    {
        $item = $this->items->find($id) ?? abort(404);
        $this->definition($item['collection']);
        $content = array_map(fn ($t) => $t['content'], $item['translations']);
        return $this->form($item['collection'], $item, $item, $item['settings'], $item['translations'], $content, []);
    }

    public function update(Request $request, int $id): Response
    {
        $item = $this->items->find($id) ?? abort(404);
        $type = $item['collection'];
        $definition = $this->definition($type);
        [$core, $settings, $translations, $content, $media, $errors] = $this->parse($request, $type, $definition, $id);
        if ($errors !== []) {
            return $this->form($type, $item, $core, $settings, $translations, $content, $errors);
        }
        $this->items->save($id, $type, $core, $settings, $translations, $content, $media);
        $this->log->log('collection.updated', $type, $id, ['title' => $this->title($translations)]);
        if ($request->input('stay') === '1') {
            return redirect(route('admin.collections.edit', ['id' => $id]))->with('success', 'შენახულია.');
        }
        return redirect(route('admin.collections.index', ['type' => $type]) . '#item-' . $id)->with('success', 'შენახულია.');
    }

    public function toggle(int $id): Response
    {
        $item = $this->items->find($id) ?? abort(404);
        $this->items->setActive($id, !$item['is_active']);
        return back(route('admin.collections.index', ['type' => $item['collection']]))
            ->with('success', $item['is_active'] ? 'დაიმალა.' : 'გამოჩნდა.');
    }

    public function destroy(int $id): Response
    {
        $item = $this->items->find($id) ?? abort(404);
        $title = $this->title($item['translations']);
        $this->items->delete($id);
        $this->log->log('collection.deleted', $item['collection'], $id, ['title' => $title]);
        return redirect(route('admin.collections.index', ['type' => $item['collection']]))->with('success', "„{$title}“ წაიშალა.");
    }

    /** AJAX: {"ids": [..], "offset": 0} — გვერდზე ნაჩვენები ჩანაწერების ახალი რიგი */
    public function reorder(Request $request, string $type): array
    {
        $this->definition($type);
        $this->items->reorder($type, array_map('intval', (array)$request->input('ids', [])), max(0, (int)$request->input('offset', 0)));
        return ['ok' => true];
    }

    private function definition(string $type): array
    {
        return Collection::type($type) ?? abort(404);
    }

    /** @return array{0: array, 1: array, 2: array, 3: array, 4: list<int>, 5: array<string, string>} */
    private function parse(Request $request, string $type, array $definition, ?int $id): array
    {
        $default = $this->languages->defaultCode();
        $codes = array_column($this->languages->all(), 'code');
        $errors = [];

        $categoryId = (int)$request->input('category_id', 0) ?: null;
        $imageId = trim((string)$request->input('image_id', ''));
        $core = [
            'category_id' => $definition['categories'] ? $categoryId : null,
            'image_id'    => ctype_digit($imageId) ? (int)$imageId : null,
            'is_active'   => $request->input('is_active') === '1',
        ];
        if ($core['category_id'] !== null && (($category = $this->categories->find($core['category_id'])) === null || $category['collection'] !== $type)) {
            $errors['category_id'] = 'არასწორი კატეგორია.';
        }
        if ($core['image_id'] !== null && (($m = $this->media->find($core['image_id'])) === null || !Media::isImage($m))) {
            $errors['image_id'] = 'აირჩიეთ სურათი მედია ბიბლიოთეკიდან.';
        }

        $translations = [];
        $input = (array)$request->input('t', []);
        foreach ($codes as $lang) {
            $title = trim(is_string($input[$lang]['title'] ?? null) ? $input[$lang]['title'] : '');
            $slug = trim(is_string($input[$lang]['slug'] ?? null) ? $input[$lang]['slug'] : '');
            $slug = $slug === '' ? Str::slug($title) : strtolower($slug);
            $translations[$lang] = ['title' => mb_substr($title, 0, 190), 'slug' => $slug];
            if ($title === '') {
                if ($lang === $default) {
                    $errors["t.$lang.title"] = 'ძირითად ენაზე სავალდებულოა.';
                }
                continue;
            }
            if (!$definition['public']) {
                // საჯარო გვერდი არ აქვს — slug შიდაა: დამთხვევისას სუფიქსი (ანა, ანა-2 ...), შეცდომის ნაცვლად
                $base = $slug !== '' && preg_match(self::SLUG, $slug) ? substr($slug, 0, 140) : 'item';
                for ($n = 2, $slug = $base; $this->items->slugExists($type, $lang, $slug, $id); $n++) {
                    $slug = "$base-$n";
                }
                $translations[$lang]['slug'] = $slug;
            } elseif ($slug === '' || !preg_match(self::SLUG, $slug) || strlen($slug) > 150) {
                $errors["t.$lang.slug"] = 'მხოლოდ ლათინური პატარა ასოები, ციფრები და „-“.';
            } elseif ($this->items->slugExists($type, $lang, $slug, $id)) {
                $errors["t.$lang.slug"] = 'ეს მისამართი ამ კოლექციაში უკვე დაკავებულია.';
            }
        }

        $result = $this->input->parse($definition['fields'], (array)$request->input('settings', []), (array)$request->input('content', []), $codes, $default);
        // თარგმანი, რომლის სათაურიც ცარიელია, არ ინახება — ამ ენის ველების შეცდომები არ ითვლება
        foreach ($result['errors'] as $path => $message) {
            $lang = substr($path, strrpos($path, '.') + 1);
            if (!in_array($lang, $codes, true) || $translations[$lang]['title'] !== '' || $lang === $default) {
                $errors[$path] = $message;
            }
        }
        return [$core, $result['settings'], $translations, $result['content'], $result['media'], $errors];
    }

    private function form(string $type, ?array $item, array $core, array $settings, array $translations, array $content, array $errors = []): Response
    {
        $definition = Collection::type($type);
        $default = $this->languages->defaultCode();
        return view('admin/collections/form', [
            'title'          => ($item === null ? 'ახალი: ' . $definition['singular'] : $this->title($translations)) . ' · ' . $definition['label'],
            'type'           => $type,
            'definition'     => $definition,
            'collectionType' => $type,
            'item'           => $item,
            'core'           => $core,
            'settings'       => $settings,
            'translations'   => $translations,
            'content'        => $content,
            'formErrors'     => $errors,
            'languages'      => $this->languages->all(),
            'defaultLang'    => $default,
            'categoryOptions'=> $definition['categories'] ? $this->categories->options($type, $default) : [],
            'pageOptions'    => $this->pages->options($default),
            'publicUrl'      => $item !== null && $item['is_active'] ? $this->items->url($item, $default, $default) : '',
            'routeBase'      => fn (string $lang) => Collection::routeSlug($type, $lang, $default),
        ], $errors === [] ? 200 : 422);
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
}
