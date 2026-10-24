<?php
declare(strict_types=1);

// პროექტის helper-ები (core/helpers.php — ფრეიმვორკის, ეს — WEBDEV-ის მოდულების)

use App\Models\Language;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;

/** მედიის ჩანაწერი id-ით (მაგ. setting('general.logo')) */
function media(mixed $id): ?array
{
    $id = (int)$id;
    return $id > 0 ? app(Media::class)->find($id) : null;
}

/** მედიის URL: media_url(setting('general.logo'), 'medium'); არ არსებობს → '' */
function media_url(mixed $id, string $variant = 'original'): string
{
    $media = media($id);
    return $media === null ? '' : app(Media::class)->url($media, $variant);
}

/**
 * მენიუ მიმდინარე ენაზე: menu('header'), menu('footer').
 * header მენიუ ჯერ ცარიელია (ახალი საიტი) → გამოქვეყნებული გვერდები, რომ საიტი ნავიგაციის გარეშე არ დარჩეს.
 * @return list<array{label: string, href: string, new_tab: bool, children: list<array>}>
 */
function menu(string $location): array
{
    $menus = app(Menu::class);
    $items = $menus->tree($location, locale(), app(Language::class)->defaultCode());
    if ($items === [] && $location === 'header' && $menus->isEmpty($location)) {
        $toItem = fn (array $page) => ['label' => $page['title'], 'href' => $page['href'], 'new_tab' => false,
                                       'children' => array_map(fn ($child) => ['label' => $child['title'], 'href' => $child['href'], 'new_tab' => false, 'children' => []], $page['children'] ?? [])];
        $items = array_map($toItem, app(Page::class)->navigation(locale()));
    }
    return $items;
}

/** <a<?= link_target($item) ?>> — ახალ ფანჯარაში გახსნის ატრიბუტები */
function link_target(array $item): string
{
    return !empty($item['new_tab']) ? ' target="_blank" rel="noopener"' : '';
}

/** ბლოკის link ველის ატრიბუტები: <a<?= link_attrs($data['button_link']) ?>> → href (+ target/rel) */
function link_attrs(?array $link): string
{
    if ($link === null) {
        return ' href="#"';
    }
    return ' href="' . e($link['href']) . '"' . (!empty($link['new_tab']) ? ' target="_blank" rel="noopener"' : '');
}

/** alt ტექსტი მიმდინარე ენაზე (აკლია → ძირითადი ენა) */
function media_alt(mixed $id): string
{
    $id = (int)$id;
    return $id > 0 ? app(Media::class)->alt($id, locale(), app(Language::class)->defaultCode()) : '';
}
