<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Calculations\CreateAppointment;
use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Mail\Booking;
use App\Mail\BookingSummary;
use App\Models\Appointment;
use App\Models\Review;
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

    public function userAppointments(Request $request)
    {
        $appointments = Appointment::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'service:id,name',
                'employee.user:id,name',
            ])
            ->orderByDesc('start_datetime')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'status' => $appointment->status,
                    'display_status' => $this->displayStatus($appointment),
                    'start_datetime' => $appointment->start_datetime?->toIso8601String(),
                    'end_datetime' => $appointment->end_datetime?->toIso8601String(),
                    'price' => $appointment->price,
                    'service' => [
                        'id' => $appointment->service?->id,
                        'name' => $appointment->service?->name,
                    ],
                    'employee' => [
                        'id' => $appointment->employee?->id,
                        'name' => $appointment->employee?->user?->name,
                    ],
                ];
            })
            ->values();

        return response()->json($appointments);
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
            "status" => "confirmed",
            "confirmed_at" => now(),
        ])->save();
        $appointment->load(['service', 'employee.user', 'employee.services']);
        $summary = $this->buildSummary($appointment);
        $query = http_build_query($summary, '', '&', PHP_QUERY_RFC3986);
        Mail::to($appointment->customer->email)->send(new BookingSummary($appointment));
        return redirect()->away($frontendBase . "/summary" . ($query ? "?{$query}" : ''));
    }

    public function cancelUserAppointment(Request $request, Appointment $appointment)
    {
        if ($appointment->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'You are not allowed to cancel this appointment'], 403);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'Appointment already cancelled'], 409);
        }

        if (!in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return response()->json(['message' => 'Only pending or confirmed appointments can be cancelled'], 422);
        }

        $appointment->forceFill([
            'status' => 'cancelled',
        ])->save();

        return response()->json([
            'message' => 'Appointment cancelled',
            'appointment' => [
                'id' => $appointment->id,
                'status' => $appointment->status,
            ],
        ]);
    }

    public function barberAppointments(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $appointments = Appointment::query()
            ->where('employee_id', $employee->id)
            ->with([
                'service:id,name',
                'customer:id,name',
            ])
            ->orderBy('start_datetime')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'status' => $appointment->status,
                    'client' => $appointment->customer?->name ?? $appointment->guest_name ?? 'Guest',
                    'service' => $appointment->service?->name,
                    'time' => optional($appointment->start_datetime)->format('H:i'),
                    'start_datetime' => $appointment->start_datetime?->toIso8601String(),
                    'end_datetime' => $appointment->end_datetime?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json($appointments);
    }

    public function cancelBarberAppointment(Request $request, Appointment $appointment)
    {
        $employee = $request->user()->employee;
        if (!$employee || $appointment->employee_id !== $employee->id) {
            return response()->json(['message' => 'You are not allowed to cancel this appointment'], 403);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'Appointment already cancelled'], 409);
        }

        if (!in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return response()->json(['message' => 'Only pending or confirmed appointments can be cancelled'], 422);
        }

        $appointment->forceFill([
            'status' => 'cancelled',
        ])->save();

        return response()->json([
            'message' => 'Appointment cancelled',
            'appointment' => [
                'id' => $appointment->id,
                'status' => $appointment->status,
            ],
        ]);
    }

    public function barberReviews(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $reviews = Review::query()
            ->whereHas('appointment', fn ($q) => $q->where('employee_id', $employee->id))
            ->with(['user:id,name'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'client' => $review->user?->name ?? 'Guest',
                'rating' => $review->rating,
                'text' => $review->comment,
            ])
            ->values();

        return response()->json($reviews);
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

    private function displayStatus(Appointment $appointment): string
    {
        return match ($appointment->status) {
            'cancelled', 'no_show' => 'cancelled',
            'completed' => 'completed',
            default => $appointment->start_datetime?->isPast() ? 'completed' : 'upcoming',
        };
    }
}
