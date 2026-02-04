<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Calculations\CreateAppointment;
use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Mail\Booking;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

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
        $confirmationLink = URL::temporarySignedRoute("appointments.confirm", now()->addMinutes(60), ["appointment" => $appointment->id]);
        Mail::to($appointment->customer->email)->send(new Booking($appointment, $confirmationLink));
        return response()->json(["message"=> "Booking created, confirmation email sent", "appointment" => $appointment,], 201);
    }

    public function confirm(Appointment $appointment){
        if($appointment->confirmed_at){
            return response()->json(["message" => "Booking already confirmed"], 400);
        }
        $appointment->update([
            "confirmed_at" => now()
        ]);
        return response()->json(["message" => "Booking confirmed", "appointment" => $appointment]);
    }
}