<?php

use App\Http\Controllers\Public\AppointmentController;
use App\Http\Controllers\Public\AppointmentReviewController;
use App\Http\Controllers\Public\AuthController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\CurrentUserController;
use App\Http\Controllers\Public\EmployeeController;
use App\Http\Controllers\Public\EmployeeImageController;
use App\Http\Controllers\Public\ForgotPasswordController;
use App\Http\Controllers\Public\ResetPasswordController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\ShopImageController;
use App\Http\Controllers\Public\ShopInformationController;
use App\Http\Controllers\Public\ShopOpeningHourController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:10,1']);

Route::post('/login', [AuthController::class, 'customerLogin'])->middleware(['guest', 'throttle:10,1']);
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
    Route::post('/employee/login', [AuthController::class, 'employeeLogin']);
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');

Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->whereNumber('employee');
Route::get('/employee-images/{employeeImage}', [EmployeeImageController::class, 'showOriginal'])->name('employee-images.original');
Route::get('/employee-images/{employeeImage}/preview', [EmployeeImageController::class, 'showPreview'])->name('employee-images.preview');
Route::get('/shop-images', [ShopImageController::class, 'index']);
Route::get('/shop-images/{shopImage}', [ShopImageController::class, 'showOriginal'])->name('shop-images.original');
Route::get('/shop-images/{shopImage}/preview', [ShopImageController::class, 'showPreview'])->name('shop-images.preview');
Route::get('/services', [ServiceController::class, 'index']);

Route::get('/shop-opening-hours', [ShopOpeningHourController::class, 'index']);
Route::get('/shop-information', [ShopInformationController::class, 'show']);
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('optional.api-token');

Route::get('/appointments/confirm/{appointment}', [AppointmentController::class, 'confirm'])
    ->name('appointments.confirm')
    ->middleware('signed:relative');

Route::prefix('/booking')->group(function () {
    Route::get('/services', [BookingController::class, 'services']);
    Route::get('/employees', [BookingController::class, 'employees']);
    Route::get('/summary', [BookingController::class, 'summary']);
    Route::get('/days', [BookingController::class, 'days']);
    Route::get('/slots', [BookingController::class, 'slots']);
});

Route::middleware('auth.api-token')->group(function () {
    Route::get('/user', [CurrentUserController::class, 'show']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:customer')->group(function () {
        Route::get('/user/appointments', [AppointmentController::class, 'userAppointments']);
        Route::post('/user/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelUserAppointment']);
        Route::post('/user/appointments/{appointment}/review', [AppointmentReviewController::class, 'store']);
    });
});

Route::post('/forgot-password', ForgotPasswordController::class)
    ->middleware(['guest', 'throttle:3,1'])
    ->name('password.email');

Route::post('/reset-password', ResetPasswordController::class)
    ->middleware('guest')
    ->name('password.update');
