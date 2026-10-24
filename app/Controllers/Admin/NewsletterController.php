<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Language;
use App\Models\Subscriber;
use App\Services\Newsletter;
use Core\Auth\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\HtmlSanitizer;
use Throwable;

/**
 * Newsletter ადმინში: გამომწერები (სია, ძებნა, CSV, წაშლა) და კამპანიები (შექმნა, ტესტი, გაგზავნა პარტიებად).
 */
final class NewsletterController
{
    private const PER_PAGE = 50;
    private const BATCH = 20;

    public function __construct(
        private readonly Subscriber $subscribers,
        private readonly Campaign $campaigns,
        private readonly Newsletter $newsletter,
        private readonly Language $languages,
        private readonly HtmlSanitizer $sanitizer,
        private readonly ActivityLog $log,
        private readonly Auth $auth,
    ) {
    }

    // ---- გამომწერები ----

    public function subscribers(Request $request): Response
    {
        $filters = ['status' => (string)$request->query('status', ''), 'q' => trim((string)$request->query('q', ''))];
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->subscribers->paginate($filters, $page, self::PER_PAGE);
        return view('admin/newsletter/subscribers', [
            'title'   => 'გამომწერები',
            'rows'    => $result['rows'],
            'total'   => $result['total'],
            'page'    => $page,
            'pages'   => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'filters' => $filters,
            'counts'  => $this->subscribers->counts(),
        ]);
    }

