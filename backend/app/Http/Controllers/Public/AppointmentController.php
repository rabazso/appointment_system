<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests\Public\AppointmentStoreRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Appointment\AppointmentStatusResource;
use App\Http\Resources\Appointment\UserAppointmentResource;
use App\Mail\Booking;
use App\Mail\BookingSummary;
use App\Models\Appointment;
use App\Services\Booking\AppointmentCancellationNotificationService;
use App\Services\Booking\AppointmentAvailabilityService;
use App\Services\Booking\AppointmentCreationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AppointmentController extends Controller
{
    public function index(Request $request, AppointmentAvailabilityService $availability)
    {
        $this->addServiceIdsToRequest($request);

        $request->validate([
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'selected_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        return response()->json($availability->bookableSlotsForDate($request));
    }

    public function store(AppointmentStoreRequest $request, AppointmentCreationService $appointments)
    {
        $appointment = $appointments->create($request);
        $appointment->loadMissing([
            'appointmentServices.service:id,name',
            'employee:id,name',
            'customer:id,name,email',
        ]);

        $confirmationLink = $this->buildConfirmationLink($appointment);
        
        $recipientEmail = $appointment->customer?->email;
        if ($recipientEmail) {
            Mail::to($recipientEmail)->send(new Booking($appointment, $confirmationLink));
        }

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
        $customer = $request->user()->customer;
        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $appointments = Appointment::query()
            ->where('customer_id', $customer->id)
            ->with([
                'appointmentServices.service:id,name',
                'employee:id,name',
                'review:id,appointment_id,rating,comment,is_visible,created_at',
            ])
            ->orderByDesc('start_datetime')
            ->get();

        $payload = $appointments
            ->map(fn (Appointment $appointment) => (new UserAppointmentResource($appointment))->toArray($request))
            ->values();

        return response()->json($payload);
    }

    public function confirm(Appointment $appointment)
    {
        $frontendBase = $this->frontendBaseUrl();
        if ($frontendBase === '') {
            return response()->json(["message" => "Frontend URL is not configured"], 500);
        }

        if ($appointment->status === 'confirmed') {
            return response()->json(["message" => "Booking already confirmed"], 410);
        }

        if ($appointment->status !== 'pending') {
            return response()->json(["message" => "Only pending appointments can be confirmed"], 409);
        }

        $appointment->update([
            "status" => "confirmed",
        ]);

        $appointment->load([
            'appointmentServices.service:id,name',
            'employee:id,name',
            'customer:id,name,email',
        ]);

        $summary = $this->buildSummary($appointment);
        $query = http_build_query($summary, '', '&', PHP_QUERY_RFC3986);
        $recipientEmail = $appointment->customer?->email;

        if ($recipientEmail) {
            Mail::to($recipientEmail)->send(new BookingSummary($appointment));
        }

        return redirect()->away($frontendBase . "/summary" . ($query ? "?{$query}" : ''));
    }

    public function cancelUserAppointment(Request $request, Appointment $appointment, AppointmentCancellationNotificationService $cancellationNotificationService)
    {
        $customer = $request->user()->customer;
        if (!$customer || $appointment->customer_id !== $customer->id) {
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
            'cancelled_by' => 'customer',
        ])->save();
        $cancellationNotificationService->send($appointment);

        return response()->json([
            'message' => 'Appointment cancelled',
            'appointment' => (new AppointmentStatusResource($appointment))->toArray($request),
        ]);
    }

    private function buildSummary(Appointment $appointment): array
    {
        $employee = $appointment->employee;
        $appointmentService = $appointment->appointmentServices->first();
        $service = $appointmentService?->service;

        return [
            'serviceName' => $service?->name,
            'barberName' => $employee?->name,
            'date' => optional($appointment->start_datetime)->format('Y-m-d'),
            'time' => optional($appointment->start_datetime)->format('H:i'),
            'duration' => $appointment->total_duration,
            'price' => $appointment->total_price,
            'note' => $appointment->customer_note,
        ];
    }

    private function addServiceIdsToRequest(Request $request): void
    {
        $serviceIds = $request->input('service_ids');

        if (!$serviceIds && $request->filled('service_id')) {
            $serviceIds = [$request->input('service_id')];
        }

        if ($serviceIds && !is_array($serviceIds)) {
            $serviceIds = [$serviceIds];
        }

        $request->merge([
            'service_ids' => $serviceIds ?: [],
        ]);
    }

    private function frontendBaseUrl(): string
    {
        $frontendBase = rtrim((string) config('app.frontend_url'), '/');

        if ($frontendBase !== '' && !preg_match('#^https?://#', $frontendBase)) {
            $frontendBase = 'http://' . $frontendBase;
        }

        return $frontendBase;
    }
}
