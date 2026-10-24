<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LanguageController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\TranslationController;
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

        $router->get('/languages', [LanguageController::class, 'index'])->name('languages.index');
        $router->get('/languages/create', [LanguageController::class, 'create'])->name('languages.create');
        $router->post('/languages', [LanguageController::class, 'store'])->name('languages.store');
        $router->get('/languages/{id}/edit', [LanguageController::class, 'edit'])->name('languages.edit');
        $router->put('/languages/{id}', [LanguageController::class, 'update'])->name('languages.update');
        $router->delete('/languages/{id}', [LanguageController::class, 'destroy'])->name('languages.destroy');

        $router->get('/translations', [TranslationController::class, 'index'])->name('translations.index');
        $router->put('/translations', [TranslationController::class, 'update'])->name('translations.update');
        $router->post('/translations', [TranslationController::class, 'store'])->name('translations.store');
        $router->delete('/translations/{id}', [TranslationController::class, 'destroy'])->name('translations.destroy');

        $router->get('/media', [MediaController::class, 'index'])->name('media.index');
        $router->post('/media', [MediaController::class, 'store'])->name('media.store');
        $router->get('/media/picker', [MediaController::class, 'picker'])->name('media.picker');
        $router->get('/media/{id}/edit', [MediaController::class, 'edit'])->name('media.edit');
        $router->put('/media/{id}', [MediaController::class, 'update'])->name('media.update');
        $router->delete('/media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

        $router->get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        $router->put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
