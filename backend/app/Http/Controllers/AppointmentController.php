<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Calculations\CreateAppointment;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Mail\Booking;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function index(AppointmentRequest $request, AppointmentCalculation $calculation)
    {
        $appointments = $calculation->Appointments($request);
        
        return response()->json(
            $appointments,
        );
    }

    public function store(AppointmentStoreRequest $request, CreateAppointment $create)
    {
        $appointment = $create->Create($request);
        $user = auth()->user();
        $token = JWTAuth::fromUser(auth()->user(), ['appointment_id' => $appointment->id]);
        $confirmationLink = url("/appointments/confirm/{$token}");
        Mail::to($appointment->customer->email)->send(new Booking($appointment, $confirmationLink));
        return response()->json(["message"=> "Successfully booked", "appointment" => $appointment,], 201);
    }
}