    public function export(Request $request): Response
    {
        $rows = $this->subscribers->all(['status' => (string)$request->query('status', ''), 'q' => trim((string)$request->query('q', ''))]);
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM — Excel-ისთვის
        fputcsv($out, ['email', 'lang', 'status', 'confirmed_at', 'created_at']);
        foreach ($rows as $row) {
            // =, +, -, @ — Excel-ში ფორმულად რომ არ შესრულდეს
            fputcsv($out, array_map(fn ($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'" . $v : $v, array_values($row)));
        }
        rewind($out);
        $csv = (string)stream_get_contents($out);
        fclose($out);
        return (new Response($csv))
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    }

    public function destroySubscriber(int $id): Response
    {
        $subscriber = $this->subscribers->find($id) ?? abort(404);
        $this->subscribers->delete($id);
        $this->log->log('subscriber.deleted', 'subscriber', $id, ['email' => $subscriber['email']]);
        return back(route('admin.newsletter.subscribers'))->with('success', "გამომწერი {$subscriber['email']} წაიშალა.");
    }

    // ---- კამპანიები ----

    public function campaigns(): Response
    {
        return view('admin/newsletter/campaigns', [
            'title'     => 'კამპანიები',
            'campaigns' => $this->campaigns->all(),
            'counts'    => $this->subscribers->counts(),
            'languages' => $this->languages->all(),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null, ['subject' => '', 'body' => '', 'lang' => ''], []);
    }

    public function store(Request $request): Response
    {
        [$data, $errors] = $this->input($request);
        if ($errors !== []) {
            return $this->form(null, $data, $errors);
        }
        $id = $this->campaigns->save(null, $data, $this->auth->id());
        $this->log->log('campaign.created', 'campaign', $id, ['title' => $data['subject']]);
        return redirect(route('admin.newsletter.edit', ['id' => $id]))->with('success', 'კამპანია შენახულია. გაგზავნამდე სცადეთ ტესტური წერილი.');
    }

    public function edit(int $id): Response
    {
        $campaign = $this->campaigns->find($id) ?? abort(404);
        return $this->form($campaign, $campaign, []);
    }

    public function update(Request $request, int $id): Response
    {
        $campaign = $this->campaigns->find($id) ?? abort(404);
        if ($campaign['status'] !== 'draft') {
            return redirect(route('admin.newsletter.edit', ['id' => $id]))->with('error', 'გაგზავნილი კამპანია აღარ იცვლება.');
        }
        [$data, $errors] = $this->input($request);
        if ($errors !== []) {
            return $this->form($campaign, $data, $errors);
        }
        $this->campaigns->save($id, $data, null);
        $this->log->log('campaign.updated', 'campaign', $id, ['title' => $data['subject']]);
        return redirect(route('admin.newsletter.edit', ['id' => $id]))->with('success', 'შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $campaign = $this->campaigns->find($id) ?? abort(404);
        if ($campaign['status'] === 'sending') {
            return back(route('admin.newsletter.campaigns'))->with('error', 'მიმდინარე გაგზავნას ვერ წაშლით.');
        }
        $this->campaigns->delete($id);
        $this->log->log('campaign.deleted', 'campaign', $id, ['title' => $campaign['subject']]);
        return redirect(route('admin.newsletter.campaigns'))->with('success', 'კამპანია წაიშალა.');
    }

    public function test(Request $request, int $id): Response
    {
        $campaign = $this->campaigns->find($id) ?? abort(404);
        $email = trim((string)$request->input('email', ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return redirect(route('admin.newsletter.edit', ['id' => $id]))->with('error', 'მიუთითეთ სწორი ელ-ფოსტა.');
        }
        try {
            $this->newsletter->sendTest($campaign, $email);
        } catch (Throwable $e) {
            return redirect(route('admin.newsletter.edit', ['id' => $id]))->with('error', 'გაგზავნა ვერ მოხერხდა: ' . $e->getMessage() . ' — შეამოწმეთ პარამეტრები → ელ-ფოსტა.');
        }
        return redirect(route('admin.newsletter.edit', ['id' => $id]))->with('success', "ტესტური წერილი გაიგზავნა: $email");
    }

    /** ადრესატების სიის შექმნა; გაგზავნას შემდეგ batch-ები აგრძელებს (JS ან bin/console newsletter:send) */
    public function start(int $id): Response
    {
        $campaign = $this->campaigns->find($id) ?? abort(404);
        if ($campaign['status'] !== 'draft') {
            return redirect(route('admin.newsletter.edit', ['id' => $id]));
        }
        $count = $this->campaigns->start($id);
        $this->log->log('campaign.started', 'campaign', $id, ['title' => $campaign['subject'], 'count' => $count]);
        return redirect(route('admin.newsletter.edit', ['id' => $id]))
            ->with($count > 0 ? 'success' : 'error', $count > 0 ? "გაგზავნა დაიწყო: $count ადრესატი." : 'აქტიური გამომწერი არ არის.');
    }

    /** AJAX: მომდევნო პარტია */
    public function batch(int $id): array
    {
        $this->campaigns->find($id) ?? abort(404);
        return $this->newsletter->sendBatch($id, self::BATCH);
    }

    /** @return array{0: array{subject: string, body: string, lang: string}, 1: array<string, string>} */
    private function input(Request $request): array
    {
        $codes = array_column($this->languages->all(), 'code');
        $lang = (string)$request->input('lang', '');
        $data = [
            'subject' => mb_substr(trim((string)$request->input('subject', '')), 0, 190),
            'body'    => $this->sanitizer->clean((string)$request->input('body', '')),
            'lang'    => in_array($lang, $codes, true) ? $lang : '',
        ];
        $errors = [];
        if ($data['subject'] === '') {
            $errors['subject'] = 'თემა სავალდებულოა.';
        }
        if ($data['body'] === '') {
            $errors['body'] = 'წერილის ტექსტი სავალდებულოა.';
        }
        return [$data, $errors];
    }

    private function form(?array $campaign, array $data, array $errors): Response
    {
        return view('admin/newsletter/form', [
            'title'      => $campaign === null ? 'ახალი კამპანია' : 'კამპანია: ' . $campaign['subject'],
            'campaign'   => $campaign,
            'data'       => $data,
            'formErrors' => $errors,
            'languages'  => $this->languages->all(),
            'stats'      => $campaign !== null ? $this->campaigns->stats((int)$campaign['id']) : null,
            'failures'   => $campaign !== null ? $this->campaigns->failures((int)$campaign['id']) : [],
            'recipients' => $this->subscribers->counts()['active'],
            'adminEmail' => (string)(auth()->user()['email'] ?? ''),
        ], $errors === [] ? 200 : 422);
    }
}
