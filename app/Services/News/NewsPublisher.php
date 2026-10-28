<?php
declare(strict_types=1);

namespace App\Services\News;

use App\Models\Collection;
use App\Models\Media;
use App\Models\NewsItem;
use Core\Support\Str;
use RuntimeException;
use Throwable;

/**
 * სიახლე → ბლოგის სტატია (ინგლისური ვერსია): ყდის სურათი მედიაში (საქაღალდე news), გაწმენდილი ტექსტი,
 * feed-ის კატეგორია, წყარო და ორიგინალის ბმული (canonical / ატრიბუცია). ქართული თარგმანი — შემდეგი ეტაპი.
 */
final class NewsPublisher
{
    public const COLLECTION = 'blog';
    public const LANG = 'en';
    public const MEDIA_FOLDER = 'news';
    public const SOURCE_NAME = 'DEV Community';

    public function __construct(
        private readonly NewsItem $news,
        private readonly Collection $collection,
        private readonly Media $media,
        private readonly DevToHtml $html,
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
            return $blogId;
        } catch (Throwable $e) {
            $this->news->markFailed($id, $e->getMessage());
            throw $e;
        }
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

    /** უნიკალური slug კოლექციაში (დამთხვევისას -2, -3 ...) */
    private function slug(string $title): string
    {
        $base = Str::slug($title, 140) ?: 'news';
        $slug = $base;
        for ($i = 2; $this->collection->slugExists(self::COLLECTION, self::LANG, $slug); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }
}
