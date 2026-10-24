<?php
declare(strict_types=1);

namespace App\Blocks;

use App\Models\Collection;
use App\Models\Page;
use Core\Support\Url;

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
     * ბლოკის სრული მონაცემები: ველები + (source = collection) კოლექციის ჩანაწერები repeater-ის ფორმატში.
     */
    public function resolveBlock(array $definition, array $settings, array $content, array $fallback, string $lang, string $defaultLang): array
    {
        $data = $this->resolve($definition['fields'], $settings, $content, $fallback, $lang);

        $collection = $definition['collection'] ?? null;
        if ($collection !== null && (!empty($collection['always']) || ($data['source'] ?? 'manual') === 'collection')) {
            $items = app(Collection::class)->list($collection['type'], ($data['category'] ?? null) ?: null, (int)($data['limit'] ?? 0), $lang, $defaultLang);
            $data[$collection['target']] = array_map(fn (array $item) => $this->mapItem($item, $collection['map']), $items);
        }
        return $data;
    }

    /** კოლექციის ჩანაწერი → repeater-ის ელემენტი (config/blocks.php → collection.map) */
    private function mapItem(array $item, array $map): array
    {
        $link = $item['url'] !== '' ? ['href' => $item['url'], 'new_tab' => false] : null;
        $mapped = [];
        foreach ($map as $target => $source) {
            $mapped[$target] = match ($source) {
                '@url'       => $link,
                '@read_more' => $link !== null ? t('common.read_more') : '',
                // array_key_exists: null (მაგ. ცარიელი ბმული) უნდა დარჩეს null და არა ''
                default      => array_key_exists($source, $item) ? $item[$source] : '',
            };
        }
        return $mapped;
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
        if (($link['type'] ?? '') === 'url') {
            $href = Url::isSafeHref($href) ? Url::resolve($href) : '';
        }
        return $href === '' ? null : ['href' => $href, 'new_tab' => !empty($link['new_tab'])];
    }
}
