<?php

namespace App\Services\Booking;

use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;

class AppointmentCancellationNotificationService
{
    public function send(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'customer:id,name,email',
            'appointmentServices.service:id,name',
            'employee:id,name,user_id',
            'employee.user:id,email',
        ]);

        $recipients = collect([
            $appointment->customer?->email,
            $appointment->employee?->user?->email,
        ])->filter()->unique()->values();

        foreach ($recipients as $recipientEmail) {
            Mail::to($recipientEmail)->send(new AppointmentCancelled($appointment));
        }
    }
}
