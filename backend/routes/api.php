<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReviewController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/guest', [AuthController::class, 'guest']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::get('/reviews', [ReviewController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);


Route::middleware('auth:sanctum')->group(function () {
Route::post('/reviews', [ReviewController::class, 'store']);
Route::post('/logout', [AuthController::class, 'logout']);
});