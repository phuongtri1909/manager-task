<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Task Management Routes
Route::middleware(['auth'])->group(function () {
    // User Role Assignments (only for admin)
    Route::middleware(['check.role:admin'])->group(function () {

        Route::resource('roles', App\Http\Controllers\RoleController::class)->except(['create', 'store', 'destroy', 'show']);

        Route::get('user-roles', [App\Http\Controllers\UserRoleController::class, 'index'])->name('user-roles.index');
        Route::get('user-roles/{user}/edit', [App\Http\Controllers\UserRoleController::class, 'edit'])->name('user-roles.edit');
        Route::put('user-roles/{user}', [App\Http\Controllers\UserRoleController::class, 'update'])->name('user-roles.update');

        Route::get('tasks/admin', [App\Http\Controllers\TaskController::class, 'indexAdmin'])->name('tasks.index.admin');

        Route::get('tasks/{task}/edit', [App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
    });

    Route::middleware(['check.role:director,deputy-director,department-head,deputy-department-head'])->group(function () {
        Route::get('/tasks/managed', [App\Http\Controllers\TaskController::class, 'managedTasks'])->name('tasks.managed');

        Route::get('/tasks/assigned', [App\Http\Controllers\TaskController::class, 'assignedTasks'])->name('tasks.assigned');

        Route::get('/tasks/{task}/rejection-history/{userId}', [App\Http\Controllers\TaskController::class, 'getRejectionHistory'])
            ->name('tasks.rejection-history')
            ->middleware('auth');
        Route::get('/tasks/pending-approval', [App\Http\Controllers\TaskController::class, 'pendingApproval'])->name('tasks.pending-approval');
        Route::patch('/tasks/{task}/approve-status/{assignee}', [App\Http\Controllers\TaskController::class, 'approveStatus'])->name('tasks.approve-status');
    
        // Task Extensions
        Route::get('task-extensions', [App\Http\Controllers\TaskExtensionController::class, 'index'])->name('task-extensions.index');
        Route::patch('task-extensions/{extension}/respond', [App\Http\Controllers\TaskExtensionController::class, 'respond'])->name('task-extensions.respond');
    });

    Route::middleware(['check.role:deputy-director,department-head,deputy-department-head,staff'])->group(function () {
        Route::get('/tasks/received', [App\Http\Controllers\TaskController::class, 'receivedTasks'])->name('tasks.received');
    });
    // Task creation routes for management with permission - MUST be before the show route
    Route::middleware(['check.can_assign_task'])->group(function () {
        Route::get('tasks/create', [App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
        Route::post('tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    });

    Route::get('/tasks/user-attachments/{id}/download', [App\Http\Controllers\TaskController::class, 'downloadUserAttachment'])
        ->name('tasks.user-attachments.download');
    Route::get('tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');

    // Other task routes
    Route::patch('tasks/{task}/update-status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('tasks/{task}/approve-completion/{assignee}', [App\Http\Controllers\TaskController::class, 'approveCompletion'])->name('tasks.approve-completion');
    
    Route::get('tasks-statistics', [App\Http\Controllers\TaskController::class, 'statistics'])->name('tasks.statistics');

    // Task attachments
    Route::get('task-attachments/{attachment}/download', [App\Http\Controllers\TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');

    Route::get('tasks/{task}/extensions/request', [App\Http\Controllers\TaskExtensionController::class, 'request'])->name('task-extensions.request');
    Route::post('tasks/{task}/extensions', [App\Http\Controllers\TaskExtensionController::class, 'store'])->name('task-extensions.store');
    
});
