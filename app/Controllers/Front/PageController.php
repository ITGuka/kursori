<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Blocks\BlockRegistry;
use App\Blocks\BlockResolver;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageBlock;
use Core\Auth\Auth;
use Core\Http\HttpException;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Log;
use Core\Support\Str;
use Core\View\View;
use Throwable;

/**
 * ფრონტის გვერდები: /{lang} (მთავარი) და /{lang}/{slug}. შესულ ადმინს მონახაზებიც უჩანს (წინასწარი ხედი).
 */
final class PageController
{
    public function __construct(
        private readonly Page $pages,
        private readonly PageBlock $blocks,
        private readonly BlockRegistry $registry,
        private readonly BlockResolver $resolver,
        private readonly Language $languages,
        private readonly Auth $auth,
        private readonly View $view,
    ) {
    }

    public function home(string $lang): Response
    {
        $found = $this->pages->home($lang, $this->auth->check()) ?? throw new HttpException(404);
        return $this->render($found, $lang);
    }

    public function show(Request $request, string $lang, string $slug): Response
    {
        $found = $this->pages->findBySlug($lang, $slug, $this->auth->check()) ?? throw new HttpException(404);
        return $this->render($found, $lang);
    }

    /** @param array{page: array, translation: array} $found */
    private function render(array $found, string $lang): Response
    {
        $page = $found['page'];
        $translation = $found['translation'];
        $pageId = (int)$page['id'];
        $default = $this->languages->defaultCode();
        $breadcrumbs = $this->pages->ancestors($pageId, $lang);

        $header = '';
        $content = '';
        foreach ($this->blocks->forPage($pageId, true) as $index => $block) {
            $definition = $this->registry->get($block['type']);
            if ($definition === null) {
                continue;
            }
            $data = $this->resolver->resolve($definition['fields'], $block['settings'], $block['content'][$lang] ?? [], $block['content'][$default] ?? [], $lang);
            $html = $this->renderBlock($definition['view'], ['data' => $data, 'block' => $block, 'page' => $translation, 'breadcrumbs' => $breadcrumbs]);

            // header ბლოკი (სლაიდერი / გვერდის სათაური) navbar-თან ერთ კონტეინერში, მხოლოდ პირველი
            if ($index === 0 && !empty($definition['header'])) {
                $header = $html;
            } else {
                $content .= $html;
            }
        }

        // Poseify-ის navbar absolute-ია: header ბლოკის გარეშე შიგთავსი მის ქვეშ მოექცეოდა
        if ($header === '') {
            $header = $this->view->render('front/blocks/page_header', [
                'data' => ['title' => '', 'image' => null, 'breadcrumb' => !$page['is_home']],
                'page' => $translation,
                'breadcrumbs' => $breadcrumbs,
            ]);
        }

        $siteName = (string)setting('general.site_name');
        $alternates = $this->alternates($pageId);

        return view('front/page', [
            'header'  => $header,
            'content' => $content,
            'preview' => $page['status'] !== 'published' ? 'მონახაზი — ჩანს მხოლოდ ადმინისთვის' : null,
            'meta'    => [
                'title'          => $translation['meta_title'] !== '' ? $translation['meta_title']
                                    : ($page['is_home'] ? (setting('seo.meta_title') ?: $siteName) : $translation['title'] . ' · ' . $siteName),
                'description'    => $translation['meta_description'] !== '' ? $translation['meta_description'] : Str::limit((string)setting('seo.meta_description'), 300),
                'image'          => media_url($page['og_image_id'], 'large') ?: null,
                'canonical'      => $this->pages->url($pageId, $lang, true),
                'alternates'     => $page['status'] === 'published' ? $alternates + (isset($alternates[$default]) ? ['x-default' => $alternates[$default]] : []) : [],
                'language_links' => $this->languageLinks($alternates),
                'noindex'        => $page['status'] !== 'published',
            ],
        ]);
    }

    private function renderBlock(string $view, array $data): string
    {
        try {
            return $this->view->render($view, $data);
        } catch (Throwable $e) {
            // ერთი გაფუჭებული ბლოკი მთელ გვერდს არ აგდებს (debug რეჟიმში — აგდებს, რომ შეცდომა ჩანდეს)
            if (config('app.debug')) {
                throw $e;
            }
            Log::error($e, ['view' => $view, 'block' => $data['block']['id'] ?? null]);
            return '';
        }
    }

    /** @return array<string, string> lang => იმავე გვერდის URL აქტიურ ენებზე */
    private function alternates(int $pageId): array
    {
        $urls = [];
        foreach ($this->languages->activeCodes() as $code) {
            $url = $this->pages->url($pageId, $code);
            if ($url !== '') {
                $urls[$code] = $url;
            }
        }
        return $urls;
    }

    /** ენების გადამრთველი: თარგმანი არ არის → იმ ენის მთავარი გვერდი */
    private function languageLinks(array $alternates): array
    {
        $links = [];
        foreach ($this->languages->activeCodes() as $code) {
            $links[$code] = $alternates[$code] ?? url($code);
        }
        return $links;
    }
}
