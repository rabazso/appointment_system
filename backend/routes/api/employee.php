<?php

use App\Http\Controllers\Employee\EmployeeAppointmentController;
use App\Http\Controllers\Employee\EmployeeOwnConfigurationController;
use App\Http\Controllers\Employee\EmployeeOwnTimeOffRequestController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\Employee\EmployeeReviewsController;
use App\Http\Controllers\Employee\ShopSpecialDayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api-token', 'role:employee'])->prefix('/employee')->group(function () {
    Route::get('/appointments', [EmployeeAppointmentController::class, 'index']);
    Route::get('/reviews', [EmployeeReviewsController::class, 'index']);
    Route::patch('/reviews/{review}', [EmployeeReviewsController::class, 'updateVisibility']);

    Route::get('/profile', [EmployeeProfileController::class, 'show']);
    Route::patch('/profile', [EmployeeProfileController::class, 'update']);
    Route::post('/profile/avatar', [EmployeeProfileController::class, 'storeProfilePic']);
    Route::post('/profile/gallery', [EmployeeProfileController::class, 'storeGalleryImg']);
    Route::delete('/profile/gallery/{imgId}', [EmployeeProfileController::class, 'destroyGalleryImg'])->whereNumber('imgId');

    Route::get('/configuration/services', [EmployeeOwnConfigurationController::class, 'services']);
    Route::get('/configuration/schedules', [EmployeeOwnConfigurationController::class, 'schedules']);
    Route::get('/configuration/availability', [EmployeeOwnConfigurationController::class, 'availability']);
    Route::get('/configuration/booking-rules', [EmployeeOwnConfigurationController::class, 'bookingRules']);

    Route::get('/shop-holidays/month', [ShopSpecialDayController::class, 'indexForMonth']);

    Route::post('/appointments/{appointment}/cancel', [EmployeeAppointmentController::class, 'cancelAppointment']);
    Route::post('/appointments/{appointment}/complete', [EmployeeAppointmentController::class, 'completeAppointment']);
    Route::post('/appointments/{appointment}/no-show', [EmployeeAppointmentController::class, 'markNoShow']);

    Route::get('/time-off-requests', [EmployeeOwnTimeOffRequestController::class, 'index']);
    Route::post('/time-off-requests', [EmployeeOwnTimeOffRequestController::class, 'store']);
    Route::delete('/time-off-requests/{employeeTimeOffRequest}', [EmployeeOwnTimeOffRequestController::class, 'cancel']);
});
