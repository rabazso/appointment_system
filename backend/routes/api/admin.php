<?php

use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminAppointmentIndexController;
use App\Http\Controllers\Admin\AdminReviewsController;
use App\Http\Controllers\Admin\AppointmentAffectedPreviewController;
use App\Http\Controllers\Admin\EmployeeAvailabilityController;
use App\Http\Controllers\Admin\EmployeeBookingRulesController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeScheduleController;
use App\Http\Controllers\Admin\EmployeeServicesController;
use App\Http\Controllers\Admin\EmployeeTimeOffRequestController;
use App\Http\Controllers\Admin\ServiceAvailabilityController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ShopImageController;
use App\Http\Controllers\Admin\ShopInformationController;
use App\Http\Controllers\Admin\ShopOpeningHourController;
use App\Http\Controllers\Admin\ShopSpecialDayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api-token', 'role:admin'])->group(function () {
    Route::get('/admin/appointments', [AdminAppointmentIndexController::class, 'index']);
    Route::get('/admin/reviews', [AdminReviewsController::class, 'index']);
    Route::patch('/admin/reviews/{review}', [AdminReviewsController::class, 'updateVisibility']);
    Route::post('/services', [ServiceController::class, 'store']);

    Route::post('/admin/appointments/affected-preview/employee-schedule', [AppointmentAffectedPreviewController::class, 'employeeSchedule']);
    Route::post('/admin/appointments/affected-preview/employee-services', [AppointmentAffectedPreviewController::class, 'employeeServices']);
    Route::post('/admin/appointments/affected-preview/employee-availability', [AppointmentAffectedPreviewController::class, 'employeeAvailability']);
    Route::post('/admin/appointments/affected-preview/employee-booking-rules', [AppointmentAffectedPreviewController::class, 'employeeBookingRules']);
    Route::post('/admin/appointments/affected-preview/service-availability', [AppointmentAffectedPreviewController::class, 'serviceAvailability']);

    Route::post('/appointments/{appointment}/cancel', [AdminAppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/complete', [AdminAppointmentController::class, 'complete']);
    Route::post('/appointments/{appointment}/no-show', [AdminAppointmentController::class, 'markNoShow']);

    Route::patch('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    Route::get('/services/{service}/availability', [ServiceAvailabilityController::class, 'index']);
    Route::post('/services/{service}/availability', [ServiceAvailabilityController::class, 'store']);
    Route::put('/service-availability/{availability}', [ServiceAvailabilityController::class, 'update']);
    Route::delete('/service-availability/{availability}', [ServiceAvailabilityController::class, 'destroy']);

    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

    Route::get('/employees/{employee}/schedules', [EmployeeScheduleController::class, 'index']);
    Route::post('/employees/{employee}/schedules', [EmployeeScheduleController::class, 'store']);
    Route::put('/employee-schedules/{schedule}', [EmployeeScheduleController::class, 'update']);
    Route::delete('/employee-schedules/{schedule}', [EmployeeScheduleController::class, 'destroy']);

    Route::get('/employees/{employee}/services', [EmployeeServicesController::class, 'index']);
    Route::post('/employees/{employee}/services', [EmployeeServicesController::class, 'store']);
    Route::put('/employee-services/{serviceConfiguration}', [EmployeeServicesController::class, 'update']);
    Route::delete('/employee-services/{serviceConfiguration}', [EmployeeServicesController::class, 'destroy']);

    Route::get('/employees/{employee}/availability', [EmployeeAvailabilityController::class, 'index']);
    Route::post('/employees/{employee}/availability', [EmployeeAvailabilityController::class, 'store']);
    Route::put('/employee-availability/{availability}', [EmployeeAvailabilityController::class, 'update']);
    Route::delete('/employee-availability/{availability}', [EmployeeAvailabilityController::class, 'destroy']);

    Route::get('/employees/{employee}/booking-rules', [EmployeeBookingRulesController::class, 'index']);
    Route::post('/employees/{employee}/booking-rules', [EmployeeBookingRulesController::class, 'store']);
    Route::put('/employee-booking-rules/{bookingRules}', [EmployeeBookingRulesController::class, 'update']);
    Route::delete('/employee-booking-rules/{bookingRules}', [EmployeeBookingRulesController::class, 'destroy']);

    Route::post('/employee-time-off-requests', [EmployeeTimeOffRequestController::class, 'store']);
    Route::patch('/employee-time-off-requests/{employeeTimeOffRequest}', [EmployeeTimeOffRequestController::class, 'update']);

    Route::post('/shop-images/batch', [ShopImageController::class, 'storeMultiple']);

    Route::patch('/shop-information/{shopInformation}', [ShopInformationController::class, 'update']);

    Route::post('/shop-special-days', [ShopSpecialDayController::class, 'store']);
    Route::patch('/shop-special-days/{shopSpecialDay}', [ShopSpecialDayController::class, 'update']);
    Route::delete('/shop-special-days/{shopSpecialDay}', [ShopSpecialDayController::class, 'destroy']);

    Route::patch('/shop-opening-hours/{shopOpeningHour}', [ShopOpeningHourController::class, 'update']);

    Route::get('/employee-time-off-requests/month', [EmployeeTimeOffRequestController::class, 'indexForMonth']);
    Route::get('/employee-time-off-requests', [EmployeeTimeOffRequestController::class, 'index']);
    Route::get('/shop-special-days/date', [ShopSpecialDayController::class, 'showByDate']);
    Route::get('/shop-special-days/month', [ShopSpecialDayController::class, 'indexForMonth']);
});
