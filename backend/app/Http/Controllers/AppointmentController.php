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
        $backendConfirmUrl = URL::temporarySignedRoute(
            "appointments.confirm",
            now()->addMinutes(60),
            ["appointment" => $appointment->id],
            false
        );
        $backendBase = rtrim((string) config('app.url'), '/');
        if ($backendBase !== '' && !preg_match('#^https?://#', $backendBase)) {
            $backendBase = 'http://' . $backendBase;
        }
        $confirmationLink = $backendBase . $backendConfirmUrl;
        Mail::to($appointment->customer->email)->send(new Booking($appointment, $confirmationLink));
        return response()->json(["message"=> "Booking created, confirmation email sent", "appointment" => $appointment,], 201);
    }

    public function confirm(Request $request, Appointment $appointment){
        $frontendBase = rtrim((string) config('app.frontend_url'), '/');
        if ($frontendBase !== '' && !preg_match('#^https?://#', $frontendBase)) {
            $frontendBase = 'http://' . $frontendBase;
        }
        if ($frontendBase === '') {
            return response()->json(["message" => "Frontend URL is not configured"], 500);
        }

        if ($appointment->confirmed_at) {
            return response()->json(["message" => "Booking already confirmed"], 410);
        }
        $appointment->forceFill([
            "confirmed_at" => now(),
        ])->save();
        $appointment->load(['service', 'employee.user', 'employee.services']);
        $summary = $this->buildSummary($appointment);
        $query = http_build_query($summary, '', '&', PHP_QUERY_RFC3986);
        return redirect()->away($frontendBase . "/summary" . ($query ? "?{$query}" : ''));
    }

    private function buildSummary(Appointment $appointment): array
    {
        $employee = $appointment->employee;
        $service = $appointment->service;
        $price = null;

        if ($employee && $employee->relationLoaded('services')) {
            $serviceMatch = $employee->services->firstWhere('id', $appointment->service_id);
            if ($serviceMatch && $serviceMatch->pivot) {
                $price = $serviceMatch->pivot->price;
            }
        }

        return [
            'serviceName' => $service?->name,
            'barberName' => $employee?->user?->name,
            'date' => optional($appointment->start_datetime)->format('Y-m-d'),
            'time' => optional($appointment->start_datetime)->format('H:i'),
            'price' => $price,
        ];
    }
}
