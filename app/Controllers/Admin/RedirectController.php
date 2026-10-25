<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\Redirect;
use Core\Http\Request;
use Core\Http\Response;

final class RedirectController
{
    public function __construct(private readonly Redirect $redirects, private readonly ActivityLog $log)
    {
    }

    public function index(Request $request): Response
    {
        $search = trim($request->string('q', ''));
        return view('admin/redirects/index', [
            'title'     => 'გადამისამართებები',
            'redirects' => $this->redirects->all($search),
            'notFound'  => $this->redirects->notFound(),
            'search'    => $search,
            'tab'       => $request->query('tab') === '404' ? '404' : 'redirects',
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->form(null, ['from_path' => Redirect::normalize($request->string('from', '/')), 'to_url' => '', 'code' => 301, 'is_active' => true], []);
    }

    public function store(Request $request): Response
    {
        [$data, $errors] = $this->input($request, null);
        if ($errors !== []) {
            return $this->form(null, $data, $errors);
        }
        $id = $this->redirects->save(null, $data);
        $this->log->log('redirect.created', 'redirect', $id, ['title' => $data['from_path']]);
        return redirect(route('admin.redirects.index'))->with('success', 'გადამისამართება დაემატა.');
    }

    public function edit(int $id): Response
    {
        $redirect = $this->redirects->find($id) ?? abort(404);
        return $this->form($redirect, $redirect, []);
    }

    public function update(Request $request, int $id): Response
    {
        $redirect = $this->redirects->find($id) ?? abort(404);
        [$data, $errors] = $this->input($request, $id);
        if ($errors !== []) {
            return $this->form($redirect, $data, $errors);
        }
        $this->redirects->save($id, $data);
        $this->log->log('redirect.updated', 'redirect', $id, ['title' => $data['from_path']]);
        return redirect(route('admin.redirects.index'))->with('success', 'შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $redirect = $this->redirects->find($id) ?? abort(404);
        $this->redirects->delete($id);
        $this->log->log('redirect.deleted', 'redirect', $id, ['title' => $redirect['from_path']]);
        return redirect(route('admin.redirects.index'))->with('success', 'გადამისამართება წაიშალა.');
    }

    public function destroyNotFound(Request $request): Response
    {
        $id = (int)$request->input('id', 0);
        $this->redirects->deleteNotFound($id > 0 ? $id : null);
        return redirect(route('admin.redirects.index', ['tab' => '404']))->with('success', $id > 0 ? 'ჩანაწერი წაიშალა.' : '404 ჟურნალი გასუფთავდა.');
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(Request $request, ?int $id): array
    {
        $from = Redirect::normalize($request->string('from_path', ''));
        $to = trim($request->string('to_url', ''));
        if ($to !== '' && !preg_match('~^https?://~i', $to)) {
            $to = Redirect::normalize($to);
        }
        $data = ['from_path' => $from, 'to_url' => mb_substr($to, 0, 2000), 'code' => (int)$request->input('code', 301) === 302 ? 302 : 301,
                 'is_active' => $request->input('is_active') === '1'];

        $errors = [];
        if ($from === '/' || str_starts_with($from, '/admin')) {
            $errors['from_path'] = 'მიუთითეთ საიტის ძველი მისამართი (მაგ. /ka/dzveli-gverdi). მთავარი და /admin — არა.';
        } elseif ($this->redirects->fromExists($from, $id)) {
            $errors['from_path'] = 'ამ მისამართიდან გადამისამართება უკვე არსებობს.';
        }
        if ($to === '' || !Redirect::validTarget($to)) {
            $errors['to_url'] = 'მიუთითეთ ახალი მისამართი: /ka/akhali-gverdi ან https://...';
        } elseif ($to === $from) {
            $errors['to_url'] = 'მისამართი თავის თავზე ვერ გადამისამართდება.';
        }
        return [$data, $errors];
    }

    private function form(?array $redirect, array $data, array $errors): Response
    {
        return view('admin/redirects/form', [
            'title'      => $redirect === null ? 'ახალი გადამისამართება' : 'გადამისამართება',
            'redirect'   => $redirect,
            'data'       => $data,
            'formErrors' => $errors,
        ], $errors === [] ? 200 : 422);
    }
}
