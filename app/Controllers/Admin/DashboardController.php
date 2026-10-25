<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\AdminUser;
use App\Models\Collection;
use App\Models\Dashboard;
use App\Models\FormSubmission;
use App\Models\Subscriber;
use App\Models\Todo;
use Core\Http\Request;
use Core\Http\Response;

final class DashboardController
{
    public function __construct(
        private readonly AdminUser $users,
        private readonly ActivityLog $log,
        private readonly FormSubmission $submissions,
        private readonly Subscriber $subscribers,
        private readonly Dashboard $dashboard,
        private readonly Todo $todos,
    ) {
    }

    public function index(): Response
    {
        $lang = (string)config('app.default_locale');
        $collectionCounts = $this->dashboard->collections();
        $collections = [];
        foreach (Collection::types() as $type => $definition) {
            $collections[$type] = [
                'label'  => $definition['label'] ?? $type,
                'icon'   => $definition['icon'] ?? 'fa fa-th-list',
                'counts' => $collectionCounts[$type] ?? ['total' => 0, 'active' => 0],
                'latest' => $this->dashboard->latestItems($type, $lang),
            ];
        }

        return view('admin/dashboard', [
            'title'       => 'Dashboard',
            'usersCount'  => $this->users->count(),
            'activeCount' => $this->users->countActive(),
            'unread'      => array_sum($this->submissions->unreadCounts()),
            'subscribers' => $this->subscribers->counts(),
            'pages'       => $this->dashboard->pages(),
            'media'       => $this->dashboard->media(),
            'collections' => $collections,
            'daily'       => $this->dashboard->daily(30),
            'latestPages' => $this->dashboard->latestPages($lang),
            'messages'    => $this->dashboard->latestSubmissions(),
            'forms'       => FormSubmission::forms(),
            'todos'       => $this->todos->forUser((int)auth()->id()),
            'activity'    => $this->log->recent(8),
        ]);
    }

    public function storeTodo(Request $request): array
    {
        $text = trim(mb_substr((string)$request->input('text', ''), 0, 255));
        $userId = (int)auth()->id();
        if ($text === '') {
            return ['ok' => false, 'error' => 'ჩაწერეთ დავალება.'];
        }
        if ($this->todos->count($userId) >= Todo::MAX) {
            return ['ok' => false, 'error' => 'სია სავსეა — წაშალეთ შესრულებული დავალებები.'];
        }
        return ['ok' => true, 'id' => $this->todos->create($userId, $text), 'text' => $text];
    }

    public function toggleTodo(Request $request, int $id): array
    {
        $this->todos->setDone((int)auth()->id(), $id, $request->input('done') === '1');
        return ['ok' => true];
    }

    public function destroyTodo(int $id): array
    {
        $this->todos->delete((int)auth()->id(), $id);
        return ['ok' => true];
    }
}
