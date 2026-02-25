<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\ProfileRoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobCategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\DirectoryController;
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

    // Workers (public read)
    Route::prefix('workers')->group(function () {
        Route::get('/', [WorkerController::class, 'index']);
        Route::get('/{id}', [WorkerController::class, 'show']);
    });

    // Directories (public read)
    Route::prefix('directories')->group(function () {
        Route::get('/', [DirectoryController::class, 'index']);
        Route::get('/{id}', [DirectoryController::class, 'show']);
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

    // Authenticated user CRUD (update self, delete self)
    Route::prefix('user')->group(function () {
        Route::put('/', [UserController::class, 'update']);
        Route::delete('/', [UserController::class, 'destroy']);
    });

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

        // Gallery Management
        Route::prefix('{profileId}/gallery')->group(function () {
            Route::get('/', [GalleryController::class, 'index']);
            Route::post('/', [GalleryController::class, 'store']);
            Route::get('/{id}', [GalleryController::class, 'show']);
            Route::put('/{id}', [GalleryController::class, 'update']);
            Route::delete('/{id}', [GalleryController::class, 'destroy']);
        });

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

// Admin-only routes for managing Users
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:edit-users,delete-users'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
    });
});

// Admin-only routes for managing Job Categories, Roles and Permissions
Route::middleware(['auth:sanctum', 'validate.api', 'throttle:60,1', 'permission:manage-roles,manage-permissions'])->group(function () {
    // Directory Management
    Route::prefix('directories')->group(function () {
        Route::post('/', [DirectoryController::class, 'store']);
        Route::put('/{id}', [DirectoryController::class, 'update']);
        Route::delete('/{id}', [DirectoryController::class, 'destroy']);
    });

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
