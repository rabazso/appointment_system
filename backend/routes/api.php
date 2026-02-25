<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/guest', [AuthController::class, 'guest']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed:relative', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);

Route::get('/appointments/confirm/{appointment}', [AppointmentController::class, 'confirm'])->name("appointments.confirm")->middleware("signed:relative");

Route::get('/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/barber/appointments', [AppointmentController::class, 'barberAppointments']);
    Route::post('/barber/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelBarberAppointment']);
    Route::get('/barber/reviews', [AppointmentController::class, 'barberReviews']);
    Route::get('/user/appointments', [AppointmentController::class, 'userAppointments']);
    Route::post('/user/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelUserAppointment']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1');
    Route::middleware('verified')->group(function () {
        Route::apiResource("reviews", ReviewController::class)->only(["store", "destroy"]);
    });
});
