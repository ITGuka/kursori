<?php
declare(strict_types=1);

namespace App\Services\News;

use App\Models\Collection;
use App\Models\Media;
use App\Models\NewsItem;
use Core\Support\HtmlSanitizer;
use Core\Support\Str;
use RuntimeException;
use Throwable;

/**
 * სიახლე → ბლოგის სტატია (ინგლისური ვერსია): ყდის სურათი მედიაში (საქაღალდე news), გაწმენდილი ტექსტი,
 * feed-ის კატეგორია, წყარო და ორიგინალის ბმული (canonical / ატრიბუცია); შემდეგ ქართული თარგმანი (ClaudeTranslator).
 */
final class NewsPublisher
{
    public const COLLECTION = 'blog';
    public const LANG = 'en';
    public const TRANSLATION_LANG = 'ka';
    public const MEDIA_FOLDER = 'news';
    public const SOURCE_NAME = 'DEV Community';

    public function __construct(
        private readonly NewsItem $news,
        private readonly Collection $collection,
        private readonly Media $media,
        private readonly DevToHtml $html,
        private readonly HtmlSanitizer $sanitizer,
    ) {
    }

    /** @return int ბლოგის ჩანაწერის id; შეცდომისას სიახლე failed სტატუსს იღებს და გამონაკლისი ვრცელდება */
    public function publish(int $id): int
    {
        $item = $this->news->find($id) ?? throw new RuntimeException("News item $id not found.");
        if ($item['status'] === 'published' && $item['collection_item_id'] !== null) {
            return (int)$item['collection_item_id'];
        }
        try {
            $body = $this->html->clean((string)$item['body_html']);
            if ($body === '') {
                throw new RuntimeException('Article body is empty after cleaning.');
            }
            $imageId = $item['image_url'] ? $this->image((string)$item['image_url'], (string)$item['title']) : null;
            $title = mb_substr(trim((string)$item['title']), 0, 190);

            $blogId = $this->collection->save(
                null,
                self::COLLECTION,
                ['category_id' => $item['category_id'] !== null ? (int)$item['category_id'] : null, 'image_id' => $imageId, 'is_active' => true],
                ['source_name' => self::SOURCE_NAME, 'source_url' => (string)$item['canonical_url']],
                [self::LANG => ['title' => $title, 'slug' => $this->slug($title)]],
                [self::LANG => [
                    'excerpt' => Str::limit(trim((string)$item['description']), 500),
                    'body'    => $body,
                    'author'  => mb_substr((string)$item['author_name'], 0, 100),
                ]],
                [],
            );
            $this->news->markPublished($id, $blogId);
        } catch (Throwable $e) {
            $this->news->markFailed($id, $e->getMessage());
            throw $e;
        }
        // თარგმანის შეცდომა გამოქვეყნებას არ აუქმებს — სტატია ინგლისურად რჩება, შეცდომა ჩანს სიახლის ჩანაწერში
        try {
            $this->translate($id);
        } catch (Throwable $e) {
            $this->news->setError($id, 'Translation: ' . $e->getMessage());
        }
        return $blogId;
    }

    /**
     * გამოქვეყნებული სიახლის ქართული ვერსია (Claude). false — თარგმანი გამორთულია, გასაღები არ არის ან სტატია ზედმეტად გრძელია.
     * @throws Throwable API-ის ან პასუხის შეცდომა
     */
    public function translate(int $id): bool
    {
        $item = $this->news->find($id);
        $blog = $item && $item['collection_item_id'] !== null ? $this->collection->find((int)$item['collection_item_id']) : null;
        $source = $blog['translations'][self::LANG] ?? null;
        $apiKey = (string)setting('news.claude_api_key');
        if ($source === null || setting('news.translate') !== true || $apiKey === '') {
            return false;
        }
        $body = (string)($source['content']['body'] ?? '');
        $maxChars = (int)setting('news.translate_max_chars');
        if ($maxChars > 0 && strlen($body) > $maxChars) {
            $this->news->setError($id, sprintf('Translation skipped: article is %d characters (limit %d).', strlen($body), $maxChars));
            return false;
        }

        $translator = new ClaudeTranslator($apiKey, (string)setting('news.claude_model') ?: 'claude-haiku-5-5');
        $ka = $translator->translate((string)$source['title'], (string)($source['content']['excerpt'] ?? ''), $body);
        $title = mb_substr($ka['title'], 0, 190);
        $this->collection->saveTranslation((int)$blog['id'], self::TRANSLATION_LANG, $title, $this->slug($title, self::TRANSLATION_LANG), [
            'excerpt' => Str::limit($ka['excerpt'], 500),
            'body'    => $this->sanitizer->clean($ka['body']),
            'author'  => (string)($source['content']['author'] ?? ''),
        ]);
        $this->news->setError($id, null);
        return true;
    }

    /** სურათის ჩამოტვირთვა დროებით ფაილში → მედია (ვარიანტები, WebP); alt — სათაური */
    private function image(string $url, string $title): int
    {
        if (!preg_match('~^https://~i', $url)) {
            throw new RuntimeException('Cover image URL must be HTTPS.');
        }
        // dev.to-ს CDN (Cloudflare) ფორმატს Accept-ით ირჩევს და ანიმაციურ GIF-ს ანიმაციურ WebP-ად აბრუნებს, რომელსაც GD ვერ ხსნის —
        // ვითხოვთ JPEG-ს (ანიმაციიდან — პირველი კადრი); WebP ასლს მედია თავად ქმნის
        $url = (string)preg_replace_callback('~/dynamic/image/([^/]+)/~', function (array $m): string {
            $options = array_filter(explode(',', $m[1]), fn ($o) => !str_starts_with($o, 'format='));
            return '/dynamic/image/' . implode(',', [...$options, 'format=jpeg']) . '/';
        }, $url, 1);
        $tmp = tempnam(sys_get_temp_dir(), 'news');
        try {
            $ch = curl_init($url);
            $handle = fopen($tmp, 'wb');
            curl_setopt_array($ch, [
                CURLOPT_FILE           => $handle,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => ['Accept: image/jpeg,image/png,image/webp;q=0.8', 'User-Agent: KURSORI/1.0 (+' . config('app.url') . ')'],
                CURLOPT_MAXFILESIZE    => (int)config('media.max_size'),
            ]);
            $ok = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($handle);
            if ($ok === false || $status !== 200) {
                throw new RuntimeException("Cover image download failed ($status).");
            }
            $media = $this->media->import($tmp, self::MEDIA_FOLDER);
            $this->media->update((int)$media['id'], self::MEDIA_FOLDER, [self::LANG => ['alt' => $title]]);
            return (int)$media['id'];
        } finally {
            @unlink($tmp);
        }
    }

    /** უნიკალური slug კოლექციაში ენაზე (ქართული — ტრანსლიტერაციით; დამთხვევისას -2, -3 ...) */
    private function slug(string $title, string $lang = self::LANG): string
    {
        $base = Str::slug($title, 140) ?: 'news';
        $slug = $base;
        for ($i = 2; $this->collection->slugExists(self::COLLECTION, $lang, $slug); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }
}
