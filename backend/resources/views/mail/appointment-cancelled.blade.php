@php
    $cancelledBy = match ($appointment->cancelled_by) {
        'customer' => 'customer',
        'employee' => 'barber',
        'admin' => 'shop admin',
        default => 'shop',
    };
@endphp

<x-mail.shell
    title="Appointment cancelled"
    subtitle="This appointment has been cancelled by {{ $cancelledBy }}."
>
    <x-mail.appointment-details :appointment="$appointment" />

    @if (!empty($cancellationReason))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0; border:1px solid #efdfcc; border-radius:16px; background-color:#ffffff;">
            <tr>
                <td style="padding:16px 18px;">
                    <p style="margin:0 0 8px; color:#111827; font-weight:700; font-size:14px;">
                        Cancellation reason:
                    </p>
                    <p style="margin:0; color:#374151; font-size:14px; line-height:1.6; white-space:pre-line;">
                        {{ $cancellationReason }}
                    </p>
                </td>
            </tr>
        </table>
    @endif
</x-mail.shell>
