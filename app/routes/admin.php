<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\UserController;
use Core\Http\Router;

/** @var Router $router */

$router->group(['prefix' => '/admin', 'name' => 'admin.'], function (Router $router): void {
    $router->group(['middleware' => ['guest']], function (Router $router): void {
        $router->get('/login', [AuthController::class, 'showLogin'])->name('login');
        $router->post('/login', [AuthController::class, 'login'])->name('login.attempt');
    });

    $router->group(['middleware' => ['auth'], 'where' => ['id' => '\d+']], function (Router $router): void {
        $router->post('/logout', [AuthController::class, 'logout'])->name('logout');
        $router->get('/', [DashboardController::class, 'index'])->name('dashboard');

        $router->get('/users', [UserController::class, 'index'])->name('users.index');
        $router->get('/users/create', [UserController::class, 'create'])->name('users.create');
        $router->post('/users', [UserController::class, 'store'])->name('users.store');
        $router->get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        $router->put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        $router->delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
