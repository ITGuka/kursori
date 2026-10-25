<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Media;
use App\Models\Page;
use Core\Http\Request;
use Core\Http\Response;
use Core\Settings\Settings;
use Core\Support\Crypt;

final class SettingsController
{
    public function __construct(
        private readonly Settings $settings,
        private readonly Language $languages,
        private readonly ActivityLog $log,
        private readonly Media $media,
    ) {
    }

    public function index(): Response
    {
        return view('admin/settings/index', [
            'title'     => 'პარამეტრები',
            'groups'    => $this->settings->groups(),
            'settings'  => $this->settings,
            'languages' => $this->languages->all(),
        ]);
    }

    /** ფორმა: settings[group.field] ან settings[group.field][lang] */
    public function update(Request $request): Response
    {
        $input = (array)$request->input('settings', []);
        $codes = array_column($this->languages->all(), 'code');
        $values = [];
        $errors = [];

        foreach ($this->settings->groups() as $group => $definition) {
            foreach ($definition['fields'] as $name => $field) {
                $key = "$group.$name";
                $field = $this->settings->field($key);

                if ($field['type'] === 'toggle') {
                    $values[$key] = ($input[$key] ?? '0') === '1' ? '1' : '0';
                    continue;
                }
                if ($field['type'] === 'password') {
                    // ცარიელი — ძველი რჩება; „წაშლა“ ჩამრთველით — იშლება; სხვაგვარად — დაშიფრული
                    $value = is_string($input[$key] ?? null) ? $input[$key] : '';
                    if (($input["$key.clear"] ?? '') === '1') {
                        $values[$key] = '';
                    } elseif ($value !== '') {
                        $values[$key] = mb_strlen($value) > 255 ? '' : Crypt::encrypt($value);
                    }
                    continue;
                }

                if ($field['translatable']) {
                    foreach ($codes as $code) {
                        $value = $this->clean($input[$key][$code] ?? '', $field);
                        if (($error = $this->validate($value, $field)) !== null) {
                            $errors["$key.$code"] = $error;
                        }
                        $values[$key][$code] = $value;
                    }
                } else {
                    $value = $this->clean($input[$key] ?? '', $field);
                    if (($error = $this->validate($value, $field)) !== null) {
                        $errors[$key] = $error;
                    }
                    $values[$key] = $value;
                }
            }
        }

        if ($errors !== []) {
            return back(route('admin.settings.index'))
                ->withErrors($errors)
                ->withInput(['settings' => $values])
                ->with('error', 'ზოგიერთი ველი არასწორია — შეამოწმეთ მონიშნული ველები.');
        }

        $this->settings->save($values);
        $this->log->log('settings.updated', 'settings');
        return back(route('admin.settings.index'))->with('success', 'პარამეტრები შენახულია.');
    }

    private function clean(mixed $value, array $field): string
    {
        $value = is_string($value) ? $value : '';
        // კოდის ველები ზუსტად ინახება; დანარჩენი — ზედმეტი სივრცეების გარეშე
        return $field['type'] === 'code' ? str_replace("\r\n", "\n", $value) : trim($value);
    }

    private function validate(string $value, array $field): ?string
    {
        if ($value === '') {
            return null;
        }

        $max = $field['max'] ?? (in_array($field['type'], ['textarea', 'code'], true) ? 65000 : 255);
        if (mb_strlen($value) > $max) {
            return "მაქსიმუმ $max სიმბოლო.";
        }

        return match ($field['type']) {
            'email'  => filter_var($value, FILTER_VALIDATE_EMAIL) === false ? 'არასწორი ელ-ფოსტა.' : null,
            'url'    => filter_var($value, FILTER_VALIDATE_URL) === false
                        || !in_array(strtolower((string)parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true)
                        ? 'ბმული უნდა იწყებოდეს http:// ან https://-ით.' : null,
            'number' => !is_numeric($value) ? 'უნდა იყოს რიცხვი.' : null,
            'select' => !array_key_exists($value, $field['options'] ?? []) ? 'არასწორი მნიშვნელობა.' : null,
            'image'  => !ctype_digit($value) || ($media = $this->media->find((int)$value)) === null || !Media::isImage($media)
                        ? 'აირჩიეთ სურათი მედია ბიბლიოთეკიდან.' : null,
            'page'   => !ctype_digit($value) || app(Page::class)->find((int)$value) === null ? 'აირჩიეთ გვერდი.' : null,
            default  => null,
        };
    }
}
