<?php
declare(strict_types=1);

namespace App\Services;

use Core\Media\Uploader;
use Core\Media\UploadException;

/**
 * შეკვეთის ფორმების მიმაგრებული ფაილები (config/forms.php → attachments): რამდენიმე ფაილი ერთ ველში (name[]),
 * storage/orders/YYYY/MM-ში (web root-ის გარეთ), ტიპი — შიგთავსით (Uploader). Inbox-ში — [{name, path, mime, size}, ...].
 */
final class Attachments
{
    /** შენახული ფაილის ბილიკი storage/orders-ში (Uploader: YYYY/MM/{20 hex}.{ext}) */
    public const FILE_PATTERN = '~^\d{4}/\d{2}/[a-f0-9]{20}\.[a-z0-9]{2,5}$~';

    public static function config(): array
    {
        return (array)config('forms.attachments');
    }

    /**
     * $_FILES-ის მრავლობითი ველი (name[]) → ფაილების სია; ცარიელი სლოტები გამოტოვებულია
     * @return list<array{name: string, tmp_name: string, error: int, size: int}>
     */
    public static function fromRequest(mixed $files): array
    {
        if (!is_array($files) || !is_array($files['name'] ?? null)) {
            return [];
        }
        $list = [];
        foreach (array_keys($files['name']) as $i) {
            $file = ['name' => (string)$files['name'][$i], 'tmp_name' => (string)($files['tmp_name'][$i] ?? ''),
                     'error' => (int)($files['error'][$i] ?? UPLOAD_ERR_NO_FILE), 'size' => (int)($files['size'][$i] ?? 0)];
            if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $list[] = $file;
            }
        }
        return $list;
    }

    /**
     * ყველა ფაილის შენახვა ან არცერთის: შეცდომისას უკვე შენახულები იშლება
     * @param list<array> $files
     * @return list<array{name: string, path: string, mime: string, size: int}>
     * @throws UploadException შეცდომა ფაილის სახელით
     */
    public static function store(array $files): array
    {
        $config = self::config();
        if (count($files) > (int)$config['max_files']) {
            throw new UploadException(t('form.upload_too_many', ['count' => $config['max_files']]));
        }
        if (array_sum(array_column($files, 'size')) > (int)$config['max_total']) {
            throw new UploadException(t('form.upload_total', ['max' => self::megabytes((int)$config['max_total'])]));
        }
        $messages = [];
        foreach (['too_large', 'partial', 'failed', 'empty', 'type', 'invalid_image', 'too_many_pixels'] as $key) {
            $messages[$key] = t("form.upload_$key");
        }
        $uploader = new Uploader(storage_path('orders'), $config['allowed'], (int)$config['max_size'], (int)config('media.max_pixels'), $messages);

        $stored = [];
        foreach ($files as $file) {
            try {
                $saved = $uploader->store($file);
            } catch (UploadException $e) {
                self::delete($stored);
                throw new UploadException(Uploader::cleanName($file['name']) . ': ' . $e->getMessage(), 0, $e);
            }
            $stored[] = ['name' => $saved['original_name'], 'path' => $saved['path'], 'mime' => $saved['mime'], 'size' => $saved['size']];
        }
        return $stored;
    }

    /**
     * შენახული ფაილები ჩანაწერიდან (ძველი ფორმატიც — ერთი ფაილი {name, path})
     * @return list<array{name: string, path: string, mime?: string, size?: int}>
     */
    public static function list(mixed $value): array
    {
        if (is_array($value) && isset($value['path'])) {
            $value = [$value];
        }
        return array_values(array_filter(is_array($value) ? $value : [], fn ($file) => is_array($file) && isset($file['name'], $file['path'])));
    }

    /**
     * ჩანაწერის ფაილები: attachments ან ძველი ფორმატი — attachment (ერთი ფაილი, 2026-10-30-მდე)
     * @return list<array{name: string, path: string, mime?: string, size?: int}>
     */
    public static function of(array $data): array
    {
        return self::list($data['attachments'] ?? $data['attachment'] ?? null);
    }

    /** ფაილის სრული ბილიკი — მხოლოდ Uploader-ის ფორმატის ბილიკი storage/orders-ში და მხოლოდ თუ არსებობს */
    public static function path(array $file): ?string
    {
        $path = (string)($file['path'] ?? '');
        if (preg_match(self::FILE_PATTERN, $path) !== 1) {
            return null;
        }
        $absolute = storage_path('orders/' . $path);
        return is_file($absolute) ? $absolute : null;
    }

    /** @param list<array> $files */
    public static function delete(array $files): void
    {
        foreach ($files as $file) {
            if (($path = self::path($file)) !== null) {
                @unlink($path);
            }
        }
    }

    public static function megabytes(int $bytes): string
    {
        return (string)round($bytes / 1048576);
    }
}
