<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/services/{id}/schedules', [ScheduleController::class, 'byService']);
Route::get('/services/{serviceId}/available-slots', [ScheduleController::class, 'availableSlots']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/my', [BookingController::class, 'my']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('/payments', [PaymentController::class, 'store']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::patch('/schedules/{id}/availability', [ScheduleController::class, 'setAvailability']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
        Route::get('/reports/summary', [ReportController::class, 'summary']);
        Route::get('/reports/bookings', [ReportController::class, 'bookings']);
    });
});
