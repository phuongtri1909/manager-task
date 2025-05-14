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
    // Roles
    
    
    // User Role Assignments (only for admin)
    Route::middleware(['check.role:admin'])->group(function () {

        Route::resource('roles', App\Http\Controllers\RoleController::class)->except(['create','store','destroy','show']);

        Route::get('user-roles', [App\Http\Controllers\UserRoleController::class, 'index'])->name('user-roles.index');
        Route::get('user-roles/{user}/edit', [App\Http\Controllers\UserRoleController::class, 'edit'])->name('user-roles.edit');
        Route::put('user-roles/{user}', [App\Http\Controllers\UserRoleController::class, 'update'])->name('user-roles.update');
    });
    
    // Tasks
    Route::resource('tasks', App\Http\Controllers\TaskController::class);
    Route::patch('tasks/{task}/update-status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('tasks/{task}/approve-completion/{assignee}', [App\Http\Controllers\TaskController::class, 'approveCompletion'])->name('tasks.approve-completion');
    Route::get('tasks-statistics', [App\Http\Controllers\TaskController::class, 'statistics'])->name('tasks.statistics');
    
    // Task Extensions
    Route::get('task-extensions', [App\Http\Controllers\TaskExtensionController::class, 'index'])->name('task-extensions.index');
    Route::get('tasks/{task}/extensions/request', [App\Http\Controllers\TaskExtensionController::class, 'request'])->name('task-extensions.request');
    Route::post('tasks/{task}/extensions', [App\Http\Controllers\TaskExtensionController::class, 'store'])->name('task-extensions.store');
    Route::patch('task-extensions/{extension}/respond', [App\Http\Controllers\TaskExtensionController::class, 'respond'])->name('task-extensions.respond');
});
