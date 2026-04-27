<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeTemporaryPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $employeeName, public string $email, public string $temporaryPassword, public string $loginUrl,) 
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Barber Shop employee account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.employee-temporary-password',
            with: [
                'employeeName' => $this->employeeName,
                'email' => $this->email,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $this->loginUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

