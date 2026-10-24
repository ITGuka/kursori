<?php
declare(strict_types=1);

namespace Core\Support;

final class Str
{
    /** ქართული (ეროვნული სისტემა) და რუსული ტრანსლიტერაცია URL-ებისთვის */
    private const TRANSLIT = [
        'ა' => 'a', 'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z', 'თ' => 't',
        'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n', 'ო' => 'o', 'პ' => 'p', 'ჟ' => 'zh',
        'რ' => 'r', 'ს' => 's', 'ტ' => 't', 'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'gh', 'ყ' => 'q',
        'შ' => 'sh', 'ჩ' => 'ch', 'ც' => 'ts', 'ძ' => 'dz', 'წ' => 'ts', 'ჭ' => 'ch', 'ხ' => 'kh', 'ჯ' => 'j',
        'ჰ' => 'h',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
    ];

    /** "ჩვენ შესახებ" → "chven-shesakheb"; მხოლოდ a-z, 0-9 და "-" */
    public static function slug(string $text, int $maxLength = 150): string
    {
        $text = strtr(mb_strtolower(trim($text)), self::TRANSLIT);
        if (function_exists('transliterator_transliterate')) {
            $text = (string)transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        }
        $text = (string)preg_replace('/[^a-z0-9]+/', '-', strtolower($text));
        return trim(substr($text, 0, $maxLength), '-');
    }

    /** ტექსტის შემოკლება სიტყვის საზღვარზე (meta description და სხვ.) */
    public static function limit(string $text, int $length, string $end = '…'): string
    {
        $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($text)));
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        $cut = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');
        return rtrim($space !== false && $space > $length * 0.6 ? mb_substr($cut, 0, $space) : $cut, " ,.;:-") . $end;
    }
}
