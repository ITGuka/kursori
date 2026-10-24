<?php
declare(strict_types=1);

namespace Core\Support;

final class Url
{
    /**
     * ადმინში შეყვანილი ბმული საიტზე გამოსაყენებლად უსაფრთხოა: http(s), mailto:, tel:,
     * საიტის შიდა ბილიკი (/...) ან ღუზა (#...). javascript:, data: და მსგავსი — არა.
     */
    public static function isSafeHref(string $url): bool
    {
        return (bool)preg_match('~^(https?://[^\s]+|mailto:[^\s]+|tel:[0-9+()\s-]+|/(?!/)[^\s]*|#[\w-]*)$~i', $url);
    }

    /** შიდა ბილიკს ("/ka/about") base path ემატება — საიტი ქვესაქაღალდეშიც მუშაობს */
    public static function resolve(string $url): string
    {
        return str_starts_with($url, '/') && !str_starts_with($url, '//') ? url(ltrim($url, '/')) : $url;
    }
}
