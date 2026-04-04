<?php

use App\Http\Middleware\AuthenticateBroadcastRequest;
use App\Http\Middleware\CheckAccountRole;
use App\Http\Middleware\CheckAdminPermission;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureNoProfileSelected;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ValidateApiRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'validate.api'   => ValidateApiRequest::class,
            'account.role'   => CheckAccountRole::class,
            'permission'     => CheckPermission::class,
            'user.only'      => EnsureNoProfileSelected::class,
            'admin.permission' => CheckAdminPermission::class,
            'auth.broadcast' => AuthenticateBroadcastRequest::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthorizationException $e, $request) {
            return response()->json([
                'message' => 'You are not allowed to perform this action.',
            ], 403);
        });
    })->create();
