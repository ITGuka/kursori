<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BlockController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\CollectionController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\RedirectController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DesignController;
use App\Controllers\Admin\InboxController;
use App\Controllers\Admin\NewsletterController;
use App\Controllers\Admin\LanguageController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\MenuController;
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

    $router->group(['middleware' => ['auth', 'flush_page_cache'], 'where' => ['id' => '\d+']], function (Router $router): void {
        $router->post('/logout', [AuthController::class, 'logout'])->name('logout');
        $router->get('/', [DashboardController::class, 'index'])->name('dashboard');
        $router->post('/todos', [DashboardController::class, 'storeTodo'])->name('todos.store');
        $router->post('/todos/{id}/toggle', [DashboardController::class, 'toggleTodo'])->name('todos.toggle');
        $router->delete('/todos/{id}', [DashboardController::class, 'destroyTodo'])->name('todos.destroy');

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

        $router->get('/pages', [PageController::class, 'index'])->name('pages.index');
        $router->get('/pages/create', [PageController::class, 'create'])->name('pages.create');
        $router->post('/pages', [PageController::class, 'store'])->name('pages.store');
        $router->get('/pages/{id}/edit', [PageController::class, 'edit'])->name('pages.edit');
        $router->put('/pages/{id}', [PageController::class, 'update'])->name('pages.update');
        $router->delete('/pages/{id}', [PageController::class, 'destroy'])->name('pages.destroy');

        $router->get('/pages/{id}/blocks/create', [BlockController::class, 'create'])->name('blocks.create');
        $router->post('/pages/{id}/blocks', [BlockController::class, 'store'])->name('blocks.store');
        $router->post('/pages/{id}/blocks/reorder', [BlockController::class, 'reorder'])->name('blocks.reorder');
        $router->get('/blocks/{id}/edit', [BlockController::class, 'edit'])->name('blocks.edit');
        $router->put('/blocks/{id}', [BlockController::class, 'update'])->name('blocks.update');
        $router->post('/blocks/{id}/toggle', [BlockController::class, 'toggle'])->name('blocks.toggle');
        $router->post('/blocks/{id}/duplicate', [BlockController::class, 'duplicate'])->name('blocks.duplicate');
        $router->delete('/blocks/{id}', [BlockController::class, 'destroy'])->name('blocks.destroy');

        $router->group(['where' => ['type' => '[a-z_]+']], function (Router $router): void {
            $router->get('/collections/{type}', [CollectionController::class, 'index'])->name('collections.index');
            $router->get('/collections/{type}/create', [CollectionController::class, 'create'])->name('collections.create');
            $router->post('/collections/{type}', [CollectionController::class, 'store'])->name('collections.store');
            $router->post('/collections/{type}/reorder', [CollectionController::class, 'reorder'])->name('collections.reorder');
            $router->get('/collections/{type}/categories', [CategoryController::class, 'index'])->name('categories.index');
            $router->get('/collections/{type}/categories/create', [CategoryController::class, 'create'])->name('categories.create');
            $router->post('/collections/{type}/categories', [CategoryController::class, 'store'])->name('categories.store');
        });
        $router->get('/collection-items/{id}/edit', [CollectionController::class, 'edit'])->name('collections.edit');
        $router->put('/collection-items/{id}', [CollectionController::class, 'update'])->name('collections.update');
        $router->post('/collection-items/{id}/toggle', [CollectionController::class, 'toggle'])->name('collections.toggle');
        $router->delete('/collection-items/{id}', [CollectionController::class, 'destroy'])->name('collections.destroy');
        $router->get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        $router->put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        $router->delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        $router->get('/designs', [DesignController::class, 'index'])->name('designs.index');
        $router->get('/designs/create', [DesignController::class, 'create'])->name('designs.create');
        $router->post('/designs', [DesignController::class, 'store'])->name('designs.store');
        $router->post('/designs/reorder', [DesignController::class, 'reorder'])->name('designs.reorder');
        $router->get('/designs/{id}/edit', [DesignController::class, 'edit'])->name('designs.edit');
        $router->put('/designs/{id}', [DesignController::class, 'update'])->name('designs.update');
        $router->post('/designs/{id}/toggle', [DesignController::class, 'toggle'])->name('designs.toggle');
        $router->delete('/designs/{id}', [DesignController::class, 'destroy'])->name('designs.destroy');

        $router->get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
        $router->get('/inbox/{id}', [InboxController::class, 'show'])->name('inbox.show');
        $router->post('/inbox/{id}/unread', [InboxController::class, 'markUnread'])->name('inbox.unread');
        $router->post('/inbox/{id}/status', [InboxController::class, 'status'])->name('inbox.status');
        $router->delete('/inbox/{id}', [InboxController::class, 'destroy'])->name('inbox.destroy');

        $router->get('/newsletter/subscribers', [NewsletterController::class, 'subscribers'])->name('newsletter.subscribers');
        $router->get('/newsletter/subscribers/export', [NewsletterController::class, 'export'])->name('newsletter.export');
        $router->delete('/newsletter/subscribers/{id}', [NewsletterController::class, 'destroySubscriber'])->name('newsletter.subscribers.destroy');
        $router->get('/newsletter', [NewsletterController::class, 'campaigns'])->name('newsletter.campaigns');
        $router->get('/newsletter/create', [NewsletterController::class, 'create'])->name('newsletter.create');
        $router->post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
        $router->get('/newsletter/{id}', [NewsletterController::class, 'edit'])->name('newsletter.edit');
        $router->put('/newsletter/{id}', [NewsletterController::class, 'update'])->name('newsletter.update');
        $router->delete('/newsletter/{id}', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');
        $router->post('/newsletter/{id}/test', [NewsletterController::class, 'test'])->name('newsletter.test');
        $router->post('/newsletter/{id}/start', [NewsletterController::class, 'start'])->name('newsletter.start');
        $router->post('/newsletter/{id}/batch', [NewsletterController::class, 'batch'])->name('newsletter.batch');

        $router->get('/redirects', [RedirectController::class, 'index'])->name('redirects.index');
        $router->get('/redirects/create', [RedirectController::class, 'create'])->name('redirects.create');
        $router->post('/redirects', [RedirectController::class, 'store'])->name('redirects.store');
        $router->delete('/redirects/not-found', [RedirectController::class, 'destroyNotFound'])->name('redirects.not_found.destroy');
        $router->get('/redirects/{id}/edit', [RedirectController::class, 'edit'])->name('redirects.edit');
        $router->put('/redirects/{id}', [RedirectController::class, 'update'])->name('redirects.update');
        $router->delete('/redirects/{id}', [RedirectController::class, 'destroy'])->name('redirects.destroy');

        $router->get('/menus', [MenuController::class, 'index'])->name('menus.index');
        $router->get('/menus/{id}/items/create', [MenuController::class, 'create'])->name('menus.items.create');
        $router->post('/menus/{id}/items', [MenuController::class, 'store'])->name('menus.items.store');
        $router->post('/menus/{id}/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
        $router->get('/menu-items/{id}/edit', [MenuController::class, 'edit'])->name('menus.items.edit');
        $router->put('/menu-items/{id}', [MenuController::class, 'update'])->name('menus.items.update');
        $router->post('/menu-items/{id}/toggle', [MenuController::class, 'toggle'])->name('menus.items.toggle');
        $router->delete('/menu-items/{id}', [MenuController::class, 'destroy'])->name('menus.items.destroy');

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
