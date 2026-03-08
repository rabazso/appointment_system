<p>Dear {{ $appointment->guest_name ?? $appointment->customer?->name ?? 'Guest' }},</p>

<p>link:</p>

<a href="{{ $confirmationLink }}">Confirm</a>

<p>asd</p>
