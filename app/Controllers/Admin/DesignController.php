<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Design;
use App\Models\Language;
use App\Models\Media;
use App\Services\TemplateArchive;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Log;
use RuntimeException;
use Throwable;

/**
 * შეკვეთის ფორმის დიზაინები: /admin/designs. შაბლონი — ZIP (HTML/CSS/JS), კატეგორიები — რამდენიმე.
 */
final class DesignController
{
    public function __construct(
        private readonly Design $designs,
        private readonly Category $categories,
        private readonly Language $languages,
        private readonly Media $media,
        private readonly TemplateArchive $archive,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(Request $request): Response
    {
        $default = $this->languages->defaultCode();
        $filters = ['q' => trim($request->string('q', '')), 'category' => (int)$request->query('category', 0)];
        return view('admin/designs/index', [
            'title'           => 'დიზაინები',
            'collectionType'  => Design::CATEGORIES,
            'rows'            => $this->designs->all($default, $filters['category'], $filters['q']),
            'filters'         => $filters,
            'categoryOptions' => $this->categories->options(Design::CATEGORIES, $default),
            'languages'       => $this->languages->all(),
            'sortable'        => $filters['q'] === '' && $filters['category'] === 0,
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, ['image_id' => null, 'is_active' => true], [], []);
    }

    public function store(Request $request): Response
    {
        [$core, $titles, $categoryIds, $errors] = $this->parse($request, null);
        $file = $request->file('template');
        if ($file === null) {
            $errors['template'] = 'ატვირთეთ შაბლონის ZIP არქივი.';
        }
        if ($errors !== []) {
            return $this->form(null, $core, $titles, $categoryIds, $errors);
        }
        $id = $this->designs->save(null, $core, $titles, $categoryIds);
        try {
            $notice = $this->upload($id, $file, null, null)['notice'];
        } catch (RuntimeException $e) {
            // შაბლონის გარეშე დიზაინი არ რჩება
            $this->designs->delete($id);
            return $this->form(null, $core, $titles, $categoryIds, ['template' => $e->getMessage()]);
        }
        $this->log->log('design.created', 'design', $id, ['title' => $this->title($titles)]);
        return redirect(route('admin.designs.index') . '#design-' . $id)->with('success', 'დიზაინი დაემატა.' . $notice);
    }

    public function edit(int $id): Response
    {
        $design = $this->designs->find($id) ?? abort(404);
        return $this->form($design, $design, $design['translations'], $design['category_ids']);
    }

    public function update(Request $request, int $id): Response
    {
        $design = $this->designs->find($id) ?? abort(404);
        [$core, $titles, $categoryIds, $errors] = $this->parse($request, $design);
        $file = $request->file('template');
        if ($errors === [] && $file !== null) {
            try {
                $result = $this->upload($id, $file, $design['folder'], $design['image_id'] !== null ? (int)$design['image_id'] : null);
                $notice = $result['notice'];
                $core['image_id'] = $result['image_id'] ?? $core['image_id'];
            } catch (RuntimeException $e) {
                $errors['template'] = $e->getMessage();
            }
        }
        if ($errors !== []) {
            return $this->form($design, $core, $titles, $categoryIds, $errors);
        }
        $this->designs->save($id, $core, $titles, $categoryIds);
        $this->log->log('design.updated', 'design', $id, ['title' => $this->title($titles)]);
        return redirect(route('admin.designs.index') . '#design-' . $id)->with('success', 'შენახულია.' . ($notice ?? ''));
    }

    public function toggle(int $id): Response
    {
        $design = $this->designs->find($id) ?? abort(404);
        $this->designs->setActive($id, !$design['is_active']);
        return back(route('admin.designs.index'))->with('success', $design['is_active'] ? 'დაიმალა.' : 'გამოჩნდა.');
    }

    public function destroy(int $id): Response
    {
        $design = $this->designs->find($id) ?? abort(404);
        $title = $this->title($design['translations']);
        $this->designs->delete($id);
        TemplateArchive::remove($design['folder']);
        $this->log->log('design.deleted', 'design', $id, ['title' => $title]);
        return redirect(route('admin.designs.index'))->with('success', "„{$title}“ წაიშალა.");
    }

    /** AJAX: {"ids": [..]} — ახალი რიგი */
    public function reorder(Request $request): array
    {
        $this->designs->reorder(array_map('intval', (array)$request->input('ids', [])));
        return ['ok' => true];
    }

    /**
     * ZIP → ახალი საქაღალდე; წარმატებისას ძველი იშლება. ბარათის სურათი ყოველთვის შაბლონიდან აიღება
     * (TemplateArchive::previewImage) და მედია ბიბლიოთეკაში დაემატება; ძველი სურათი იშლება, თუ სხვაგან არ გამოიყენება.
     * სურათი ვერ მოიძებნა → ძველი რჩება.
     * @return array{notice: string, image_id: ?int}
     */
    private function upload(int $id, array $file, ?string $oldFolder, ?int $oldImageId): array
    {
        $template = $this->archive->extract($file, $id);
        $notice = $template['skipped'] > 0 ? " შაბლონიდან გამოტოვდა {$template['skipped']} დაუშვებელი ან დამალული ფაილი." : '';
        $imageId = null;
        $source = TemplateArchive::previewImage($template['folder'], $template['entry'], (string)($file['name'] ?? ''));
        try {
            $imageId = $source !== null ? (int)$this->media->import($source, 'დიზაინები', auth()->user()['id'] ?? null)['id'] : null;
        } catch (Throwable $e) {
            Log::error($e, ['design' => $id, 'image' => $source]);
        }
        if ($imageId === null) {
            $notice .= ' შაბლონში ბარათის სურათი ვერ მოიძებნა — ჩადეთ ZIP-ის მთავარ საქაღალდეში screenshot.jpg.';
        }
        $this->designs->setTemplate($id, ['folder' => $template['folder'], 'entry' => $template['entry'], 'files' => $template['files'], 'size' => $template['size']]
            + ($imageId !== null ? ['image_id' => $imageId] : []));
        TemplateArchive::remove($oldFolder);
        if ($imageId !== null && $oldImageId !== null && $this->media->usages($oldImageId) === []) {
            $this->media->delete($oldImageId);
        }
        return ['notice' => $notice, 'image_id' => $imageId];
    }

    /**
     * ბარათის სურათი ფორმიდან არ იცვლება — მხოლოდ ZIP-იდან (upload).
     * @return array{0: array, 1: array<string, string>, 2: list<int>, 3: array<string, string>}
     */
    private function parse(Request $request, ?array $design): array
    {
        $errors = [];
        $core = ['image_id' => $design !== null && $design['image_id'] !== null ? (int)$design['image_id'] : null, 'is_active' => $request->input('is_active') === '1'];

        $titles = [];
        $input = (array)$request->input('t', []);
        $default = $this->languages->defaultCode();
        foreach (array_column($this->languages->all(), 'code') as $lang) {
            $titles[$lang] = mb_substr(trim(is_string($input[$lang] ?? null) ? $input[$lang] : ''), 0, 190);
            if ($titles[$lang] === '' && $lang === $default) {
                $errors["t.$lang"] = 'ძირითად ენაზე სავალდებულოა.';
            }
        }

        $allowed = array_keys($this->categories->options(Design::CATEGORIES, $default));
        $categoryIds = array_values(array_intersect($allowed, array_map('intval', (array)$request->input('categories', []))));
        return [$core, $titles, $categoryIds, $errors];
    }

    private function form(?array $design, array $core, array $titles, array $categoryIds, array $errors = []): Response
    {
        return view('admin/designs/form', [
            'title'           => ($design === null ? 'ახალი დიზაინი' : $this->title($titles)) . ' · დიზაინები',
            'collectionType'  => Design::CATEGORIES,
            'design'          => $design,
            'core'            => $core,
            'titles'          => $titles,
            'categoryIds'     => $categoryIds,
            'formErrors'      => $errors,
            'languages'       => $this->languages->all(),
            'defaultLang'     => $this->languages->defaultCode(),
            'categoryOptions' => $this->categories->options(Design::CATEGORIES, $this->languages->defaultCode()),
            'previewUrl'      => $design !== null ? Design::previewUrl($design) : '',
            'maxUpload'       => min(TemplateArchive::MAX_ARCHIVE, self::bytes((string)ini_get('upload_max_filesize')), self::bytes((string)ini_get('post_max_size'))),
        ], $errors === [] ? 200 : 422);
    }

    private function title(array $titles): string
    {
        $default = $this->languages->defaultCode();
        foreach ([$titles[$default] ?? '', ...array_values($titles)] as $title) {
            if ($title !== '') {
                return $title;
            }
        }
        return '—';
    }

    /** php.ini-ის ზომა ('8M') → ბაიტები */
    private static function bytes(string $value): int
    {
        $number = (int)$value;
        return match (strtoupper(substr(trim($value), -1))) {
            'G'     => $number * 1024 ** 3,
            'M'     => $number * 1024 ** 2,
            'K'     => $number * 1024,
            default => $number ?: PHP_INT_MAX,
        };
    }
}
