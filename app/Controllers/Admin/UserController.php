<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ActivityLog;
use App\Models\AdminUser;
use Core\Auth\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Validator;

final class UserController
{
    public function __construct(
        private readonly AdminUser $users,
        private readonly Auth $auth,
        private readonly ActivityLog $log,
    ) {
    }

    public function index(): Response
    {
        return view('admin/users/index', ['title' => 'მომხმარებლები', 'users' => $this->users->all()]);
    }

    public function create(): Response
    {
        return view('admin/users/form', ['title' => 'ახალი მომხმარებელი', 'user' => null]);
    }

    public function store(Request $request): Response
    {
        $data = $this->input($request);
        $validator = Validator::make($data, [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:190|unique:admin_users,email',
            'password' => 'required|string|min:8|max:72|confirmed',
        ]);
        if ($validator->fails()) {
            return back(route('admin.users.create'))->withErrors($validator->errors())->withInput($this->safeInput($data));
        }

        $id = $this->users->create($data);
        $this->log->log('user.created', 'admin_user', $id, ['email' => $data['email']]);
        return redirect(route('admin.users.index'))->with('success', 'მომხმარებელი შეიქმნა.');
    }

    public function edit(int $id): Response
    {
        $user = $this->users->find($id) ?? abort(404);
        return view('admin/users/form', ['title' => 'რედაქტირება: ' . $user['name'], 'user' => $user]);
    }

    public function update(Request $request, int $id): Response
    {
        $user = $this->users->find($id) ?? abort(404);
        $isSelf = $id === $this->auth->id();
        $data = $this->input($request);

        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => "required|email|max:190|unique:admin_users,email,$id",
            'password' => 'nullable|string|min:8|max:72|confirmed',
        ];
        // საკუთარი პაროლის შესაცვლელად საჭიროა მიმდინარე პაროლი
        if ($isSelf && $data['password'] !== '') {
            $rules['current_password'] = 'required';
        }

        $errors = Validator::make($data, $rules)->errors();
        if ($isSelf && $data['password'] !== '' && !isset($errors['current_password'])
            && !password_verify($data['current_password'], $user['password_hash'])) {
            $errors['current_password'] = 'მიმდინარე პაროლი არასწორია.';
        }
        if (!$data['is_active'] && $isSelf) {
            $errors['is_active'] = 'საკუთარ ანგარიშს ვერ გათიშავთ.';
        } elseif (!$data['is_active'] && $user['is_active'] && $this->users->countActive() <= 1) {
            $errors['is_active'] = 'ბოლო აქტიურ მომხმარებელს ვერ გათიშავთ.';
        }
        if ($errors !== []) {
            return back(route('admin.users.edit', ['id' => $id]))->withErrors($errors)->withInput($this->safeInput($data));
        }

        $this->users->update($id, $data);
        if ($isSelf && $data['password'] !== '') {
            // სხვა მოწყობილობებზე სესიები იხურება, მიმდინარე რჩება
            $this->auth->refresh();
        }
        $this->log->log('user.updated', 'admin_user', $id, ['email' => $data['email'], 'password_changed' => $data['password'] !== '']);
        return redirect(route('admin.users.index'))->with('success', 'ცვლილებები შენახულია.');
    }

    public function destroy(int $id): Response
    {
        $user = $this->users->find($id) ?? abort(404);

        if ($id === $this->auth->id()) {
            return back(route('admin.users.index'))->with('error', 'საკუთარ ანგარიშს ვერ წაშლით.');
        }
        if ($user['is_active'] && $this->users->countActive() <= 1) {
            return back(route('admin.users.index'))->with('error', 'ბოლო აქტიურ მომხმარებელს ვერ წაშლით.');
        }

        $this->users->delete($id);
        $this->log->log('user.deleted', 'admin_user', $id, ['email' => $user['email']]);
        return redirect(route('admin.users.index'))->with('success', 'მომხმარებელი წაიშალა.');
    }

    /** @return array{name: string, email: string, password: string, password_confirmation: string, current_password: string, is_active: bool} */
    private function input(Request $request): array
    {
        return [
            'name'                  => trim((string)$request->input('name', '')),
            'email'                 => AdminUser::normalizeEmail((string)$request->input('email', '')),
            'password'              => (string)$request->input('password', ''),
            'password_confirmation' => (string)$request->input('password_confirmation', ''),
            'current_password'      => (string)$request->input('current_password', ''),
            'is_active'             => $request->input('is_active') === '1',
        ];
    }

    /** ფორმაში დასაბრუნებელი ველები — პაროლების გარეშე */
    private function safeInput(array $data): array
    {
        return ['name' => $data['name'], 'email' => $data['email'], 'is_active' => $data['is_active'] ? '1' : '0'];
    }
}
