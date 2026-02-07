<?php

use App\Http\Controllers\Api\AuthController;
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
});
