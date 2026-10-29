<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Design;
use App\Models\Language;
use App\Models\Package;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Str;

/**
 * ფასების პაკეტები (= დიზაინების ზედა დონის კატეგორიები): /admin/packages.
 * ფასების ბლოკი და შეკვეთის ფორმა მათ ბაზიდან კითხულობს; დარგები — ქვეკატეგორიები (CategoryController).
 */
final class PackageController
{
    private const SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const CODE = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private readonly Package $packages,
        private readonly Category $categories,
        private readonly Language $languages,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(): Response
    {
        return view('admin/packages/index', [
            'title'          => 'პაკეტები',
            'rows'           => $this->packages->all($this->languages->defaultCode()),
            'languages'      => $this->languages->all(),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, ['code' => '', 'sort' => 0, 'is_active' => true], []);
    }

    public function store(Request $request): Response
    {
        [$data, $translations, $errors] = $this->input($request, null);
        if ($errors !== []) {
            return $this->form(null, $data, $translations, $errors);
        }
        $id = $this->packages->save(null, $data, $translations);
        $this->log->log('package.created', 'package', $id, ['title' => $translations[$this->languages->defaultCode()]['name']]);
        return redirect(route('admin.packages.index'))->with('success', 'პაკეტი დაემატა.');
    }

    public function edit(int $id): Response
    {
        $package = $this->packages->find($id) ?? abort(404);
        return $this->form($package, $package, $package['translations']);
    }

    public function update(Request $request, int $id): Response
    {
        $package = $this->packages->find($id) ?? abort(404);
        [$data, $translations, $errors] = $this->input($request, $id);
        if ($errors !== []) {
            return $this->form($package, $data, $translations, $errors);
        }
        $this->packages->save($id, $data, $translations);
        $this->log->log('package.updated', 'package', $id, ['title' => $translations[$this->languages->defaultCode()]['name']]);
        return redirect(route('admin.packages.index'))->with('success', 'პაკეტი შენახულია.');
    }

    /** დარგებიანი პაკეტი არ იშლება — ქვეკატეგორიები სხვა პაკეტში უნდა გადავიდეს */
    public function destroy(int $id): Response
    {
        $package = $this->packages->find($id) ?? abort(404);
        $name = $package['translations'][$this->languages->defaultCode()]['name'] ?? '#' . $id;
        if ($this->categories->hasChildren($id)) {
            return redirect(route('admin.packages.index'))->with('error', "„{$name}“-ს დარგები აქვს — ჯერ გადაიტანეთ ისინი სხვა პაკეტში.");
        }
        $this->categories->delete($id);
        $this->log->log('package.deleted', 'package', $id, ['title' => $name]);
        return redirect(route('admin.packages.index'))->with('success', "პაკეტი „{$name}“ წაიშალა. მისი დიზაინები პაკეტის გარეშე დარჩა.");
    }

    /** @return array{0: array, 1: array<string, array>, 2: array<string, string>} */
    private function input(Request $request, ?int $id): array
    {
        $errors = [];
        $code = strtolower(trim($request->string('code', '')));
        $data = ['code' => $code, 'sort' => (int)$request->input('sort', 0), 'is_active' => $request->input('is_active') === '1'];
        if ($code === '' || strlen($code) > 30 || !preg_match(self::CODE, $code)) {
            $errors['code'] = 'სავალდებულოა: ლათინური პატარა ასოები, ციფრები და „-“ (30 სიმბოლომდე).';
        } elseif ($this->packages->codeExists($code, $id)) {
            $errors['code'] = 'ეს კოდი უკვე დაკავებულია.';
        }

        $translations = [];
        $input = (array)$request->input('t', []);
        $default = $this->languages->defaultCode();
        $text = fn (array $row, string $name) => trim(is_string($row[$name] ?? null) ? str_replace("\r\n", "\n", $row[$name]) : '');
        foreach (array_column($this->languages->all(), 'code') as $lang) {
            $row = (array)($input[$lang] ?? []);
            $name = $text($row, 'name');
            $slug = $text($row, 'slug');
            $slug = $slug === '' ? Str::slug($name) : strtolower($slug);
            $translations[$lang] = ['name' => mb_substr($name, 0, 150), 'slug' => $slug];
            foreach (Package::FIELDS as $field => $max) {
                $translations[$lang][$field] = mb_substr($text($row, $field), 0, $max);
            }
            if ($name === '') {
                if ($lang === $default) {
                    $errors["$lang.name"] = 'ძირითად ენაზე სახელი სავალდებულოა.';
                }
                continue;
            }
            if ($slug === '' || !preg_match(self::SLUG, $slug)) {
                $errors["$lang.slug"] = 'მხოლოდ ლათინური პატარა ასოები, ციფრები და „-“.';
            } elseif ($this->categories->slugExists(Design::CATEGORIES, $lang, $slug, $id)) {
                $errors["$lang.slug"] = 'ეს მისამართი უკვე დაკავებულია.';
            }
        }
        return [$data, $translations, $errors];
    }

    private function form(?array $package, array $data, array $translations, array $errors = []): Response
    {
        return view('admin/packages/form', [
            'title'          => ($package === null ? 'ახალი პაკეტი' : 'პაკეტი') . ' · დიზაინები',
            'package'        => $package,
            'data'           => $data,
            'translations'   => $translations,
            'formErrors'     => $errors,
            'languages'      => $this->languages->all(),
            'defaultLang'    => $this->languages->defaultCode(),
        ], $errors === [] ? 200 : 422);
    }
}
