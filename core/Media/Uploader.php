<?php
declare(strict_types=1);

namespace Core\Media;

use finfo;

/**
 * ფაილის შემოწმება და შენახვა: {root}/YYYY/MM/{შემთხვევითი}.{ext}
 *
 * - ტიპი დგინდება შიგთავსით (finfo), არა სახელით ან ბრაუზერის Content-Type-ით;
 * - გაფართოება აიღება დაშვებული MIME-ის რუკიდან — მომხმარებლის სახელი ბილიკში არ ხვდება;
 * - სურათი დამატებით მოწმდება getimagesize()-ით და პიქსელების ლიმიტით (GD-ის მეხსიერებისთვის).
 */
final class Uploader
{
    /**
     * @param string $root სრული ბილიკი (მაგ. public/uploads)
     * @param array<string, string> $allowed MIME => გაფართოება
     * @param array<string, string> $messages შეცდომების ტექსტები (:max ჩანაცვლდება)
     */
    public function __construct(
        private readonly string $root,
        private readonly array $allowed,
        private readonly int $maxBytes,
        private readonly int $maxPixels,
        private readonly array $messages = [],
    ) {
    }

    /**
     * @param array{name?: string, tmp_name?: string, error?: int, size?: int}|null $file $_FILES-ის ელემენტი
     * @return array{path: string, original_name: string, mime: string, extension: string, size: int, width: ?int, height: ?int}
     */
    public function store(?array $file): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new UploadException($this->message(match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'too_large',
                UPLOAD_ERR_NO_FILE => 'no_file',
                UPLOAD_ERR_PARTIAL => 'partial',
                default => 'failed',
            }));
        }
        if (!is_uploaded_file((string)$file['tmp_name'])) {
            throw new UploadException($this->message('failed'));
        }

        return $this->save((string)$file['tmp_name'], (string)($file['name'] ?? 'file'), fn (string $from, string $to) => move_uploaded_file($from, $to));
    }

    /**
     * დისკზე არსებული ფაილის დამატება (seed, იმპორტი) — იგივე შემოწმებებით.
     * @return array{path: string, original_name: string, mime: string, extension: string, size: int, width: ?int, height: ?int}
     */
    public function storeLocal(string $source, ?string $originalName = null): array
    {
        if (!is_file($source)) {
            throw new UploadException($this->message('no_file'));
        }
        return $this->save($source, $originalName ?? basename($source), fn (string $from, string $to) => copy($from, $to));
    }

    /** @param callable(string, string): bool $transfer */
    private function save(string $source, string $originalName, callable $transfer): array
    {
        $size = (int)filesize($source);
        if ($size <= 0) {
            throw new UploadException($this->message('empty'));
        }
        if ($size > $this->maxBytes) {
            throw new UploadException($this->message('too_large'));
        }

        $mime = (string)(new finfo(FILEINFO_MIME_TYPE))->file($source);
        $extension = $this->allowed[$mime] ?? null;
        if ($extension === null) {
            throw new UploadException($this->message('type'));
        }

        $width = $height = null;
        if (str_starts_with($mime, 'image/')) {
            $info = @getimagesize($source);
            if ($info === false || $info[0] < 1 || $info[1] < 1) {
                throw new UploadException($this->message('invalid_image'));
            }
            [$width, $height] = $info;
            if ($width * $height > $this->maxPixels) {
                throw new UploadException($this->message('too_many_pixels'));
            }
            // getimagesize მხოლოდ header-ს კითხულობს; დაზიანებული (მაგ. მოჭრილი) სურათი სრულად უნდა გაიხსნას
            if (!self::decodes($source)) {
                throw new UploadException($this->message('invalid_image'));
            }
        }

        $directory = date('Y') . '/' . date('m');
        $absoluteDir = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory);
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new UploadException($this->message('failed'));
        }

        do {
            $name = bin2hex(random_bytes(10)) . '.' . $extension;
        } while (is_file($absoluteDir . DIRECTORY_SEPARATOR . $name));

        if (!$transfer($source, $absoluteDir . DIRECTORY_SEPARATOR . $name)) {
            throw new UploadException($this->message('failed'));
        }

        return [
            'path'          => $directory . '/' . $name,
            'original_name' => self::cleanName($originalName),
            'mime'          => $mime,
            'extension'     => $extension,
            'size'          => $size,
            'width'         => $width,
            'height'        => $height,
        ];
    }

    private static function decodes(string $file): bool
    {
        if (!function_exists('imagecreatefromstring')) {
            return true;
        }
        // GD მოჭრილ JPEG-ზე მხოლოდ warning-ს აგდებს — ის შეცდომად ჩავთვალოთ
        $failed = false;
        set_error_handler(static function () use (&$failed): bool {
            $failed = true;
            return true;
        });
        try {
            $image = imagecreatefromstring((string)file_get_contents($file));
        } finally {
            restore_error_handler();
        }
        if ($image === false) {
            return false;
        }
        imagedestroy($image);
        return !$failed;
    }

    /** ორიგინალი სახელი მხოლოდ საჩვენებლად: ბილიკის და საკონტროლო სიმბოლოების გარეშე */
    public static function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = (string)preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
        return mb_substr(trim($name) !== '' ? trim($name) : 'file', 0, 190);
    }

    private function message(string $key): string
    {
        $defaults = [
            'no_file'         => 'No file was uploaded.',
            'too_large'       => 'The file is too large (max :max MB).',
            'partial'         => 'The file was only partially uploaded.',
            'failed'          => 'The file could not be saved.',
            'empty'           => 'The file is empty.',
            'type'            => 'This file type is not allowed.',
            'invalid_image'   => 'The image is damaged or not supported.',
            'too_many_pixels' => 'The image dimensions are too large.',
        ];
        $text = $this->messages[$key] ?? $defaults[$key];
        return str_replace(':max', (string)round($this->maxBytes / 1048576, 1), $text);
    }
}
