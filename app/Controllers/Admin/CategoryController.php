<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Design;
use App\Models\Language;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Str;

/**
 * კოლექციის კატეგორიები: /admin/collections/{type}/categories (2 დონე).
 * დიზაინების (= ბიზნესის) კატეგორიებიც: /admin/collections/designs/categories.
 */
final class CategoryController
{
    private const SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private readonly Category $categories,
        private readonly Language $languages,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(string $type): Response
    {
        $definition = $this->definition($type);
        return view('admin/categories/index', [
            'title'          => 'კატეგორიები · ' . $definition['label'],
            'type'           => $type,
            'definition'     => $definition,
            'collectionType' => $type,
            'rows'           => $this->categories->tree($type, $this->languages->defaultCode()),
            'languages'      => $this->languages->all(),
        ]);
    }

    public function create(string $type): Response
    {
        $this->definition($type);
        return $this->form($type, null, ['parent_id' => null, 'sort' => 0, 'is_active' => true], []);
    }

    public function store(Request $request, string $type): Response
    {
        $this->definition($type);
        [$data, $translations, $errors] = $this->input($request, $type, null);
        if ($errors !== []) {
            return $this->form($type, null, $data, $translations, $errors);
        }
        $id = $this->categories->save(null, $type, $data, $translations);
        $this->log->log('category.created', $type, $id, ['title' => $translations[$this->languages->defaultCode()]['name']]);
        return redirect(route('admin.categories.index', ['type' => $type]))->with('success', 'კატეგორია დაემატა.');
    }

    public function edit(int $id): Response
    {
        $category = $this->categories->find($id) ?? abort(404);
        $this->definition($category['collection']);
        // დიზაინების ზედა დონე = პაკეტი (ფასით) — საკუთარი ფორმა აქვს
        if ($category['collection'] === Design::CATEGORIES && $category['parent_id'] === null) {
            return redirect(route('admin.packages.edit', ['id' => $id]));
        }
        return $this->form($category['collection'], $category, $category, $this->categories->translations($id));
    }

    public function update(Request $request, int $id): Response
    {
        $category = $this->categories->find($id) ?? abort(404);
        $type = $category['collection'];
        [$data, $translations, $errors] = $this->input($request, $type, $id);
        if ($errors !== []) {
            return $this->form($type, $category, $data, $translations, $errors);
        }
        $this->categories->save($id, $type, $data, $translations);
        $this->log->log('category.updated', $type, $id, ['title' => $translations[$this->languages->defaultCode()]['name']]);
        return redirect(route('admin.categories.index', ['type' => $type]))->with('success', 'კატეგორია შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $category = $this->categories->find($id) ?? abort(404);
        $name = $this->categories->translations($id)[$this->languages->defaultCode()]['name'] ?? '#' . $id;
        if ($category['collection'] === Design::CATEGORIES && $this->categories->hasChildren($id)) {
            return redirect(route('admin.categories.index', ['type' => Design::CATEGORIES]))
                ->with('error', "პაკეტს „{$name}“ დარგები აქვს — ჯერ გადაიტანეთ ისინი სხვა პაკეტში.");
        }
        $this->categories->delete($id);
        $this->log->log('category.deleted', $category['collection'], $id, ['title' => $name]);
        return redirect(route('admin.categories.index', ['type' => $category['collection']]))
            ->with('success', "კატეგორია „{$name}“ წაიშალა. მისი ჩანაწერები კატეგორიის გარეშე დარჩა.");
    }

    /** კოლექციის (categories = true) ან დიზაინების განსაზღვრება; index — ჩანაწერების სიის URL (?category= ფილტრით) */
    private function definition(string $type): array
    {
        if ($type === Design::CATEGORIES) {
            return ['label' => 'დიზაინები', 'categories' => true, 'index' => route('admin.designs.index'), 'packages' => route('admin.packages.index')];
        }
        $definition = Collection::type($type) ?? abort(404);
        return $definition['categories'] ? $definition + ['index' => route('admin.collections.index', ['type' => $type])] : abort(404);
    }

    /** @return array{0: array, 1: array<string, array{name: string, slug: string}>, 2: array<string, string>} */
    private function input(Request $request, string $type, ?int $id): array
    {
        $errors = [];
        $parentId = (int)$request->input('parent_id', 0) ?: null;
        $data = ['parent_id' => $parentId, 'sort' => (int)$request->input('sort', 0), 'is_active' => $request->input('is_active') === '1'];

        if ($parentId !== null) {
            $parent = $this->categories->find($parentId);
            if ($parent === null || $parent['collection'] !== $type || $parent['parent_id'] !== null || $parentId === $id) {
                $errors['parent_id'] = 'არასწორი მშობელი კატეგორია.';
            } elseif ($id !== null && $this->categories->hasChildren($id)) {
                $errors['parent_id'] = 'კატეგორიას ქვეკატეგორიები აქვს — ის მხოლოდ ზედა დონეზე შეიძლება იყოს.';
            }
        } elseif ($type === Design::CATEGORIES) {
            // დიზაინების დარგი ყოველთვის პაკეტშია; ახალი პაკეტი — /admin/packages
            $errors['parent_id'] = 'აირჩიეთ პაკეტი.';
        }

        $translations = [];
        $input = (array)$request->input('t', []);
        $default = $this->languages->defaultCode();
        foreach (array_column($this->languages->all(), 'code') as $lang) {
            $name = trim(is_string($input[$lang]['name'] ?? null) ? $input[$lang]['name'] : '');
            $slug = trim(is_string($input[$lang]['slug'] ?? null) ? $input[$lang]['slug'] : '');
            $slug = $slug === '' ? Str::slug($name) : strtolower($slug);
            $translations[$lang] = ['name' => mb_substr($name, 0, 150), 'slug' => $slug];
            if ($name === '') {
                if ($lang === $default) {
                    $errors["$lang.name"] = 'ძირითად ენაზე სახელი სავალდებულოა.';
                }
                continue;
            }
            if ($slug === '' || !preg_match(self::SLUG, $slug)) {
                $errors["$lang.slug"] = 'მხოლოდ ლათინური პატარა ასოები, ციფრები და „-“.';
            } elseif ($this->categories->slugExists($type, $lang, $slug, $id)) {
                $errors["$lang.slug"] = 'ეს მისამართი უკვე დაკავებულია.';
            }
        }
        return [$data, $translations, $errors];
    }

    private function form(string $type, ?array $category, array $data, array $translations, array $errors = []): Response
    {
        $definition = $this->definition($type);
        $default = $this->languages->defaultCode();
        return view('admin/categories/form', [
            'title'          => ($category === null ? 'ახალი კატეგორია' : 'კატეგორია') . ' · ' . $definition['label'],
            'type'           => $type,
            'definition'     => $definition,
            'collectionType' => $type,
            'category'       => $category,
            'data'           => $data,
            'translations'   => $translations,
            'formErrors'     => $errors,
            'languages'      => $this->languages->all(),
            'defaultLang'    => $default,
            'parents'        => $this->categories->options($type, $default, true, $category !== null ? (int)$category['id'] : null),
            'hasChildren'    => $category !== null && $this->categories->hasChildren((int)$category['id']),
            'isDesigns'      => $type === Design::CATEGORIES,
        ], $errors === [] ? 200 : 422);
    }
}
