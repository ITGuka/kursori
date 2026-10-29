<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Collection;
use App\Models\Language;
use App\Services\News\NewsPublisher;
use Core\Database\DB;
use Core\Http\Response;

/**
 * /sitemap.xml (გვერდები + საჯარო კოლექციები, ყველა აქტიურ ენაზე, hreflang alternates-ით) და /robots.txt.
 */
final class SeoController
{
    public function __construct(
        private readonly DB $db,
        private readonly Language $languages,
        private readonly Collection $collections,
    ) {
    }

    public function sitemap(): Response
    {
        $langs = $this->languages->activeCodes();
        $default = $this->languages->defaultCode();
        $entries = [];

        if (setting('seo.indexing') === true) {
            // გვერდები: [page_id => [lang => slug]] + is_home, updated_at
            $pages = [];
            foreach ($this->db->select(
                "SELECT p.id, p.is_home, p.updated_at, t.lang, t.slug FROM pages p
                 JOIN page_translations t ON t.page_id = p.id WHERE p.status = 'published' ORDER BY p.is_home DESC, p.sort, p.id",
            ) as $row) {
                $pages[$row['id']]['is_home'] = (bool)$row['is_home'];
                $pages[$row['id']]['updated_at'] = $row['updated_at'];
                $pages[$row['id']]['slugs'][$row['lang']] = $row['slug'];
            }
            foreach ($pages as $page) {
                $urls = [];
                foreach ($langs as $lang) {
                    if (isset($page['slugs'][$lang])) {
                        $urls[$lang] = $page['is_home'] ? "$lang" : "$lang/{$page['slugs'][$lang]}";
                    }
                }
                $entries[] = ['urls' => $urls, 'lastmod' => $page['updated_at'], 'priority' => $page['is_home'] ? '1.0' : '0.8'];
            }

            // საჯარო კოლექციები
            foreach (Collection::types() as $type => $definition) {
                if (empty($definition['public'])) {
                    continue;
                }
                $items = [];
                foreach ($this->db->select(
                    "SELECT i.id, i.updated_at, t.lang, t.slug,
                            COALESCE(JSON_UNQUOTE(JSON_EXTRACT(i.settings, '$.source_url')), '') AS source_url
                     FROM collection_items i
                     JOIN collection_item_translations t ON t.item_id = i.id WHERE i.collection = ? AND i.is_active = 1 ORDER BY i.sort, i.id",
                    [$type],
                ) as $row) {
                    // იმპორტირებული სიახლის ორიგინალის ენის ვერსიას canonical წყაროზე აქვს — sitemap-ში არ შედის (D57)
                    if ($row['lang'] === NewsPublisher::LANG && str_starts_with((string)$row['source_url'], 'http')) {
                        continue;
                    }
                    $items[$row['id']]['updated_at'] = $row['updated_at'];
                    $items[$row['id']]['slugs'][$row['lang']] = $row['slug'];
                }
                foreach ($items as $item) {
                    $urls = [];
                    foreach ($langs as $lang) {
                        if (isset($item['slugs'][$lang])) {
                            $urls[$lang] = $lang . '/' . Collection::routeSlug($type, $lang, $default) . '/' . $item['slugs'][$lang];
                        }
                    }
                    $entries[] = ['urls' => $urls, 'lastmod' => $item['updated_at'], 'priority' => '0.6'];
                }
            }
        }

        // ყოველი ენის ვერსია ცალკე <url>, თითოეულში — ყველა ვერსიის hreflang (Google-ის რეკომენდაცია)
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($entries as $entry) {
            foreach ($entry['urls'] as $path) {
                $xml .= "  <url>\n    <loc>" . self::x(absolute_url($path)) . "</loc>\n"
                      . '    <lastmod>' . date('Y-m-d', strtotime((string)$entry['lastmod'])) . "</lastmod>\n"
                      . "    <priority>{$entry['priority']}</priority>\n";
                if (count($entry['urls']) > 1) {
                    foreach ($entry['urls'] as $hreflang => $alternate) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="' . self::x($hreflang) . '" href="' . self::x(absolute_url($alternate)) . "\"/>\n";
                    }
                    if (isset($entry['urls'][$default])) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . self::x(absolute_url($entry['urls'][$default])) . "\"/>\n";
                    }
                }
                $xml .= "  </url>\n";
            }
        }
        $xml .= "</urlset>\n";

        return (new Response($xml))->header('Content-Type', 'application/xml; charset=UTF-8')->header('X-Robots-Tag', 'noindex');
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];
        if (setting('seo.indexing') === true) {
            $lines[] = 'Disallow: /admin';
            $extra = trim((string)setting('seo.robots_extra'));
            if ($extra !== '') {
                $lines[] = str_replace("\r\n", "\n", $extra);
            }
            $lines[] = '';
            $lines[] = 'Sitemap: ' . absolute_url('sitemap.xml');
        } else {
            // საძიებო სისტემებისთვის დახურული (პარამეტრები → SEO)
            $lines[] = 'Disallow: /';
        }
        return (new Response(implode("\n", $lines) . "\n"))->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private static function x(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
