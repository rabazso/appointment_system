<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Calculations\CreateAppointment;
use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\AppointmentStatusResource;
use App\Http\Resources\BarberAppointmentResource;
use App\Http\Resources\UserAppointmentResource;
use App\Mail\AppointmentCancelled;
use App\Mail\Booking;
use App\Mail\BookingSummary;
use App\Mail\ReviewRequest;
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

        $confirmationLink = $this->buildConfirmationLink($appointment);
        
        $recipientEmail = $appointment->customer?->email ?? $appointment->guest_email;
        if ($recipientEmail) {
            Mail::to($recipientEmail)->send(new Booking($appointment, $confirmationLink));
        }
        
        $appointment->loadMissing(['service:id,name', 'employee.user:id,name', 'customer:id,name,email']);

        return response()->json([
            'message' => 'Booking created, confirmation email sent',
            'appointment' => (new AppointmentResource($appointment))->toArray($request),
        ], 201);
    }

    private function buildConfirmationLink(Appointment $appointment): string
    {
        $relativeSignedUrl = URL::temporarySignedRoute(
            'appointments.confirm',
            now()->addMinutes(60),
            ['appointment' => $appointment->id],
            false
        );

        return url($relativeSignedUrl);
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
            ->get();

        $payload = $appointments
            ->map(fn (Appointment $appointment) => (new UserAppointmentResource($appointment))->toArray($request))
            ->values();

        return response()->json($payload);
    }

    public function confirm(Appointment $appointment){
        $frontendBase = 'http://' . rtrim((string) config('app.frontend_url'), '/');
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
        $recipientEmail = $appointment->customer?->email ?? $appointment->guest_email;
        if ($recipientEmail) {
            Mail::to($recipientEmail)->send(new BookingSummary($appointment));
        }
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
            'appointment' => (new AppointmentStatusResource($appointment))->toArray($request),
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
            ->get();

        $payload = $appointments
            ->map(fn (Appointment $appointment) => (new BarberAppointmentResource($appointment))->toArray($request))
            ->values();

        return response()->json($payload);
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

        $reason = trim((string) $request->input('cancellation_reason', ''));
        $isPastAppointment = $appointment->start_datetime?->isPast() ?? false;
        $reasonRules = $isPastAppointment
            ? ['nullable', 'string', 'max:500']
            : ['required', 'string', 'min:30', 'max:500'];

        validator(
            ['cancellation_reason' => $reason],
            ['cancellation_reason' => $reasonRules]
        )->validate();

        $appointment->forceFill([
            'status' => 'cancelled',
            'cancellation_reason' => $reason !== '' ? $reason : null,
        ])->save();
        $this->sendBarberCancellationEmail($appointment);

        return response()->json([
            'message' => 'Appointment cancelled',
            'appointment' => (new AppointmentStatusResource($appointment))->toArray($request),
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

    public function completeBarberAppointment(Request $request, Appointment $appointment)
    {
        $employee = $request->user()->employee;
        if (!$employee || $appointment->employee_id !== $employee->id) {
            return response()->json(['message' => 'You are not allowed to complete this appointment'], 403);
        }

        if ($appointment->status === 'completed') {
            return response()->json(['message' => 'Appointment already completed'], 409);
        }

        if ($appointment->status !== 'confirmed') {
            return response()->json(['message' => 'Only confirmed appointments can be marked as completed'], 422);
        }

        $appointment->forceFill([
            'status' => 'completed',
        ])->save();

        $this->sendReviewRequestEmail($appointment);

        return response()->json([
            'message' => 'Appointment completed',
            'appointment' => (new AppointmentStatusResource($appointment))->toArray($request),
        ]);
    }

    private function sendReviewRequestEmail(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'customer:id,name,email',
            'service:id,name',
            'employee.user:id,name',
        ]);

        if (!$appointment->customer?->email) {
            return;
        }

        $frontendBase = rtrim((string) config('app.frontend_url'), '/');
        if ($frontendBase !== '' && !preg_match('#^https?://#', $frontendBase)) {
            $frontendBase = 'http://' . $frontendBase;
        }

        if ($frontendBase === '') {
            return;
        }

        $query = http_build_query([
            'openReview' => 1,
            'appointment_id' => $appointment->id,
        ], '', '&', PHP_QUERY_RFC3986);

        $reviewLink = $frontendBase . '/?' . $query . '#reviews';

        Mail::to($appointment->customer->email)->send(
            new ReviewRequest($appointment, $reviewLink)
        );
    }

    private function sendBarberCancellationEmail(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'customer:id,name,email',
            'service:id,name',
            'employee.user:id,name',
        ]);

        $recipientEmail = $appointment->customer?->email ?? $appointment->guest_email;
        if (!$recipientEmail) {
            return;
        }

        Mail::to($recipientEmail)->send(new AppointmentCancelled($appointment));
    }

}
