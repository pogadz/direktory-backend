<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileRoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobCategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

// Public routes with rate limiting and API validation
Route::middleware(['validate.api', 'throttle:10,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Job Categories (public read)
    Route::prefix('job-categories')->group(function () {
        Route::get('/', [JobCategoryController::class, 'index']);
        Route::get('/{id}', [JobCategoryController::class, 'show']);
    });

    /**
     * @hideFromAPIDocumentation
     */
    Route::get('/test', function(){
        return response()->json([
            'message' => 'test'
        ]);
    });
});

// Protected routes with auth, API validation, and rate limiting
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Profile Management Routes
    Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/', [ProfileController::class, 'store']);
        Route::get('/active', [ProfileController::class, 'active']);
        Route::get('/current', [ProfileController::class, 'current']);
        Route::post('/switch', [ProfileController::class, 'switch']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::put('/{id}', [ProfileController::class, 'update']);
        Route::delete('/{id}', [ProfileController::class, 'destroy']);

        // Profile Role Management
        Route::prefix('{profileId}/roles')->group(function () {
            Route::get('/', [ProfileRoleController::class, 'index']);
            Route::post('/assign', [ProfileRoleController::class, 'assign']);
            Route::post('/revoke', [ProfileRoleController::class, 'revoke']);
            Route::post('/sync', [ProfileRoleController::class, 'sync']);
            Route::get('/permissions', [ProfileRoleController::class, 'permissions']);
        });
    });
});

// Admin-only routes for managing Job Categories, Roles and Permissions
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:manage-roles,manage-permissions'])->group(function () {
    // Job Category Management
    Route::prefix('job-categories')->group(function () {
        Route::post('/', [JobCategoryController::class, 'store']);
        Route::put('/{id}', [JobCategoryController::class, 'update']);
        Route::delete('/{id}', [JobCategoryController::class, 'destroy']);
    });
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
    /**
     * @hideFromAPIDocumentation
     */
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Dashboard - requires view-dashboard permission']);
    });
});

Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:edit-users,delete-users'])->group(function () {
    /**
     * @hideFromAPIDocumentation
     */
    Route::get('/manage/users', function () {
        return response()->json(['message' => 'User Management - requires edit or delete users permission']);
    });
});
