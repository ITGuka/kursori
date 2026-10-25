<?php
declare(strict_types=1);

namespace Core\Media;

use GdImage;

/**
 * სურათის ზომის ვარიანტები GD-ით: "abc.jpg" → "abc-thumb.jpg", "abc-medium.jpg" ...
 * პროპორცია ინახება, პატარა სურათი არ დიდდება; JPEG-ის EXIF ორიენტაცია სწორდება.
 * ხელახალი კოდირება ვარიანტებიდან მეტამონაცემებს (EXIF, GPS) შლის.
 */
final class ImageResizer
{
    private const RESIZABLE = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @param array<string, array{0: int, 1: int, 2?: string}> $variants სახელი => [სიგანე, სიმაღლე, 'fit'|'crop']
     */
    public function __construct(private readonly array $variants, private readonly int $quality = 85, private readonly int $webpQuality = 80)
    {
    }

    public static function supports(string $mime): bool
    {
        // GIF ანიმაცია GD-ით იკარგება, ამიტომ GIF რჩება ორიგინალად
        return in_array($mime, self::RESIZABLE, true);
    }

    /**
     * @param string $file ორიგინალის სრული ბილიკი
     * @return array<string, array{suffix: string, width: int, height: int}> შექმნილი ვარიანტები
     */
    public function createVariants(string $file, string $mime): array
    {
        if (!self::supports($mime) || ($source = $this->load($file, $mime)) === null) {
            return [];
        }

        $source = $this->applyOrientation($source, $file, $mime);
        $created = [];
        foreach ($this->variants as $name => $spec) {
            [$maxWidth, $maxHeight] = $spec;
            $image = ($spec[2] ?? 'fit') === 'crop'
                ? $this->crop($source, $maxWidth, $maxHeight)
                : $this->fit($source, $maxWidth, $maxHeight);
            if ($image === null) {
                continue;
            }

            $target = self::variantPath($file, $name);
            if ($this->save($image, $target, $mime)) {
                $created[$name] = ['suffix' => $name, 'width' => imagesx($image), 'height' => imagesy($image)];
            }
            if ($image !== $source) {
                imagedestroy($image);
            }
        }
        imagedestroy($source);
        return $created;
    }

    /**
     * WebP ასლები ორიგინალისა და ვარიანტებისთვის: "abc.jpg" → "abc.webp", "abc-medium.jpg" → "abc-medium.webp".
     * @param list<string> $variantNames უკვე შექმნილი ვარიანტები
     * @return bool true — ყველა ასლი შეიქმნა (WebP თავად ან GD-ის მხარდაჭერის გარეშე — false)
     */
    public function createWebp(string $file, string $mime, array $variantNames): bool
    {
        if ($mime === 'image/webp' || !self::supports($mime) || !function_exists('imagewebp')) {
            return false;
        }
        foreach (array_merge([$file], array_map(fn (string $name) => self::variantPath($file, $name), $variantNames)) as $path) {
            $image = $this->load($path, $mime);
            if ($image === null) {
                return false;
            }
            // ორიგინალი EXIF-ით შეიძლება იყოს შემობრუნებული; ვარიანტები უკვე სწორადაა
            if ($path === $file) {
                $image = $this->applyOrientation($image, $file, $mime);
            }
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $saved = imagewebp($image, self::webpPath($path), $this->webpQuality);
            imagedestroy($image);
            if (!$saved) {
                return false;
            }
        }
        return true;
    }

    /** "2026/10/abc-medium.jpg" → "2026/10/abc-medium.webp" */
    public static function webpPath(string $path): string
    {
        $dot = strrpos($path, '.');
        return ($dot === false ? $path : substr($path, 0, $dot)) . '.webp';
    }

    /** "2026/10/abc.jpg" + "thumb" → "2026/10/abc-thumb.jpg" (მუშაობს როგორც სრულ, ისე შედარებით ბილიკზე) */
    public static function variantPath(string $path, string $variant): string
    {
        $dot = strrpos($path, '.');
        return $dot === false ? "$path-$variant" : substr($path, 0, $dot) . '-' . $variant . substr($path, $dot);
    }

    private function load(string $file, string $mime): ?GdImage
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/png'  => @imagecreatefrompng($file),
            'image/webp' => @imagecreatefromwebp($file),
            default      => false,
        };
        return $image instanceof GdImage ? $image : null;
    }

    private function applyOrientation(GdImage $image, string $file, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $image;
        }
        $orientation = (int)(@exif_read_data($file)['Orientation'] ?? 1);
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated instanceof GdImage) {
            imagedestroy($image);
            return $rotated;
        }
        return $image;
    }

    /** ჩაწერა ჩარჩოში პროპორციით; უკვე პატარა სურათი უცვლელად (ასლად) */
    private function fit(GdImage $source, int $maxWidth, int $maxHeight): ?GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        return $this->resample($source, 0, 0, $width, $height, max(1, (int)round($width * $ratio)), max(1, (int)round($height * $ratio)));
    }

    /** ზუსტი ზომა ცენტრიდან ამოჭრით */
    private function crop(GdImage $source, int $targetWidth, int $targetHeight): ?GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = max($targetWidth / $width, $targetHeight / $height);
        if ($scale > 1) {
            return $this->fit($source, $targetWidth, $targetHeight);
        }
        $cropWidth = (int)round($targetWidth / $scale);
        $cropHeight = (int)round($targetHeight / $scale);
        return $this->resample($source, (int)(($width - $cropWidth) / 2), (int)(($height - $cropHeight) / 2), $cropWidth, $cropHeight, $targetWidth, $targetHeight);
    }

    private function resample(GdImage $source, int $x, int $y, int $width, int $height, int $newWidth, int $newHeight): ?GdImage
    {
        $image = imagecreatetruecolor($newWidth, $newHeight);
        if (!$image instanceof GdImage) {
            return null;
        }
        // PNG/WebP გამჭვირვალობა
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagecopyresampled($image, $source, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
        return $image;
    }

    private function save(GdImage $image, string $target, string $mime): bool
    {
        return match ($mime) {
            'image/jpeg' => imagejpeg($image, $target, $this->quality),
            'image/png'  => imagepng($image, $target, 6),
            'image/webp' => imagewebp($image, $target, $this->quality),
            default      => false,
        };
    }
}
