<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    // Guest-only routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    // Authenticated admin routes
    Route::middleware(['auth', 'admin.permission:manage-roles,manage-permissions'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Roles CRUD + permission sync
        Route::resource('roles', AdminRoleController::class)->names([
            'index'   => 'admin.roles.index',
            'create'  => 'admin.roles.create',
            'store'   => 'admin.roles.store',
            'show'    => 'admin.roles.show',
            'edit'    => 'admin.roles.edit',
            'update'  => 'admin.roles.update',
            'destroy' => 'admin.roles.destroy',
        ]);
        Route::post('/roles/{role}/permissions', [AdminRoleController::class, 'syncPermissions'])
            ->name('admin.roles.permissions.sync');

        // Permissions CRUD
        Route::resource('permissions', AdminPermissionController::class)->names([
            'index'   => 'admin.permissions.index',
            'create'  => 'admin.permissions.create',
            'store'   => 'admin.permissions.store',
            'show'    => 'admin.permissions.show',
            'edit'    => 'admin.permissions.edit',
            'update'  => 'admin.permissions.update',
            'destroy' => 'admin.permissions.destroy',
        ]);
    });
});
