<?php
declare(strict_types=1);

namespace App\Blocks;

use App\Models\Page;

/**
 * შენახული ბლოკი → view-ის მონაცემები მიმდინარე ენაზე.
 * თარგმნადი ველი ცარიელია → ძირითადი ენის მნიშვნელობა → default. link → ['href', 'new_tab'] ან null.
 */
final class BlockResolver
{
    public function __construct(private readonly Page $pages)
    {
    }

    /**
     * @param array<string, array> $fields
     * @param array $settings შენახული settings JSON
     * @param array $content მიმდინარე ენის content
     * @param array $fallback ძირითადი ენის content
     */
    public function resolve(array $fields, array $settings, array $content, array $fallback, string $lang): array
    {
        $data = [];
        foreach ($fields as $name => $field) {
            if (($field['type'] ?? 'text') === 'repeater') {
                $data[$name] = [];
                foreach ((array)($settings[$name] ?? []) as $item) {
                    $id = (string)($item['_id'] ?? '');
                    $data[$name][] = $this->values(
                        $field['fields'],
                        (array)$item,
                        (array)($content[$name][$id] ?? []),
                        (array)($fallback[$name][$id] ?? []),
                        $lang,
                    );
                }
                continue;
            }
            $data += $this->values([$name => $field], $settings, $content, $fallback, $lang);
        }
        return $data;
    }

    private function values(array $fields, array $settings, array $content, array $fallback, string $lang): array
    {
        $data = [];
        foreach ($fields as $name => $field) {
            if (!empty($field['translatable'])) {
                $value = $content[$name] ?? '';
                if ($value === '' || $value === null) {
                    $value = $fallback[$name] ?? '';
                }
            } else {
                $value = $settings[$name] ?? null;
            }
            if ($value === '' || $value === null) {
                $value = $field['default'] ?? ($field['type'] === 'toggle' ? false : '');
            }
            $data[$name] = ($field['type'] ?? '') === 'link' ? $this->link(is_array($value) ? $value : [], $lang) : $value;
        }
        return $data;
    }

    /** @return array{href: string, new_tab: bool}|null */
    private function link(array $link, string $lang): ?array
    {
        $href = match ($link['type'] ?? '') {
            'page'  => $this->pages->url((int)($link['page_id'] ?? 0), $lang),
            'url'   => (string)($link['url'] ?? ''),
            default => '',
        };
        // შიდა ბილიკს ("/ka/about") base path ემატება (ქვესაქაღალდეში გაშვებისთვის)
        if (($link['type'] ?? '') === 'url' && str_starts_with($href, '/') && !str_starts_with($href, '//')) {
            $href = url(ltrim($href, '/'));
        }
        return $href === '' ? null : ['href' => $href, 'new_tab' => !empty($link['new_tab'])];
    }
}
