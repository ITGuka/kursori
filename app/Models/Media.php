<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database\DB;
use Core\Media\ImageResizer;
use Core\Media\Uploader;
use Core\Settings\Settings;

/**
 * მედია ბიბლიოთეკა: ფაილები public/uploads-ში, მეტამონაცემები `media`-ში, alt/caption ენებზე.
 */
final class Media
{
    public const FOLDER_PATTERN = '/^[\p{L}\p{N} _-]{1,50}$/u';

    /** @var array<int, array|null> მოთხოვნის განმავლობაში უკვე წაკითხული ჩანაწერები */
    private array $found = [];

    public function __construct(
        private readonly DB $db,
        private readonly Uploader $uploader,
        private readonly ImageResizer $resizer,
        private readonly Settings $settings,
    ) {
    }

    /** @param array|null $file $_FILES-ის ელემენტი */
    public function upload(?array $file, ?string $folder, ?int $userId): array
    {
        return $this->register($this->uploader->store($file), $folder, $userId);
    }

    public function import(string $source, ?string $folder, ?int $userId = null): array
    {
        return $this->register($this->uploader->storeLocal($source), $folder, $userId);
    }

    public function find(int $id): ?array
    {
        if (!array_key_exists($id, $this->found)) {
            $this->found[$id] = $this->db->first('SELECT * FROM media WHERE id = ?', [$id]);
        }
        return $this->found[$id];
    }

    /** @return array<string, array{alt: string, caption: string}> lang => ტექსტები */
    public function translations(int $id): array
    {
        $rows = [];
        foreach ($this->db->select('SELECT lang, alt, caption FROM media_translations WHERE media_id = ?', [$id]) as $row) {
            $rows[$row['lang']] = ['alt' => $row['alt'], 'caption' => $row['caption']];
        }
        return $rows;
    }

    public function alt(int $id, string $lang, string $fallbackLang): string
    {
        $alt = $this->db->value(
            "SELECT alt FROM media_translations WHERE media_id = ? AND lang IN (?, ?) AND alt <> ''
             ORDER BY lang = ? DESC LIMIT 1",
            [$id, $lang, $fallbackLang, $lang],
        );
        return (string)($alt ?? '');
    }

    /**
     * @param array{q?: string, folder?: string, type?: string} $filters type: image | document
     * @return array{rows: list<array>, total: int}
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        $where = ['1 = 1'];
        $bindings = [];
        if (($filters['q'] ?? '') !== '') {
            $like = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $where[] = '(m.original_name LIKE ? OR EXISTS (SELECT 1 FROM media_translations t WHERE t.media_id = m.id AND (t.alt LIKE ? OR t.caption LIKE ?)))';
            array_push($bindings, $like, $like, $like);
        }
        if (($filters['folder'] ?? '') !== '') {
            $where[] = 'm.folder = ?';
            $bindings[] = $filters['folder'];
        }
        if (($filters['type'] ?? '') === 'image') {
            $where[] = "m.mime LIKE 'image/%'";
        } elseif (($filters['type'] ?? '') === 'document') {
            $where[] = "m.mime NOT LIKE 'image/%'";
        }
        $whereSql = implode(' AND ', $where);

        $total = (int)$this->db->value("SELECT COUNT(*) FROM media m WHERE $whereSql", $bindings);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $this->db->select("SELECT m.* FROM media m WHERE $whereSql ORDER BY m.id DESC LIMIT $perPage OFFSET $offset", $bindings);
        return ['rows' => $rows, 'total' => $total];
    }

    /** @return list<string> */
    public function folders(): array
    {
        return $this->db->column('SELECT DISTINCT folder FROM media WHERE folder IS NOT NULL ORDER BY folder');
    }

    /** @param array<string, array{alt?: string, caption?: string}> $translations */
    public function update(int $id, ?string $folder, array $translations): void
    {
        $this->db->transaction(function (DB $db) use ($id, $folder, $translations): void {
            $db->update('media', ['folder' => $folder], 'id = ?', [$id]);
            foreach ($translations as $lang => $text) {
                $db->query(
                    'INSERT INTO media_translations (media_id, lang, alt, caption) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE alt = VALUES(alt), caption = VALUES(caption)',
                    [$id, $lang, mb_substr(trim($text['alt'] ?? ''), 0, 255), mb_substr(trim($text['caption'] ?? ''), 0, 500)],
                );
            }
        });
        unset($this->found[$id]);
    }

