<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountRoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

// Public routes with rate limiting and API validation
Route::middleware(['validate.api', 'throttle:10,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/test', function(){
        return response()->json([
            'message' => 'test'
        ]);
    });
});

// Protected routes with auth, API validation, and rate limiting
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Account Management Routes
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('/active', [AccountController::class, 'active']);
        Route::get('/current', [AccountController::class, 'current']);
        Route::post('/switch', [AccountController::class, 'switch']);
        Route::get('/{id}', [AccountController::class, 'show']);
        Route::put('/{id}', [AccountController::class, 'update']);
        Route::delete('/{id}', [AccountController::class, 'destroy']);

        // Account Role Management
        Route::prefix('{accountId}/roles')->group(function () {
            Route::get('/', [AccountRoleController::class, 'index']);
            Route::post('/assign', [AccountRoleController::class, 'assign']);
            Route::post('/revoke', [AccountRoleController::class, 'revoke']);
            Route::post('/sync', [AccountRoleController::class, 'sync']);
            Route::get('/permissions', [AccountRoleController::class, 'permissions']);
        });
    });
});

// Admin-only routes for managing Roles and Permissions
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:manage-roles,manage-permissions'])->group(function () {
    // Role Management
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);

        // Role-Permission Management
        Route::post('/{id}/permissions/assign', [RoleController::class, 'assignPermissions']);
        Route::post('/{id}/permissions/revoke', [RoleController::class, 'revokePermissions']);
        Route::post('/{id}/permissions/sync', [RoleController::class, 'syncPermissions']);
    });

    // Permission Management
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/by-category', [PermissionController::class, 'indexByCategory']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::get('/{id}', [PermissionController::class, 'show']);
        Route::put('/{id}', [PermissionController::class, 'update']);
        Route::delete('/{id}', [PermissionController::class, 'destroy']);
    });
});

// Example: Protected routes using permission-based middleware
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:view-dashboard'])->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Dashboard - requires view-dashboard permission']);
    });
});

Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:edit-users,delete-users'])->group(function () {
    Route::get('/manage/users', function () {
        return response()->json(['message' => 'User Management - requires edit or delete users permission']);
    });
});
