<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Calculations\CreateAppointment;

use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Mail\BookingConfirmation;
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
        Mail::to($appointment->customer->email)->send(new BookingConfirmation($appointment));
        return response()->json(["message"=> "Successfully booked", "appointment" => $appointment,], 201);
    }
}