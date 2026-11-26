<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('schedules', ScheduleController::class);
    Route::get('/schedules/{schedule}/reminders', [ReminderController::class, 'index']);
    Route::post('/schedules/{schedule}/reminders', [ReminderController::class, 'store']);
    Route::delete('/schedules/{schedule}/reminders/{reminder}', [ReminderController::class, 'destroy']);
});
