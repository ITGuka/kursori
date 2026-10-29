<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\NewsFeed;
use App\Models\NewsItem;
use App\Models\ScheduledJob;
use App\Services\News\DevToHtml;
use Core\Http\Request;
use Core\Http\Response;

/**
 * სიახლეების რიგი (მოდერაცია): ფილტრი სტატუსით/feed-ით, წინასწარი ნახვა, დამტკიცება/უარყოფა (რამდენიმე ერთად).
 * გამოქვეყნება და თარგმანი ფონზე ხდება (schedule:run) — „ახლავე“ ჯობს რიგში აყენებს.
 */
final class NewsController
{
    private const PER_PAGE = 30;

    public function __construct(
        private readonly NewsItem $news,
        private readonly NewsFeed $feeds,
        private readonly ScheduledJob $jobs,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(Request $request): Response
    {
        $status = (string)$request->query('status', 'new');
        $filters = [
            'status' => isset(NewsItem::STATUSES[$status]) ? $status : '',
            'feed'   => (int)$request->query('feed', 0),
            'q'      => trim($request->string('q', '')),
        ];
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->news->paginate($filters, $page, self::PER_PAGE);

        return view('admin/news/index', [
            'title'      => 'სიახლეები',
            'rows'       => $result['rows'],
            'total'      => $result['total'],
            'page'       => $page,
            'pages'      => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'filters'    => $filters,
            'counts'     => $this->news->counts(),
            'feeds'      => $this->feeds->options(),
            'translated' => setting('news.translate') === true && (string)setting('news.claude_api_key') !== '',
        ]);
    }

    public function show(int $id, DevToHtml $html): Response
    {
        $item = $this->news->find($id) ?? abort(404);
        return view('admin/news/show', [
            'title' => 'სიახლე',
            'item'  => $item,
            'body'  => $html->clean((string)$item['body_html']),
            'feed'  => $item['feed_id'] !== null ? $this->feeds->find((int)$item['feed_id']) : null,
        ]);
    }

    /** ids[] + action: approve | reject | publish (დამტკიცება + გამოქვეყნების ჯობი ახლავე) */
    public function moderate(Request $request): Response
    {
        $ids = array_map('intval', (array)$request->input('ids', []));
        $action = (string)$request->input('action', '');
        $back = (string)$request->input('back', '');
        $back = str_starts_with($back, route('admin.news.index')) ? $back : route('admin.news.index');

        if ($ids === [] || !in_array($action, ['approve', 'reject', 'publish'], true)) {
            return redirect($back)->with('error', 'მონიშნეთ სიახლეები.');
        }
        $changed = $this->news->moderate($ids, $action === 'reject' ? 'rejected' : 'approved');
        $this->log->log($action === 'reject' ? 'news.rejected' : 'news.approved', 'news', null, ['count' => $changed]);

        $message = $action === 'reject' ? "უარყოფილია: $changed." : "დამტკიცებულია: $changed — გამოქვეყნდება გრაფიკით.";
        if ($action === 'publish' && $changed > 0 && ($jobId = $this->jobs->idByCommand('news:publish')) !== null) {
            $this->jobs->queueNow($jobId);
            $message = "დამტკიცებულია: $changed — გამოქვეყნება დაიწყება 5 წუთში (cron), თარგმანით 1–2 წთ სტატიაზე.";
        }
        return redirect($back)->with($changed > 0 ? 'success' : 'error', $changed > 0 ? $message : 'ამ სტატუსის სიახლეები ვერ შეიცვლება.');
    }

    /** წამოღების ჯობი ახლავე (cron-ის შემდეგ ტიკზე) */
    public function fetchNow(): Response
    {
        $jobId = $this->jobs->idByCommand('news:fetch') ?? abort(404);
        $this->jobs->queueNow($jobId);
        $this->log->log('job.queued', 'job', $jobId, ['title' => 'news:fetch']);
        return redirect(route('admin.news.index'))->with('success', 'წამოღება დაიწყება 5 წუთში (cron). შედეგი — „გრაფიკში“.');
    }
}
