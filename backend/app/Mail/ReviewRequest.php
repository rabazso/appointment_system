<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRequest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment, public string $reviewLink)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Barber Shop: Thank you! Leave us a review',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.review-request',
            with: [
                'appointment' => $this->appointment,
                'reviewLink' => $this->reviewLink,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
