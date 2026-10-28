<?php
declare(strict_types=1);

namespace App\Services\News;

use Core\Support\HtmlSanitizer;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * dev.to-ს body_html → საიტის სტატიის HTML: იშლება ინტერფეისის ნაწილები (კოდის პანელის ღილაკები, SVG, follow ბარათები,
 * embed-ები — iframe ბმულად რჩება), შემდეგ HtmlSanitizer. სურათები dev.to-ს CDN-იდან იტვირთება.
 */
final class DevToHtml
{
    /** წასაშლელი ელემენტები შიგთავსიანად (XPath) */
    private const REMOVE = [
        '//svg', '//script', '//style', '//button', '//video', '//form',
        "//*[contains(concat(' ', normalize-space(@class), ' '), ' highlight__panel ')]",
        "//*[contains(concat(' ', normalize-space(@class), ' '), ' js-actions-panel ')]",
        "//*[contains(concat(' ', normalize-space(@class), ' '), ' ltag__user ')]",
        "//*[contains(concat(' ', normalize-space(@class), ' '), ' crayons-btn ')]",
        "//a[contains(concat(' ', normalize-space(@class), ' '), ' anchor ')]",
    ];

    public function __construct(private readonly HtmlSanitizer $sanitizer)
    {
    }

    public function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="news-root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($dom);

        foreach (self::REMOVE as $query) {
            foreach (iterator_to_array($xpath->query($query)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
        // embed (YouTube, CodePen და სხვ.) — ბმული წყაროზე
        foreach (iterator_to_array($xpath->query('//iframe[@src]')) as $iframe) {
            /** @var DOMElement $iframe */
            $src = (string)$iframe->getAttribute('src');
            $replacement = $dom->createElement('p');
            if (preg_match('~^https?://~i', $src)) {
                $link = $dom->createElement('a');
                $link->setAttribute('href', $src);
                $link->appendChild($dom->createTextNode($src));
                $replacement->appendChild($link);
            }
            $iframe->parentNode?->replaceChild($replacement, $iframe);
        }
        // გარე ბმულები ახალ ფანჯარაში; ფარდობითი (/username) → dev.to
        foreach ($xpath->query('//a[@href]') as $link) {
            /** @var DOMElement $link */
            $href = (string)$link->getAttribute('href');
            if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
                $link->setAttribute('href', 'https://dev.to' . $href);
            }
            if (!str_starts_with($href, '#')) {
                $link->setAttribute('target', '_blank');
            }
        }

        $root = $dom->getElementById('news-root');
        $inner = '';
        foreach ($root?->childNodes ?? [] as $child) {
            $inner .= $dom->saveHTML($child);
        }
        return $this->sanitizer->clean($inner);
    }
}
