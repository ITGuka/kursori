<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Design;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * საიტის შაბლონის (HTML/CSS/JS) ZIP არქივის გახსნა public/uploads/designs/{folder}/-ში.
 *
 * უსაფრთხოება: მხოლოდ სტატიკური ფაილები (EXTENSIONS) — სხვა ფაილები (php, .htaccess, exe ...) გამოიტოვება;
 * გზა არქივის გარეთ (../, აბსოლუტური) — არქივი უარიყოფა; ლიმიტები — ზომა, გახსნილი ზომა, ფაილების რაოდენობა.
 * საქაღალდის HTTP header-ები (CSP sandbox — შაბლონის JS ჩვენი საიტის origin-ზე არ მუშაობს) — public/.htaccess.
 */
final class TemplateArchive
{
    public const MAX_ARCHIVE = 50 * 1024 * 1024;
    private const MAX_EXTRACTED = 200 * 1024 * 1024;
    private const MAX_FILES = 3000;
    private const EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'mjs', 'map', 'json', 'txt', 'xml', 'webmanifest',
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg', 'ico', 'bmp',
        'woff', 'woff2', 'ttf', 'otf', 'eot',
        'mp4', 'webm', 'ogg', 'mp3', 'wav', 'pdf',
    ];

    /**
     * @param array $file $_FILES-ის ჩანაწერი
     * @return array{folder: string, entry: string, files: int, size: int, skipped: int}
     * @throws RuntimeException ქართული შეტყობინებით (ადმინის ფორმისთვის)
     */
    public function extract(array $file, int $designId): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('სერვერზე PHP-ის zip გაფართოება ჩართული არ არის.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
            throw new RuntimeException(in_array($file['error'] ?? null, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'ფაილი ძალიან დიდია (სერვერის ლიმიტი: ' . ini_get('upload_max_filesize') . ').'
                : 'ფაილი ვერ აიტვირთა.');
        }
        if ((int)$file['size'] > self::MAX_ARCHIVE) {
            throw new RuntimeException('არქივი ძალიან დიდია (მაქს. ' . (self::MAX_ARCHIVE / 1024 / 1024) . ' MB).');
        }
        if (strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION)) !== 'zip') {
            throw new RuntimeException('დასაშვებია მხოლოდ .zip არქივი.');
        }

        $zip = new ZipArchive();
        if ($zip->open((string)$file['tmp_name'], ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('არქივი დაზიანებულია ან ZIP არ არის.');
        }
        try {
            [$entries, $skipped, $size] = $this->scan($zip);
            $entry = self::entry(array_keys($entries));

            $folder = $designId . '-' . bin2hex(random_bytes(4));
            $target = public_path(Design::DIRECTORY . '/' . $folder);
            try {
                foreach ($entries as $path => $index) {
                    $destination = $target . '/' . $path;
                    if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true) && !is_dir(dirname($destination))) {
                        throw new RuntimeException('საქაღალდე ვერ შეიქმნა.');
                    }
                    $stream = $zip->getStream($zip->getNameIndex($index));
                    if ($stream === false || file_put_contents($destination, $stream) === false) {
                        throw new RuntimeException("ფაილი „{$path}“ ვერ გაიხსნა.");
                    }
                    fclose($stream);
                }
            } catch (RuntimeException $e) {
                self::remove($folder);
                throw $e;
            }
            return ['folder' => $folder, 'entry' => $entry, 'files' => count($entries), 'size' => $size, 'skipped' => $skipped];
        } finally {
            $zip->close();
        }
    }

    /**
     * ბარათის სურათი გახსნილი შაბლონიდან (თემები ხშირად მთავარ საქაღალდეში დებენ სკრინს, მაგ. Chefer/Chefer.jpg). რიგი:
     *   1. შაბლონის მთავარ საქაღალდეში (სადაც index.html-ია) — თემის სახელის (საქაღალდე ან ZIP-ის სახელი)
     *      ან screenshot / preview / thumbnail / thumb / cover სახელის სურათი;
     *   2. იგივე სახელები უფრო ღრმად (ზედა დონესთან ახლოს — უპირატესი);
     *   3. მთავარ საქაღალდეში არსებული ნებისმიერი სურათი (დიდი — უპირატესი);
     *   4. ყველაზე დიდი ფოტო (მინ. 600px სიგანე, ლოგოებისა და ზოლების გარეშე — პროპორცია 0.5–2.5).
     * @param string $entry index.html-ის გზა შაბლონში
     * @param string $archiveName ატვირთული ZIP-ის სახელი (თემის სახელად)
     * @return string|null სრული ბილიკი
     */
    public static function previewImage(string $folder, string $entry = '', string $archiveName = ''): ?string
    {
        $dir = public_path(Design::DIRECTORY . '/' . $folder);
        if (!is_dir($dir)) {
            return null;
        }
        $root = ($root = str_replace('\\', '/', dirname($entry))) === '.' ? '' : $root;
        $themeNames = array_filter(array_map(fn (string $name) => self::normalize($name), [basename($root), pathinfo($archiveName, PATHINFO_FILENAME)]));

        $best = null;
        $bestScore = null;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
            if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }
            $path = $file->getPathname();
            $relative = trim(str_replace('\\', '/', substr($path, strlen($dir))), '/');
            $inRoot = (dirname($relative) === '.' ? '' : dirname($relative)) === $root;
            $name = self::normalize($file->getBasename('.' . $file->getExtension()));
            // თემის სახელი: ზუსტად ან თავსართით (chefer.jpg ↔ chefer-1.0.zip, chefer-preview.jpg ↔ Chefer/)
            $named = preg_match('/^(screenshot|preview|thumbnail|thumb|cover)/', $name) === 1
                || array_filter($themeNames, fn (string $theme) => strlen($name) >= 3 && (str_starts_with($theme, $name) || str_starts_with($name, $theme))) !== [];

            $info = @getimagesize($path);
            if ($info === false || $info[0] < 1 || $info[1] < 1) {
                continue;
            }
            $area = $info[0] * $info[1];
            $photo = $info[0] >= 600 && $info[0] / $info[1] >= 0.5 && $info[0] / $info[1] <= 2.5;
            // [კლასი, სიღრმე (ნაკლები უკეთესია), ფართობი] — მასივების შედარება ელემენტებით
            $score = match (true) {
                $named && $inRoot => [4, 0, $area],
                $named            => [3, -substr_count($relative, '/'), $area],
                $inRoot           => [2, 0, $area],
                $photo            => [1, 0, $area],
                default           => null,
            };
            if ($score !== null && ($bestScore === null || $score > $bestScore)) {
                [$best, $bestScore] = [$path, $score];
            }
        }
        return $best;
    }

    /** სახელის შედარებისთვის: პატარა ასოები, მხოლოდ ასოები და ციფრები („Chefer-HTML“ → „cheferhtml“) */
    private static function normalize(string $name): string
    {
        return (string)preg_replace('/[^a-z0-9]/', '', strtolower($name));
    }

    /** შაბლონის საქაღალდის წაშლა (ძველი ვერსია ან დიზაინის წაშლისას) */
    public static function remove(?string $folder): void
    {
        if ($folder === null || !preg_match('/^\d+-[a-f0-9]{8}$/', $folder)) {
            return;
        }
        $dir = public_path(Design::DIRECTORY . '/' . $folder);
        if (!is_dir($dir)) {
            return;
        }
        $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    /**
     * @return array{0: array<string, int>, 1: int, 2: int} გასახსნელი ფაილები (გზა => index), გამოტოვებულები, ზომა
     */
    private function scan(ZipArchive $zip): array
    {
        if ($zip->numFiles > self::MAX_FILES) {
            throw new RuntimeException('არქივში ძალიან ბევრი ფაილია (მაქს. ' . self::MAX_FILES . ').');
        }
        $entries = [];
        $skipped = 0;
        $size = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = str_replace('\\', '/', (string)$stat['name']);
            if (str_ends_with($name, '/')) {
                continue;
            }
            if (str_starts_with($name, '/') || preg_match('~(^|/)\.\.(/|$)|^[a-z]:~i', $name) || str_contains($name, "\0")) {
                throw new RuntimeException('არქივი შეიცავს დაუშვებელ გზას: ' . $name);
            }
            $segments = explode('/', $name);
            $base = end($segments);
            // macOS-ის მომსახურე ფაილები, დამალული ფაილები (.htaccess ...), დაუშვებელი გაფართოებები
            if ($segments[0] === '__MACOSX' || array_filter($segments, fn ($s) => $s === '' || $s[0] === '.') !== []
                || !in_array(strtolower(pathinfo($base, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
                $skipped++;
                continue;
            }
            $size += (int)$stat['size'];
            if ($size > self::MAX_EXTRACTED) {
                throw new RuntimeException('გახსნილი შაბლონი ძალიან დიდია (მაქს. ' . (self::MAX_EXTRACTED / 1024 / 1024) . ' MB).');
            }
            $entries[$name] = $i;
        }
        if ($entries === []) {
            throw new RuntimeException('არქივში შაბლონის ფაილები ვერ მოიძებნა.');
        }
        return [$entries, $skipped, $size];
    }

    /** index.html — ყველაზე ზედა დონეზე (არქივი ხშირად ერთ საქაღალდეს შეიცავს: theme/index.html) */
    private static function entry(array $paths): string
    {
        $candidates = array_values(array_filter($paths, fn (string $path) => in_array(strtolower(basename($path)), ['index.html', 'index.htm'], true)));
        if ($candidates === []) {
            throw new RuntimeException('არქივში index.html ვერ მოიძებნა.');
        }
        usort($candidates, fn (string $a, string $b) => substr_count($a, '/') <=> substr_count($b, '/') ?: strlen($a) <=> strlen($b));
        return $candidates[0];
    }
}
