<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Media;
use Core\Auth\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Core\Media\UploadException;

final class MediaController
{
    private const PER_PAGE = 40;
    private const PICKER_PER_PAGE = 24;

    public function __construct(
        private readonly Media $media,
        private readonly Language $languages,
        private readonly ActivityLog $log,
        private readonly Auth $auth,
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = $this->filters($request);
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->media->paginate($filters, $page, self::PER_PAGE);

        return view('admin/media/index', [
            'title'   => 'მედია',
            'items'   => $result['rows'],
            'total'   => $result['total'],
            'page'    => $page,
            'pages'   => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'filters' => $filters,
            'folders' => $this->media->folders(),
        ]);
    }

    /** ერთი ფაილი თითო მოთხოვნაზე (JS ატვირთავს რიგრიგობით, პროგრესით) */
    public function store(Request $request): Response
    {
        $folder = $this->folder($request->input('folder'));
        $postMax = self::iniBytes((string)ini_get('post_max_size'));

        try {
            if ($folder === false) {
                throw new UploadException('საქაღალდის სახელი: ასოები, ციფრები, ჰიფენი, ქვედა ტირე (მაქს. 50).');
            }
            // post_max_size-ის გადაჭარბებისას PHP body-ს მთლიანად აგდებს — ფაილიც აღარ ჩანს
            if ($postMax > 0 && (int)$request->server('CONTENT_LENGTH', 0) > $postMax) {
                throw new UploadException((string)config('media.messages.too_large', 'File too large.'));
            }
            $media = $this->media->upload($request->file('file'), $folder, $this->auth->id());
        } catch (UploadException $e) {
            if ($request->wantsJson()) {
                return Response::json(['error' => $e->getMessage()], 422);
            }
            return back(route('admin.media.index'))->with('error', $e->getMessage());
        }

        $this->log->log('media.uploaded', 'media', (int)$media['id'], ['name' => $media['original_name']]);
        if ($request->wantsJson()) {
            return Response::json(['media' => $this->media->toArray($media)], 201);
        }
        return redirect(route('admin.media.index'))->with('success', 'ფაილი აიტვირთა.');
    }

    public function edit(int $id): Response
    {
        $media = $this->media->find($id) ?? abort(404);
        return view('admin/media/edit', [
            'title'        => $media['original_name'],
            'media'        => $media,
            'model'        => $this->media,
            'translations' => $this->media->translations($id),
            'languages'    => $this->languages->all(),
            'usages'       => $this->media->usages($id),
            'folders'      => $this->media->folders(),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $media = $this->media->find($id) ?? abort(404);
        $folder = $this->folder($request->input('folder'));
        if ($folder === false) {
            return back(route('admin.media.edit', ['id' => $id]))
                ->withErrors(['folder' => 'საქაღალდის სახელი: ასოები, ციფრები, ჰიფენი, ქვედა ტირე (მაქს. 50).'])
                ->withInput(['folder' => (string)$request->input('folder', ''), 'texts' => (array)$request->input('texts', [])]);
        }

        $texts = (array)$request->input('texts', []);
        $translations = [];
        foreach (array_column($this->languages->all(), 'code') as $code) {
            $translations[$code] = [
                'alt'     => is_string($texts[$code]['alt'] ?? null) ? $texts[$code]['alt'] : '',
                'caption' => is_string($texts[$code]['caption'] ?? null) ? $texts[$code]['caption'] : '',
            ];
        }

        $this->media->update($id, $folder, $translations);
        $this->log->log('media.updated', 'media', $id, ['name' => $media['original_name']]);
        return redirect(route('admin.media.edit', ['id' => $id]))->with('success', 'შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $media = $this->media->find($id) ?? abort(404);
        $usages = $this->media->usages($id);
        if ($usages !== []) {
            return back(route('admin.media.index'))->with('error', 'ფაილი გამოიყენება და ვერ წაიშლება: ' . implode('; ', $usages) . '.');
        }

        $this->media->delete($id);
        $this->log->log('media.deleted', 'media', $id, ['name' => $media['original_name']]);
        return redirect(route('admin.media.index'))->with('success', "ფაილი „{$media['original_name']}“ წაიშალა.");
    }

    /** JSON სია მედიის ასარჩევი ფანჯრისთვის */
    public function picker(Request $request): array
    {
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->media->paginate($this->filters($request), $page, self::PICKER_PER_PAGE);
        return [
            'items' => array_map([$this->media, 'toArray'], $result['rows']),
            'page'  => $page,
            'pages' => max(1, (int)ceil($result['total'] / self::PICKER_PER_PAGE)),
            'total' => $result['total'],
        ];
    }

    /** @return array{q: string, folder: string, type: string} */
    private function filters(Request $request): array
    {
        $type = (string)$request->query('type', '');
        return [
            'q'      => trim((string)$request->query('q', '')),
            'folder' => trim((string)$request->query('folder', '')),
            'type'   => in_array($type, ['image', 'document'], true) ? $type : '',
        ];
    }

    /** @return string|null|false null — საქაღალდის გარეშე, false — არასწორი სახელი */
    private function folder(mixed $value): string|null|false
    {
        $folder = trim(is_string($value) ? $value : '');
        if ($folder === '') {
            return null;
        }
        return preg_match(Media::FOLDER_PATTERN, $folder) ? $folder : false;
    }

    private static function iniBytes(string $value): int
    {
        $number = (int)$value;
        return match (strtolower(substr(trim($value), -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
