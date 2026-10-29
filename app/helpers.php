<?php
declare(strict_types=1);

// პროექტის helper-ები (core/helpers.php — ფრეიმვორკის, ეს — KURSORI-ის მოდულების)

use App\Models\Language;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBlock;

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

/**
 * მთავარი CTA (პარამეტრები → ზოგადი → მენიუს ღილაკი): navbar-ში და მობილურის ქვედა ზოლში.
 * @return array{href: string, label: string}|null გვერდი ან ტექსტი არ არის → null
 */
function cta_link(): ?array
{
    $pageId = (int)setting('general.cta_page');
    $href = $pageId > 0 ? app(Page::class)->url($pageId, locale()) : '';
    $label = (string)setting('general.cta_label');
    return $href !== '' && $label !== '' ? ['href' => $href, 'label' => $label] : null;
}

/**
 * შეკვეთის გვერდის URL (გვერდი order_form ბლოკით): order_url() → გვერდი, order_url('business') → ?package=business#form-order,
 * order_url('custom') → ინდივიდუალური შეკვეთა იმავე ფორმაში. გვერდი არ არის → ''.
 */
function order_url(?string $package = null): string
{
    static $pages = [];
    $lang = locale();
    $pages[$lang] ??= (function () use ($lang): string {
        foreach (app(PageBlock::class)->pageIdsOfType('order_form') as $pageId) {
            if (($href = app(Page::class)->url($pageId, $lang)) !== '') {
                return $href;
            }
        }
        return '';
    })();
    $href = $pages[$lang];
    if ($href === '' || $package === null || $package === '') {
        return $href;
    }
    return $href . '?package=' . rawurlencode($package) . '#form-order';
}

/** ტელეფონის tel: ბმული ფორმატირებული ნომრიდან ('+995 32 247 18 90' → 'tel:+995322471890') */
function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

/** თარიღი მიმდინარე ენაზე: '2026-10-25' → „25 ოქტომბერი, 2026“ / „25 October 2026“ ('' → '') */
function date_human(string $date): string
{
    $time = $date !== '' ? strtotime($date) : false;
    if ($time === false) {
        return '';
    }
    $months = [
        'ka' => ['იანვარი', 'თებერვალი', 'მარტი', 'აპრილი', 'მაისი', 'ივნისი', 'ივლისი', 'აგვისტო', 'სექტემბერი', 'ოქტომბერი', 'ნოემბერი', 'დეკემბერი'],
    ][locale()] ?? null;
    return $months === null ? date('j F Y', $time) : date('j', $time) . ' ' . $months[(int)date('n', $time) - 1] . ', ' . date('Y', $time);
}

/** საკითხავი დრო წუთებში (HTML ტექსტიდან, ~200 სიტყვა წუთში; მინ. 1) */
function reading_minutes(string $html): int
{
    $words = preg_split('/\s+/u', trim(strip_tags($html)), -1, PREG_SPLIT_NO_EMPTY);
    return max(1, (int)ceil(count($words) / 200));
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

/**
 * <picture> WebP-ით და srcset-ით: picture($id, 'large', ['class' => 'w-100'], '100vw').
 * srcset-ში ხვდება მხოლოდ პროპორციული ვარიანტები, არაუმეტეს მოთხოვნილისა; crop ვარიანტი (thumb) — მარტო.
 * $attrs: class, alt (აკლია → media_alt), loading (ნაგულისხმევად lazy), fetchpriority.
 */
function picture(mixed $id, string $variant = 'large', array $attrs = [], string $sizes = '100vw'): string
{
    $media = media($id);
    if ($media === null) {
        return '';
    }
    $model = app(Media::class);
    $variants = Media::variants($media);
    $size = fn (string $name) => $name === 'original'
        ? ['width' => (int)$media['width'], 'height' => (int)$media['height']]
        : $variants[$name];
    $chosen = isset($variants[$variant]) ? $variant : 'original';

    // იმავე პროპორციის კანდიდატები (crop-ის გარდა), სიგანით არაუმეტეს არჩეულისა
    $candidates = [$chosen];
    if (($size($chosen)['width'] ?? 0) > 0 && (config("media.variants.$chosen")[2] ?? 'fit') !== 'crop') {
        foreach (array_merge(array_keys($variants), ['original']) as $name) {
            if ($name !== $chosen && (config("media.variants.$name")[2] ?? 'fit') !== 'crop' && $size($name)['width'] < $size($chosen)['width']) {
                $candidates[] = $name;
            }
        }
    }
    $srcset = fn (bool $webp) => count($candidates) === 1 ? $model->url($media, $chosen, $webp) : implode(', ', array_map(fn ($name) => $model->url($media, $name, $webp) . ' ' . $size($name)['width'] . 'w', $candidates));

    $dimensions = $size($chosen);
    $img = '<img src="' . e($model->url($media, $chosen)) . '"'
        . (count($candidates) > 1 ? ' srcset="' . e($srcset(false)) . '" sizes="' . e($sizes) . '"' : '')
        . ($dimensions['width'] ? ' width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"' : '')
        . ' alt="' . e($attrs['alt'] ?? media_alt($id)) . '"'
        . (isset($attrs['class']) ? ' class="' . e($attrs['class']) . '"' : '')
        . ' loading="' . e($attrs['loading'] ?? 'lazy') . '" decoding="async"'
        . (isset($attrs['fetchpriority']) ? ' fetchpriority="' . e($attrs['fetchpriority']) . '"' : '')
        . '>';

    if (empty($media['webp'])) {
        return $img;
    }
    return '<picture><source type="image/webp" srcset="' . e($srcset(true)) . '"' . (count($candidates) > 1 ? ' sizes="' . e($sizes) . '"' : '') . '>' . $img . '</picture>';
}

/** alt ტექსტი მიმდინარე ენაზე (აკლია → ძირითადი ენა) */
function media_alt(mixed $id): string
{
    $id = (int)$id;
    return $id > 0 ? app(Media::class)->alt($id, locale(), app(Language::class)->defaultCode()) : '';
}
