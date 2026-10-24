<?php
declare(strict_types=1);

namespace Core\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * WYSIWYG-დან მიღებული HTML-ის გაწმენდა HTMLPurifier-ით: რჩება მხოლოდ ტექსტის ფორმატირება და ბმულები.
 * სკრიპტები, ატრიბუტი on*, style, javascript: ბმულები და სხვ. იშლება.
 */
final class HtmlSanitizer
{
    private const ALLOWED = 'p,br,strong,b,em,i,u,s,a[href|target|rel|title],ul,ol,li,h2,h3,h4,blockquote,span,hr';

    private ?HTMLPurifier $purifier = null;

    public function __construct(private readonly string $cachePath)
    {
    }

    public function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        $clean = trim($this->purifier()->purify($html));
        // ცარიელი რედაქტორი ხშირად "<p><br></p>"-ს აგზავნის
        return trim(strip_tags($clean)) === '' ? '' : $clean;
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier !== null) {
            return $this->purifier;
        }

        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0775, true);
        }
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $this->cachePath);
        $config->set('HTML.Allowed', self::ALLOWED);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        // target="_blank" ბმულებს ავტომატურად ემატება rel="noopener noreferrer"
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('AutoFormat.RemoveEmpty', true);

        return $this->purifier = new HTMLPurifier($config);
    }
}
