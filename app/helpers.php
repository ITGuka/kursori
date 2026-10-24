<?php
declare(strict_types=1);

// პროექტის helper-ები (core/helpers.php — ფრეიმვორკის, ეს — WEBDEV-ის მოდულების)

use App\Models\Media;
use App\Models\Language;

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

/** alt ტექსტი მიმდინარე ენაზე (აკლია → ძირითადი ენა) */
function media_alt(mixed $id): string
{
    $id = (int)$id;
    return $id > 0 ? app(Media::class)->alt($id, locale(), app(Language::class)->defaultCode()) : '';
}
