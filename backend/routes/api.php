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

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::middleware('auth:api')->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'store']);
});

Route::get('/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
Route::post('/logout', [AuthController::class, 'logout']);
Route::apiResource("reviews", ReviewController::class)->only(["store", "destroy"]);
});