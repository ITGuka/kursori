<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\AdminUser;
use App\Models\FormSubmission;
use App\Models\Subscriber;
use Core\Http\Response;

final class DashboardController
{
    public function __construct(
        private readonly AdminUser $users,
        private readonly ActivityLog $log,
        private readonly FormSubmission $submissions,
        private readonly Subscriber $subscribers,
    ) {
    }

    public function index(): Response
    {
        return view('admin/dashboard', [
            'title'       => 'Dashboard',
            'usersCount'  => $this->users->count(),
            'activeCount' => $this->users->countActive(),
            'unread'      => array_sum($this->submissions->unreadCounts()),
            'subscribers' => $this->subscribers->counts()['active'],
            'activity'    => $this->log->recent(10),
        ]);
    }
}
