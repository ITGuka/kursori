<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\AdminUser;
use Core\Http\Response;

final class DashboardController
{
    public function __construct(private readonly AdminUser $users, private readonly ActivityLog $log)
    {
    }

    public function index(): Response
    {
        return view('admin/dashboard', [
            'title'       => 'Dashboard',
            'usersCount'  => $this->users->count(),
            'activeCount' => $this->users->countActive(),
            'activity'    => $this->log->recent(10),
        ]);
    }
}
