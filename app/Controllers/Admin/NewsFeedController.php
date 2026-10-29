<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\NewsFeed;
use App\Services\News\NewsFetcher;
use App\Services\News\NewsPublisher;
use Core\Http\Request;
use Core\Http\Response;
use Throwable;

/**
 * სიახლეების feed-ები: dev.to ტეგები → ბლოგის კატეგორია და ფილტრები. „ტესტი“ — კანდიდატები შენახვის გარეშე.
 */
final class NewsFeedController
{
    public function __construct(
        private readonly NewsFeed $feeds,
        private readonly Category $categories,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(): Response
    {
        return view('admin/news/feeds', [
            'title' => 'სიახლეები — feed-ები',
            'feeds' => $this->feeds->all((string)config('app.default_locale')),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, [
            'name' => '', 'tags' => '', 'exclude_tags' => 'career,startup,buildinpublic,marketing,business,saas,sponsored',
            'category_id' => null, 'top_days' => 7, 'require_image' => true, 'is_active' => true,
        ], []);
    }

    public function store(Request $request): Response
    {
        [$data, $errors] = $this->input($request);
        if ($errors !== []) {
            return $this->form(null, $data, $errors);
        }
        $id = $this->feeds->save(null, $data);
        $this->log->log('news_feed.created', 'news_feed', $id, ['name' => $data['name']]);
        return redirect(route('admin.news.feeds.index'))->with('success', 'Feed დაემატა.');
    }

    public function edit(int $id): Response
    {
        $feed = $this->feeds->find($id) ?? abort(404);
        return $this->form($feed, $feed, []);
    }

    public function update(Request $request, int $id): Response
    {
        $feed = $this->feeds->find($id) ?? abort(404);
        [$data, $errors] = $this->input($request);
        if ($errors !== []) {
            return $this->form($feed, $data, $errors);
        }
        $this->feeds->save($id, $data);
        $this->log->log('news_feed.updated', 'news_feed', $id, ['name' => $data['name']]);
        return redirect(route('admin.news.feeds.index'))->with('success', 'შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $feed = $this->feeds->find($id) ?? abort(404);
        $this->feeds->delete($id);
        $this->log->log('news_feed.deleted', 'news_feed', $id, ['name' => $feed['name']]);
        return redirect(route('admin.news.feeds.index'))->with('success', 'Feed წაიშალა. წამოღებული სიახლეები და ბლოგის სტატიები დარჩა.');
    }

    /** რას წამოიღებდა feed ახლა (dev.to API, ტეგზე ~1 წმ) — არაფერი ინახება */
    public function test(int $id, NewsFetcher $fetcher): Response
    {
        $feed = $this->feeds->find($id) ?? abort(404);
        set_time_limit(120);
        try {
            $candidates = array_values($fetcher->candidates($feed));
            $error = null;
        } catch (Throwable $e) {
            [$candidates, $error] = [[], $e->getMessage()];
        }
        return view('admin/news/feed-test', [
            'title'      => 'Feed-ის ტესტი',
            'feed'       => $feed,
            'candidates' => $candidates,
            'error'      => $error,
        ]);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(Request $request): array
    {
        $int = fn (string $key, int $min, int $max, int $default) => max($min, min($max, (int)$request->input($key, $default)));
        $categoryId = (int)$request->input('category_id', 0);
        $data = [
            'name'          => mb_substr(trim($request->string('name', '')), 0, 100),
            'tags'          => implode(',', NewsFeed::parseTags($request->string('tags', ''))),
            'exclude_tags'  => implode(',', NewsFeed::parseTags($request->string('exclude_tags', ''))),
            'category_id'   => $categoryId > 0 ? $categoryId : null,
            'top_days'      => $int('top_days', 1, 30, 7),
            'require_image' => (int)($request->input('require_image') === '1'),
            'is_active'     => (int)($request->input('is_active') === '1'),
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'მიუთითეთ სახელი.';
        }
        if ($data['tags'] === '') {
            $errors['tags'] = 'მიუთითეთ მინიმუმ ერთი dev.to ტეგი (მაგ. php, laravel).';
        } elseif (count(NewsFeed::parseTags($data['tags'])) > 10) {
            $errors['tags'] = 'მაქსიმუმ 10 ტეგი (თითო ტეგი — ცალკე მოთხოვნა dev.to-ზე).';
        }
        if ($data['category_id'] !== null && ($this->categories->find($data['category_id'])['collection'] ?? '') !== NewsPublisher::COLLECTION) {
            $errors['category_id'] = 'აირჩიეთ ბლოგის კატეგორია.';
        }
        return [$data, $errors];
    }

    private function form(?array $feed, array $data, array $errors): Response
    {
        return view('admin/news/feed-form', [
            'title'      => $feed === null ? 'ახალი feed' : 'Feed — ' . $feed['name'],
            'feed'       => $feed,
            'data'       => $data,
            'categories' => $this->categories->options(NewsPublisher::COLLECTION, (string)config('app.default_locale')),
            'formErrors' => $errors,
        ], $errors === [] ? 200 : 422);
    }
}
