<?php
declare(strict_types=1);

namespace App\Blocks;

use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use Core\Support\HtmlSanitizer;
use Core\Support\Url;

/**
 * ადმინის ბლოკის ფორმის დამუშავება ველების სქემით: გაწმენდა, ვალიდაცია, შენახვის ფორმატში გადაყვანა.
 *
 * ფორმის ველები:  settings[field]                    — არათარგმნადი
 *                 content[lang][field]               — თარგმნადი
 *                 settings[rep][i][_id], settings[rep][i][sub], content[lang][rep][_id][sub] — repeater
 * შედეგი: settings (JSON), content[lang] (JSON), errors (ბილიკი => ტექსტი, მაგ. "slides.0.title.ka"), media (id-ები)
 */
final class BlockInput
{
    private const ITEM_ID = '/^[a-z0-9]{6,16}$/';

    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<int, true> */
    private array $media = [];
    /** @var list<string> */
    private array $langs = [];
    private string $defaultLang = '';

    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
        private readonly Media $mediaModel,
        private readonly Page $pages,
        private readonly Category $categories,
    ) {
    }

    /**
     * @param array<string, array> $fields ბლოკის ველების სქემა
     * @param list<string> $langs
     * @return array{settings: array, content: array<string, array>, errors: array<string, string>, media: list<int>}
     */
    public function parse(array $fields, array $settingsInput, array $contentInput, array $langs, string $defaultLang): array
    {
        $this->errors = [];
        $this->media = [];
        $this->langs = $langs;
        $this->defaultLang = $defaultLang;

        $settings = [];
        $content = array_fill_keys($langs, []);

        foreach ($fields as $name => $field) {
            // show_if-ით დამალული ველი ინახება (წყაროს დაბრუნებისას რომ არ დაიკარგოს), მაგრამ არ მოწმდება
            $hidden = !self::visible($field, $settingsInput, $fields);
            $errorsBefore = $this->errors;
            if (($field['type'] ?? 'text') === 'repeater') {
                [$settings[$name], $byLang] = $this->repeater($name, $field, $settingsInput[$name] ?? [], $contentInput);
                foreach ($byLang as $lang => $value) {
                    $content[$lang][$name] = $value;
                }
            } elseif (!empty($field['translatable'])) {
                foreach ($langs as $lang) {
                    $content[$lang][$name] = $this->value($field, $contentInput[$lang][$name] ?? null, "$name.$lang", $lang === $defaultLang);
                }
            } else {
                $settings[$name] = $this->value($field, $settingsInput[$name] ?? null, $name, true);
            }

            if ($hidden) {
                $this->errors = $errorsBefore;
            }
        }

        return ['settings' => $settings, 'content' => $content, 'errors' => $this->errors, 'media' => array_keys($this->media)];
    }

    /** show_if: ['source' => 'manual'] — ველი ჩანს, როცა ფორმაში source = manual (ან მისი default) */
    public static function visible(array $field, array $settings, array $fields): bool
    {
        foreach ((array)($field['show_if'] ?? []) as $other => $expected) {
            $value = $settings[$other] ?? $fields[$other]['default'] ?? null;
            if ((string)$value !== (string)$expected) {
                return false;
            }
        }
        return true;
    }

    /** @return array{0: list<array>, 1: array<string, array<string, array>>} */
    private function repeater(string $name, array $field, mixed $rows, array $contentInput): array
    {
        $rows = is_array($rows) ? array_values(array_filter($rows, 'is_array')) : [];
        $max = (int)($field['max_items'] ?? 50);
        if (count($rows) > $max) {
            $this->errors[$name] = "მაქსიმუმ $max ელემენტი.";
            $rows = array_slice($rows, 0, $max);
        }
        if (count($rows) < (int)($field['min_items'] ?? 0)) {
            $this->errors[$name] = 'საჭიროა მინიმუმ ' . (int)$field['min_items'] . ' ელემენტი.';
        }

        $items = [];
        $byLang = array_fill_keys($this->langs, []);
        foreach ($rows as $index => $row) {
            $id = is_string($row['_id'] ?? null) && preg_match(self::ITEM_ID, $row['_id']) ? $row['_id'] : bin2hex(random_bytes(5));
            $item = ['_id' => $id];
            foreach ($field['fields'] as $sub => $subField) {
                $path = "$name.$index.$sub";
                if (!empty($subField['translatable'])) {
                    foreach ($this->langs as $lang) {
                        $raw = $contentInput[$lang][$name][$row['_id'] ?? ''][$sub] ?? null;
                        $byLang[$lang][$id][$sub] = $this->value($subField, $raw, "$path.$lang", $lang === $this->defaultLang);
                    }
                } else {
                    $item[$sub] = $this->value($subField, $row[$sub] ?? null, $path, true);
                }
            }
            $items[] = $item;
        }
        return [$items, $byLang];
    }

    /** @param bool $enforceRequired თარგმნადი ველი სავალდებულოა მხოლოდ ძირითად ენაზე */
    private function value(array $field, mixed $raw, string $path, bool $enforceRequired): mixed
    {
        $type = $field['type'] ?? 'text';
        $value = match ($type) {
            'text'     => $this->text($raw, false),
            'textarea' => $this->text($raw, true),
            'wysiwyg'  => $this->sanitizer->clean(is_string($raw) ? $raw : ''),
            'image'    => $this->image($raw, $path),
            'link'     => $this->link($raw, $path),
            'select'   => $this->select($raw, $field),
            'number'   => $this->number($raw, $field, $path),
            'toggle'   => $raw === '1' || $raw === true,
            'category' => $this->category($raw, $field, $path),
            default    => null,
        };

        if (in_array($type, ['text', 'textarea', 'wysiwyg'], true)) {
            $max = (int)($field['max'] ?? match ($type) { 'text' => 255, 'textarea' => 5000, default => 100000 });
            if (mb_strlen($value) > $max) {
                $this->errors[$path] = "მაქსიმუმ $max სიმბოლო.";
                $value = mb_substr($value, 0, $max);
            }
        }

        $empty = $value === '' || $value === null || ($type === 'link' && ($value['type'] ?? '') === '');
        if ($enforceRequired && !empty($field['required']) && $empty && !isset($this->errors[$path])) {
            $this->errors[$path] = 'სავალდებულო ველი.';
        }
        return $value;
    }

    private function text(mixed $raw, bool $multiline): string
    {
        $value = is_string($raw) ? $raw : '';
        $value = str_replace("\r\n", "\n", $value);
        $value = (string)preg_replace($multiline ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u', '', $value);
        return trim($value);
    }

    private function image(mixed $raw, string $path): ?int
    {
        $raw = is_scalar($raw) ? trim((string)$raw) : '';
        if ($raw === '') {
            return null;
        }
        $media = ctype_digit($raw) ? $this->mediaModel->find((int)$raw) : null;
        if ($media === null || !Media::isImage($media)) {
            $this->errors[$path] = 'აირჩიეთ სურათი მედია ბიბლიოთეკიდან.';
            return null;
        }
        $this->media[(int)$media['id']] = true;
        return (int)$media['id'];
    }

    /** @return array{type: string, page_id: ?int, url: string, new_tab: bool} */
    private function link(mixed $raw, string $path): array
    {
        $raw = is_array($raw) ? $raw : [];
        $type = in_array($raw['type'] ?? '', ['page', 'url'], true) ? $raw['type'] : '';
        $link = ['type' => $type, 'page_id' => null, 'url' => '', 'new_tab' => ($raw['new_tab'] ?? '') === '1'];

        if ($type === 'page') {
            $id = (int)($raw['page_id'] ?? 0);
            if ($this->pages->find($id) === null) {
                $this->errors[$path] = 'აირჩიეთ გვერდი.';
            }
            $link['page_id'] = $id ?: null;
        } elseif ($type === 'url') {
            $url = trim(is_string($raw['url'] ?? null) ? $raw['url'] : '');
            if (!Url::isSafeHref($url)) {
                $this->errors[$path] = 'ბმული: https://..., /გვერდი, #ღუზა, mailto: ან tel:';
            }
            $link['url'] = mb_substr($url, 0, 2000);
        }
        return $link;
    }

    private function category(mixed $raw, array $field, string $path): ?int
    {
        $id = is_scalar($raw) ? (int)$raw : 0;
        if ($id === 0) {
            return null;
        }
        $category = $this->categories->find($id);
        if ($category === null || $category['collection'] !== ($field['collection'] ?? '')) {
            $this->errors[$path] = 'არასწორი კატეგორია.';
            return null;
        }
        return $id;
    }

    private function select(mixed $raw, array $field): string
    {
        $options = $field['options'] ?? [];
        return is_string($raw) && array_key_exists($raw, $options) ? $raw : (string)($field['default'] ?? array_key_first($options));
    }

    private function number(mixed $raw, array $field, string $path): ?int
    {
        $raw = is_scalar($raw) ? trim((string)$raw) : '';
        if ($raw === '') {
            return null;
        }
        if (filter_var($raw, FILTER_VALIDATE_INT) === false) {
            $this->errors[$path] = 'უნდა იყოს მთელი რიცხვი.';
            return null;
        }
        $value = (int)$raw;
        if ((isset($field['min']) && $value < $field['min']) || (isset($field['max']) && $value > $field['max'])) {
            $this->errors[$path] = "დასაშვებია {$field['min']}–{$field['max']}.";
        }
        return $value;
    }
}
