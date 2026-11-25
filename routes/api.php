<?php

use App\Http\Controllers\Api\{AuthController, ClientController, NotificationController, SupervisorController};
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('health', fn () => ['status' => 'ok']);

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'read']);

    // Supervisors record shift production
    Route::middleware(EnsureRole::class.':supervisor,admin')->prefix('supervisor')->group(function () {
        Route::get('shifts', [SupervisorController::class, 'shifts']);
        Route::get('shifts/{shift}', [SupervisorController::class, 'show']);
        Route::post('shifts/{shift}/start', [SupervisorController::class, 'start']);
        Route::post('shifts/{shift}/complete', [SupervisorController::class, 'complete']);
        Route::get('production-records', [SupervisorController::class, 'records']);
        Route::post('production-records', [SupervisorController::class, 'store']);
        Route::put('production-records/{record}', [SupervisorController::class, 'update']);
    });

    // Clients view their own performance (admins may pass ?client_id=)
    Route::middleware(EnsureRole::class.':client,admin')->prefix('client')->group(function () {
        Route::get('dashboard', [ClientController::class, 'dashboard']);
        Route::get('reports/{type}', [ClientController::class, 'report']);
        Route::get('reports/{type}/export', [ClientController::class, 'export']);
        Route::get('production-records', [ClientController::class, 'records']);
        Route::get('assignments', [ClientController::class, 'assignments']);
        Route::get('targets', [ClientController::class, 'targets']);
    });
});
