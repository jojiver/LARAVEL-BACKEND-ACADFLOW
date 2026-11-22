<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ScheduleImportController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => [
    'status' => 'ok',
    'app' => config('app.name'),
    'time' => now()->toIso8601String(),
]);

Route::prefix('auth/v1')->group(function () {
    Route::get('/csrf-token', [AuthController::class, 'csrfToken']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);

    Route::middleware('api.token')->group(function () {
        Route::get('/session', [AuthController::class, 'session']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

Route::middleware('api.token')->group(function () {
    Route::prefix('settings/v1')->group(function () {
        Route::get('/preferences', [SettingsController::class, 'preferences']);
        Route::patch('/preferences', [SettingsController::class, 'updatePreferences']);
    });

    Route::prefix('subjects/v1')->group(function () {
        Route::get('/get-subjects', [SubjectController::class, 'index']);
        Route::get('/get-subject/{subject}', [SubjectController::class, 'show']);
        Route::post('/create-subject', [SubjectController::class, 'store']);
        Route::post('/update-subject/{subject}', [SubjectController::class, 'update']);
        Route::post('/delete-subject/{subject}', [SubjectController::class, 'destroy']);
    });

    Route::prefix('schedules/v1')->group(function () {
        Route::get('/get-schedules', [ScheduleController::class, 'index']);
        Route::post('/create-schedule', [ScheduleController::class, 'store']);
        Route::post('/update-schedule/{schedule}', [ScheduleController::class, 'update']);
        Route::post('/delete-schedule/{schedule}', [ScheduleController::class, 'destroy']);
        Route::post('/import', [ScheduleImportController::class, 'preview']);
        Route::post('/import/confirm', [ScheduleImportController::class, 'confirm']);
    });

    Route::prefix('tasks/v1')->group(function () {
        Route::get('/get-tasks', [TaskController::class, 'index']);
        Route::get('/get-task/{task}', [TaskController::class, 'show']);
        Route::post('/create-task', [TaskController::class, 'store']);
        Route::post('/update-task/{task}', [TaskController::class, 'update']);
        Route::post('/delete-task/{task}', [TaskController::class, 'destroy']);
        Route::get('/productivity', [TaskController::class, 'productivity']);
    });
});
