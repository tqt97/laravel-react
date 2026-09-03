<?php

use App\Http\Controllers\Admin\Users\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('admin/users', UserController::class)
        ->except(['show'])
        ->middleware('can:viewAny,'.User::class)
        ->names('admin.users');
    Route::post('admin/users/bulk-destroy', [UserController::class, 'bulkDestroy'])
        ->middleware('can:deleteAny,'.User::class)
        ->name('admin.users.bulk-destroy');
    Route::post('admin/users/bulk-restore', [UserController::class, 'bulkRestore'])
        ->middleware('can:restoreAny,'.User::class)
        ->name('admin.users.bulk-restore');
    Route::post('admin/users/bulk-force-destroy', [UserController::class, 'bulkForceDestroy'])
        ->middleware('can:forceDeleteAny,'.User::class)
        ->name('admin.users.bulk-force-destroy');
    Route::patch('admin/users/{user}/restore', [UserController::class, 'restore'])
        ->middleware('can:restore,user')
        ->withTrashed()
        ->name('admin.users.restore');
    Route::delete('admin/users/{user}/force', [UserController::class, 'forceDestroy'])
        ->middleware('can:forceDelete,user')
        ->withTrashed()
        ->name('admin.users.force-destroy');
});

require __DIR__.'/settings.php';
