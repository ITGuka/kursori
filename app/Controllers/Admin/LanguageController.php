<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Translation;
use Core\Http\Request;
use Core\Http\Response;
use Core\Settings\Settings;
use Core\Support\Validator;

final class LanguageController
{
    public function __construct(
        private readonly Language $languages,
        private readonly Translation $translations,
        private readonly Settings $settings,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(): Response
    {
        return view('admin/languages/index', ['title' => 'ენები', 'languages' => $this->languages->all()]);
    }

    public function create(): Response
    {
        return view('admin/languages/form', ['title' => 'ახალი ენა', 'language' => null]);
    }

    public function store(Request $request): Response
    {
        $data = $this->input($request) + ['code' => strtolower(trim($request->string('code', '')))];
        $validator = Validator::make($data, $this->rules() + [
            'code' => ['required', 'regex:/^[a-z]{2}$/', 'unique:languages,code'],
        ], ['code' => 'კოდი']);
        if ($validator->fails()) {
            return back(route('admin.languages.create'))->withErrors($validator->errors())->withInput($this->old($data));
        }

        $id = $this->languages->create($data);
        $this->log->log('language.created', 'language', $id, ['code' => $data['code']]);
        return redirect(route('admin.languages.index'))->with('success', 'ენა დაემატა. თარგმანები შეავსეთ „თარგმანებში“.');
    }

    public function edit(int $id): Response
    {
        $language = $this->languages->find($id) ?? abort(404);
        return view('admin/languages/form', ['title' => 'ენა: ' . $language['native_name'], 'language' => $language]);
    }

    public function update(Request $request, int $id): Response
    {
        $language = $this->languages->find($id) ?? abort(404);
        $data = $this->input($request);
        // ძირითადი ენა ძირითადად რჩება, სანამ სხვას არ მიენიჭება
        $data['is_default'] = $data['is_default'] || $language['is_default'];

        $errors = Validator::make($data, $this->rules())->errors();
        if ($language['is_default'] && !$data['is_active']) {
            $errors['is_active'] = 'ძირითად ენას ვერ გათიშავთ. ჯერ სხვა ენა გახადეთ ძირითადი.';
        }
        if ($errors !== []) {
            return back(route('admin.languages.edit', ['id' => $id]))->withErrors($errors)->withInput($this->old($data));
        }

        $this->languages->update($id, $data);
        $this->log->log('language.updated', 'language', $id, ['code' => $language['code']]);
        return redirect(route('admin.languages.index'))->with('success', 'ცვლილებები შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $language = $this->languages->find($id) ?? abort(404);
        if ($language['is_default']) {
            return back(route('admin.languages.index'))->with('error', 'ძირითად ენას ვერ წაშლით.');
        }

        $this->languages->delete($id, $language['code']);
        $this->translations->forgetCache();
        $this->settings->forgetCache();
        $this->log->log('language.deleted', 'language', $id, ['code' => $language['code']]);
        return redirect(route('admin.languages.index'))->with('success', "ენა „{$language['native_name']}“ და მისი თარგმანები წაიშალა.");
    }

    private function rules(): array
    {
        return [
            'name'        => 'required|max:50',
            'native_name' => 'required|max:50',
            'sort'        => 'required|integer',
        ];
    }

    private function input(Request $request): array
    {
        return [
            'name'        => trim($request->string('name', '')),
            'native_name' => trim($request->string('native_name', '')),
            'sort'        => trim($request->string('sort', '0')),
            'is_active'   => $request->input('is_active') === '1',
            'is_default'  => $request->input('is_default') === '1',
        ];
    }

    private function old(array $data): array
    {
        return ['is_active' => $data['is_active'] ? '1' : '0', 'is_default' => $data['is_default'] ? '1' : '0'] + $data;
    }
}
