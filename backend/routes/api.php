<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
    Route::get('/barber/profile', [EmployeeController::class, 'barberProfile']);
    Route::post('/barber/profile', [EmployeeController::class, 'updateBarberProfile']);
    Route::post('/barber/profile/gallery', [EmployeeController::class, 'uploadBarberGalleryImage']);
    Route::delete('/barber/profile/gallery/{gallery}', [EmployeeController::class, 'deleteBarberGalleryImage']);
    Route::get('/user/appointments', [AppointmentController::class, 'userAppointments']);
    Route::post('/user/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelUserAppointment']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1');
    Route::middleware('verified')->group(function () {
        Route::apiResource("reviews", ReviewController::class)->only(["store", "destroy"]);
    });
});

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    if ($status !== Password::RESET_LINK_SENT) {
        return response()->json([
            'message' => __($status),
            'errors' => [
                'email' => [__($status)],
            ],
        ], 422);
    }

    return response()->json([
        'message' => __($status),
    ]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return response()->json([
        'token' => $token,
        'email' => $request->query('email'),
    ]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => [
            'required',
            'string',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[@$!%*?&.]/',
            'confirmed',
        ],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    if ($status !== Password::PASSWORD_RESET) {
        return response()->json([
            'message' => __($status),
            'errors' => [
                'email' => [__($status)],
            ],
        ], 422);
    }

    return response()->json([
        'message' => __($status),
    ]);
})->middleware('guest')->name('password.update');
