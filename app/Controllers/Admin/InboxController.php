<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\FormSubmission;
use App\Services\Attachments;
use Core\Http\Request;
use Core\Http\Response;

final class InboxController
{
    private const PER_PAGE = 30;

    public function __construct(private readonly FormSubmission $submissions, private readonly ActivityLog $log)
    {
    }

    public function index(Request $request): Response
    {
        $forms = FormSubmission::forms();
        $form = $request->string('form', '');
        $status = $request->string('status', '');
        $filters = ['form' => isset($forms[$form]) ? $form : '', 'unread' => $request->query('unread') === '1',
                    'status' => isset(FormSubmission::STATUSES[$status]) ? $status : '', 'q' => trim($request->string('q', ''))];
        $page = max(1, (int)$request->query('page', 1));
        $result = $this->submissions->paginate($filters, $page, self::PER_PAGE);

        return view('admin/inbox/index', [
            'title'   => 'Inbox',
            'forms'   => $forms,
            'rows'    => $result['rows'],
            'total'   => $result['total'],
            'page'    => $page,
            'pages'   => max(1, (int)ceil($result['total'] / self::PER_PAGE)),
            'filters' => $filters,
            'unread'  => $this->submissions->unreadCounts(),
        ]);
    }

    public function show(int $id): Response
    {
        $submission = $this->submissions->find($id) ?? abort(404);
        if (!$submission['is_read']) {
            $this->submissions->setRead($id, true);
        }
        return view('admin/inbox/show', [
            'title'      => 'შეტყობინება #' . $id,
            'submission' => $submission,
            'definition' => FormSubmission::forms()[$submission['form']] ?? ['label' => $submission['form'], 'fields' => []],
        ]);
    }

    /** მიმაგრებული ფაილი (შეკვეთები, storage/orders; $index — attachments-ის რიგითი) — მხოლოდ ადმინისთვის, ყოველთვის ჩამოსატვირთად */
    public function attachment(int $id, int $index): Response
    {
        $submission = $this->submissions->find($id) ?? abort(404);
        $attachment = Attachments::of($submission['data'])[$index] ?? abort(404);
        $path = Attachments::path($attachment) ?? abort(404);
        $name = str_replace(['"', '\\'], '', (string)$attachment['name']);
        $fallback = (string)preg_replace('/[^\x20-\x7E]/', '_', $name);

        return new Response((string)file_get_contents($path), 200, [
            'Content-Type'           => 'application/octet-stream',
            'Content-Disposition'    => 'attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($name),
            'Content-Length'         => (string)filesize($path),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }

    public function markUnread(int $id): Response
    {
        $this->submissions->find($id) ?? abort(404);
        $this->submissions->setRead($id, false);
        return redirect(route('admin.inbox.index'))->with('success', 'მონიშნულია წაუკითხავად.');
    }

    public function status(Request $request, int $id): Response
    {
        $this->submissions->find($id) ?? abort(404);
        $status = $request->string('status', '');
        if (!isset(FormSubmission::STATUSES[$status])) {
            abort(422);
        }
        $this->submissions->setStatus($id, $status);
        return redirect(route('admin.inbox.show', ['id' => $id]))->with('success', 'სტატუსი: ' . FormSubmission::STATUSES[$status][0] . '.');
    }

    public function destroy(int $id): Response
    {
        $submission = $this->submissions->find($id) ?? abort(404);
        $this->submissions->delete($id);
        // მიმაგრებული ფაილებიც იშლება
        Attachments::delete(Attachments::of($submission['data']));
        $this->log->log('inbox.deleted', 'form_submission', $id, ['email' => $submission['data']['email'] ?? '']);
        return redirect(route('admin.inbox.index'))->with('success', 'შეტყობინება წაიშალა.');
    }
}
