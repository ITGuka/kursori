<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Collection;
use App\Models\Language;
use App\Models\Page;
use Core\Http\HttpException;
use Core\Http\Response;
use Core\Support\Str;

/**
 * კოლექციის ჩანაწერის საჯარო გვერდი: /{lang}/{route}/{slug} (მხოლოდ public კოლექციები).
 */
final class CollectionController
{
    public function __construct(
        private readonly Collection $items,
        private readonly Page $pages,
        private readonly Language $languages,
    ) {
    }

    public function show(string $lang, string $collection, string $slug): Response
    {
        $default = $this->languages->defaultCode();
        $type = Collection::typeByRoute($lang, $collection, $default) ?? throw new HttpException(404);
        $item = $this->items->findBySlug($type, $lang, $slug, $default) ?? throw new HttpException(404);
        $definition = Collection::type($type);

        // breadcrumb: თუ ამავე მისამართის გვერდი არსებობს (მაგ. /ka/servisebi), ის სიის გვერდია
        $listing = $this->pages->findBySlug($lang, $collection);
        $breadcrumbs = $listing !== null
            ? [['title' => $listing['translation']['title'], 'href' => $this->pages->url((int)$listing['page']['id'], $lang)]]
            : [];

        $alternates = array_intersect_key($item['alternates'], array_flip($this->languages->activeCodes()));
        $languageLinks = [];
        foreach ($this->languages->activeCodes() as $code) {
            $languageLinks[$code] = $alternates[$code] ?? url($code);
        }
        $description = Str::limit((string)($item['excerpt'] ?? strip_tags((string)($item['body'] ?? ''))), 300);

        return view($definition['view'] ?? 'front/collections/detail', [
            'item'        => $item,
            'type'        => $type,
            'definition'  => $definition,
            'breadcrumbs' => $breadcrumbs,
            'meta'        => [
                'title'          => $item['title'] . ' · ' . setting('general.site_name'),
                'description'    => $description !== '' ? $description : null,
                'image'          => media_url($item['image'], 'large') ?: null,
                'canonical'      => $item['url'],
                'alternates'     => $alternates + (isset($alternates[$default]) ? ['x-default' => $alternates[$default]] : []),
                'language_links' => $languageLinks,
            ],
        ]);
    }
}