    /** ჩანაწერი + ორიგინალი და ვარიანტები დისკიდან */
    public function delete(int $id): void
    {
        $media = $this->find($id);
        if ($media === null) {
            return;
        }
        $this->db->delete('media', 'id = ?', [$id]);
        foreach ($this->files($media) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        unset($this->found[$id]);
    }

    /**
     * სად გამოიყენება ფაილი (წაშლამდე გაფრთხილებისთვის). ახალი მოდულები (გვერდები, ბლოკები) აქ დაემატება.
     * @return list<string> ადამიანური აღწერები
     */
    public function usages(int $id): array
    {
        $usages = [];
        foreach ($this->settings->groups() as $group => $definition) {
            foreach ($definition['fields'] as $name => $field) {
                if (($field['type'] ?? '') === 'image' && (string)$this->settings->raw("$group.$name") === (string)$id) {
                    $usages[] = 'პარამეტრები → ' . ($definition['label'] ?? $group) . ' → ' . ($field['label'] ?? $name);
                }
            }
        }

        $title = "(SELECT title FROM page_translations t WHERE t.page_id = p.id ORDER BY t.lang = ? DESC LIMIT 1)";
        $lang = (string)config('app.default_locale');
        foreach ($this->db->column("SELECT $title FROM pages p WHERE p.og_image_id = ?", [$lang, $id]) as $page) {
            $usages[] = "გვერდი „{$page}“ → OG სურათი";
        }
        $rows = $this->db->select(
            "SELECT b.type, $title AS page FROM page_blocks b JOIN pages p ON p.id = b.page_id
             WHERE JSON_CONTAINS(b.media_ids, CAST(? AS JSON))",
            [$lang, (string)$id],
        );
        $blocks = (array)config('blocks', []);
        foreach ($rows as $row) {
            $usages[] = "გვერდი „{$row['page']}“ → ბლოკი „" . ($blocks[$row['type']]['label'] ?? $row['type']) . '“';
        }
        return $usages;
    }

    /** საჯარო URL; ვარიანტი აკლია (პატარა სურათი, GIF, PDF) → ორიგინალი */
    public function url(array $media, string $variant = 'original'): string
    {
        $path = $media['path'];
        if ($variant !== 'original' && isset(self::variants($media)[$variant])) {
            $path = ImageResizer::variantPath($path, $variant);
        }
        return url(config('media.directory') . '/' . $path);
    }

    public static function isImage(array $media): bool
    {
        return str_starts_with((string)$media['mime'], 'image/');
    }

    /** @return array<string, array{width: int, height: int}> */
    public static function variants(array $media): array
    {
        return is_string($media['variants'] ?? null) ? (array)json_decode($media['variants'], true) : [];
    }

    public static function humanSize(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1) . ' MB' : max(1, (int)round($bytes / 1024)) . ' KB';
    }

    /** JSON წარმოდგენა picker-ისა და ატვირთვის პასუხისთვის */
    public function toArray(array $media): array
    {
        return [
            'id'     => (int)$media['id'],
            'name'   => $media['original_name'],
            'url'    => $this->url($media),
            'thumb'  => $this->url($media, 'thumb'),
            'mime'   => $media['mime'],
            'image'  => self::isImage($media),
            'width'  => $media['width'] !== null ? (int)$media['width'] : null,
            'height' => $media['height'] !== null ? (int)$media['height'] : null,
            'size'   => self::humanSize((int)$media['size']),
            'edit'   => route('admin.media.edit', ['id' => $media['id']]),
        ];
    }

    private function register(array $stored, ?string $folder, ?int $userId): array
    {
        $absolute = $this->absolute($stored['path']);
        $variants = ImageResizer::supports($stored['mime']) ? $this->resizer->createVariants($absolute, $stored['mime']) : [];
        // ვარიანტი, რომელიც ორიგინალის ზომისაა, არ ინახება — ზედმეტი ფაილია
        foreach ($variants as $name => $variant) {
            if ($variant['width'] === $stored['width'] && $variant['height'] === $stored['height']) {
                @unlink(ImageResizer::variantPath($absolute, $name));
                unset($variants[$name]);
            }
        }

        $id = $this->db->insert('media', [
            'path'          => $stored['path'],
            'original_name' => $stored['original_name'],
            'mime'          => $stored['mime'],
            'extension'     => $stored['extension'],
            'size'          => $stored['size'],
            'width'         => $stored['width'],
            'height'        => $stored['height'],
            'variants'      => $variants === [] ? null : json_encode(array_map(fn ($v) => ['width' => $v['width'], 'height' => $v['height']], $variants)),
            'folder'        => $folder,
            'uploaded_by'   => $userId,
        ]);
        return $this->find($id);
    }

    /** @return list<string> */
    private function files(array $media): array
    {
        $original = $this->absolute($media['path']);
        $files = [$original];
        foreach (array_keys(self::variants($media)) as $variant) {
            $files[] = ImageResizer::variantPath($original, $variant);
        }
        return $files;
    }

    private function absolute(string $path): string
    {
        return public_path(config('media.directory') . '/' . $path);
    }
}
