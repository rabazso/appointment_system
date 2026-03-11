<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Barber Shop: Appointment Cancelled',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.appointment-cancelled',
            with: [
                'appointment' => $this->appointment,
                'cancellationReason' => (string) $this->appointment->cancellation_reason,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
