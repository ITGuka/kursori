<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\ScheduledJob;
use App\Services\Scheduler;
use Core\Http\Request;
use Core\Http\Response;

/**
 * ჯობების გრაფიკი (D58): სია და cron-ის მდგომარეობა, ინტერვალი/ოფციები/აქტიურობა, „ახლავე“ (შემდეგ ტიკზე), ჟურნალი.
 */
final class JobController
{
    public function __construct(private readonly ScheduledJob $jobs, private readonly ActivityLog $log)
    {
    }

    public function index(): Response
    {
        return view('admin/jobs/index', [
            'title'        => 'გრაფიკი (cron)',
            'jobs'         => $this->jobs->all(),
            'labels'       => (array)config('schedule.commands'),
            'lastActivity' => $this->jobs->lastActivity(),
        ]);
    }

    public function show(int $id): Response
    {
        $job = $this->jobs->find($id) ?? abort(404);
        return $this->form($job, $job, []);
    }

    public function update(Request $request, int $id): Response
    {
        $job = $this->jobs->find($id) ?? abort(404);
        $data = [
            'args'             => trim(preg_replace('/\s+/', ' ', $request->string('args', '')) ?? ''),
            'interval_minutes' => max(5, min(10080, (int)$request->input('interval_minutes', 60))),
            'is_active'        => $request->input('is_active') === '1',
        ];
        $errors = [];
        if (mb_strlen($data['args']) > 190 || !Scheduler::validArgs($data['args'])) {
            $errors['args'] = 'დასაშვებია მხოლოდ ოფციები: --limit=3, --dry-run ან რიცხვი (მაგ. feed-ის id).';
        }
        if ($errors !== []) {
            return $this->form($job, $data + $job, $errors);
        }
        $this->jobs->update($id, $data['args'], $data['interval_minutes'], $data['is_active']);
        $this->log->log('job.updated', 'job', $id, ['title' => $job['command']]);
        return redirect(route('admin.jobs.index'))->with('success', 'შენახულია.');
    }

    public function run(int $id): Response
    {
        $job = $this->jobs->find($id) ?? abort(404);
        $this->jobs->queueNow($id);
        $this->log->log('job.queued', 'job', $id, ['title' => $job['command']]);
        return redirect(route('admin.jobs.index'))->with('success', "„{$job['command']}“ გაეშვება cron-ის შემდეგ ტიკზე (≤ 5 წთ).");
    }

    public function runLog(int $id): Response
    {
        $run = $this->jobs->findRun($id) ?? abort(404);
        $job = $this->jobs->find((int)$run['job_id']) ?? abort(404);
        return view('admin/jobs/run', ['title' => 'ჟურნალი', 'run' => $run, 'job' => $job]);
    }

    private function form(array $job, array $data, array $errors): Response
    {
        return view('admin/jobs/form', [
            'title'      => $job['command'],
            'job'        => $job,
            'data'       => $data,
            'label'      => (string)(config('schedule.commands')[$job['command']]['label'] ?? $job['command']),
            'runs'       => $this->jobs->runs((int)$job['id'], 50),
            'formErrors' => $errors,
        ], $errors === [] ? 200 : 422);
    }
}
