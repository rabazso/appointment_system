<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeServiceConfigurationController;
use App\Http\Controllers\EmployeeServiceController;
use App\Http\Controllers\EmployeeVersionController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ResetPasswordTokenController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceVersionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/guest', [AuthController::class, 'guest']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/versions/valid', [ServiceController::class, 'indexServicesWithValidVersion']);
Route::get('/service-versions', [ServiceVersionController::class, 'index']);
Route::get('/service-versions/valid-at', [ServiceVersionController::class, 'ShowServiceVersionValidAt']);

Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/versions/valid', [EmployeeController::class, 'indexEmployeesWithValidVersion']);
Route::get('/employee-versions', [EmployeeVersionController::class, 'index']);
Route::get('/employee-versions/valid-at', [EmployeeVersionController::class, 'ShowEmployeeVersionValidAt']);
Route::get('/employee-service-configurations', [EmployeeServiceConfigurationController::class, 'index']);
Route::get('/employee-service-configurations/valid-at', [EmployeeServiceConfigurationController::class, 'showValidAt']);
Route::get('/employee-services', [EmployeeServiceController::class, 'index']);
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);

Route::get('/appointments/confirm/{appointment}', [AppointmentController::class, 'confirm'])->name("appointments.confirm")->middleware("signed:relative");

Route::get('/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/services', [ServiceController::class, 'store']);
    Route::patch('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::post('/employee-versions', [EmployeeVersionController::class, 'store']);
    Route::patch('/employee-versions/{employeeVersion}', [EmployeeVersionController::class, 'update']);
    Route::delete('/employee-versions/{employeeVersion}', [EmployeeVersionController::class, 'destroy']);
    Route::post('/employee-service-configurations', [EmployeeServiceConfigurationController::class, 'store']);
    Route::patch('/employee-service-configurations/{employeeServiceConfiguration}', [EmployeeServiceConfigurationController::class, 'update']);
    Route::delete('/employee-service-configurations/{employeeServiceConfiguration}', [EmployeeServiceConfigurationController::class, 'destroy']);
    Route::post('/employee-services', [EmployeeServiceController::class, 'store']);
    Route::patch('/employee-services/{employeeService}', [EmployeeServiceController::class, 'update']);
    Route::delete('/employee-services/{employeeService}', [EmployeeServiceController::class, 'destroy']);
    Route::post('/service-versions', [ServiceVersionController::class, 'store']);
    Route::patch('/service-versions/{serviceVersion}', [ServiceVersionController::class, 'update']);
    Route::delete('/service-versions/{serviceVersion}', [ServiceVersionController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/appointments', [AppointmentController::class, 'userAppointments']);
    Route::post('/user/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelUserAppointment']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1');
    Route::middleware('verified')->group(function () {
        Route::apiResource("reviews", ReviewController::class)->only(["store", "destroy"]);
    });
});

Route::post('/forgot-password', ForgotPasswordController::class)->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', ResetPasswordController::class)->middleware('guest')->name('password.update');
