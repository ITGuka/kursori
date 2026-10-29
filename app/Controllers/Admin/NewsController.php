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
 * სიახლეების ჟურნალი: რა გამოქვეყნდა / ჩავარდა (ფილტრი სტატუსით, feed-ით, ძებნა), წინასწარი ნახვა, „ახლავე“.
 * შერჩევა, თარგმანი და გამოქვეყნება ერთ ჯობშია (news:run, D57).
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
        $status = (string)$request->query('status', '');
        $filters = [
            'status' => isset(NewsItem::STATUSES[$status]) ? $status : '',
            'feed'   => (int)$request->query('feed', 0),
            'q'      => trim($request->string('q', '')),
        ];
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->news->paginate($filters, $page, self::PER_PAGE);
        $jobId = $this->jobs->idByCommand('news:run');

        return view('admin/news/index', [
            'title'      => 'სიახლეები',
            'rows'       => $result['rows'],
            'total'      => $result['total'],
            'page'       => $page,
            'pages'      => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'filters'    => $filters,
            'counts'     => $this->news->counts(),
            'feeds'      => $this->feeds->options(),
            'job'        => $jobId !== null ? $this->jobs->find($jobId) : null,
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

    /** სიახლის ჯობი ახლავე (cron-ის შემდეგ ტიკზე) */
    public function runNow(): Response
    {
        $jobId = $this->jobs->idByCommand('news:run') ?? abort(404);
        $this->jobs->queueNow($jobId);
        $this->log->log('job.queued', 'job', $jobId, ['title' => 'news:run']);
        return redirect(route('admin.news.index'))->with('success', 'სიახლის ჯობი გაეშვება 5 წუთში (cron). შედეგი აქ და „გრაფიკში“ გამოჩნდება.');
    }
}
