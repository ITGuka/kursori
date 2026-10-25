<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Translation;
use Core\Database\DB;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Validator;

final class TranslationController
{
    private const PER_PAGE = 50;
    private const MAX_LENGTH = 5000;

    public function __construct(
        private readonly Translation $translations,
        private readonly Language $languages,
        private readonly ActivityLog $log,
        private readonly DB $db,
    ) {
    }

    public function index(Request $request): Response
    {
        $languages = $this->languages->all();
        $group = $request->string('group', '');
        $search = trim($request->string('q', ''));
        $missing = $request->query('missing') === '1';
        $page = max(1, (int)$request->query('page', 1));

        $result = $this->translations->paginate(array_column($languages, 'code'), $group, $search, $missing, $page, self::PER_PAGE);

        return view('admin/translations/index', [
            'title'     => 'თარგმანები',
            'languages' => $languages,
            'groups'    => $this->translations->groups(),
            'rows'      => $result['rows'],
            'total'     => $result['total'],
            'page'      => $page,
            'pages'     => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'filters'   => ['group' => $group, 'q' => $search, 'missing' => $missing],
        ]);
    }

    /** გვერდზე ნაჩვენები ყველა თარგმანის ერთად შენახვა: values[key_id][lang] */
    public function update(Request $request): Response
    {
        $input = $request->input('values', []);
        $codes = array_column($this->languages->all(), 'code');
        $ids = array_map('intval', array_keys(is_array($input) ? $input : []));
        $existing = $ids === [] ? [] : $this->db->column(
            'SELECT id FROM translation_keys WHERE id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')',
            $ids,
        );

        $values = [];
        foreach ($existing as $id) {
            foreach ($codes as $code) {
                $value = $input[$id][$code] ?? null;
                if (is_string($value)) {
                    $values[(int)$id][$code] = mb_substr($value, 0, self::MAX_LENGTH);
                }
            }
        }

        $this->translations->saveValues($values);
        $this->log->log('translation.updated', 'translation', null, ['count' => count($values)]);
        return back(route('admin.translations.index'))->with('success', 'თარგმანები შენახულია (' . count($values) . ').');
    }

    public function store(Request $request): Response
    {
        $group = strtolower(trim($request->string('group', '')));
        $key = strtolower(trim($request->string('key', '')));
        $description = trim($request->string('description', ''));
        $values = array_filter((array)$request->input('new_values', []), 'is_string');

        $errors = Validator::make(compact('group', 'key', 'description'), [
            'group'       => ['required', 'regex:' . Translation::GROUP_PATTERN],
            'key'         => ['required', 'regex:' . Translation::KEY_PATTERN],
            'description' => 'max:255',
        ], ['group' => 'ჯგუფი', 'key' => 'გასაღები', 'description' => 'აღწერა'])->errors();
        if ($errors === [] && $this->translations->exists($group, $key)) {
            // {$key}: ქართული ბრჭყალი ბაიტებად ცვლადის სახელის ნაწილად წაიკითხება
            $errors['key'] = "გასაღები „{$group}.{$key}“ უკვე არსებობს.";
        }
        if ($errors !== []) {
            return back(route('admin.translations.index'))
                ->withErrors($errors)
                ->withInput(['group' => $group, 'key' => $key, 'description' => $description, 'new_values' => $values])
                ->with('open_new_key', true);
        }

        $codes = array_column($this->languages->all(), 'code');
        $values = array_map(fn ($v) => mb_substr(trim($v), 0, self::MAX_LENGTH), array_intersect_key($values, array_flip($codes)));
        $id = $this->translations->createKey($group, $key, $description !== '' ? $description : null, $values);
        $this->translations->forgetCache();
        $this->log->log('translation.created', 'translation', $id, ['key' => "$group.$key"]);

        return redirect(route('admin.translations.index', ['group' => $group]))->with('success', "გასაღები „{$group}.{$key}“ დაემატა.");
    }

    public function destroy(int $id): Response
    {
        $key = $this->translations->findKey($id) ?? abort(404);
        $this->translations->deleteKey($id);
        $this->log->log('translation.deleted', 'translation', $id, ['key' => $key['group'] . '.' . $key['key']]);
        return back(route('admin.translations.index'))->with('success', "გასაღები „{$key['group']}.{$key['key']}“ წაიშალა.");
    }
}
