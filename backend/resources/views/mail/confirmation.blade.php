<x-mail.shell
    eyebrow="Booking Confirmation"
    title="Confirm your appointment"
    subtitle="Your selected slot is almost locked in. One quick confirmation keeps everything on schedule."
>
    <p style="margin:0 0 18px; font-size:16px; line-height:1.7; color:#2f2925;">
        Hi {{ $appointment->guest_name ?? $appointment->customer?->name ?? 'there' }},
    </p>

    <p style="margin:0 0 24px; font-size:15px; line-height:1.8; color:#5c4d40;">
        Please review the details below and confirm your booking. Your confirmation link stays active for {{ $expiresInMinutes }} minutes.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #eadbc7; border-radius:18px; background-color:#f8f1e7;">
        <tr>
            <td style="padding:20px 22px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:0 0 14px; font-size:13px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#8a5a31;">
                            Appointment details
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; color:#5c4d40;">Service</td>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; font-weight:700; color:#1f1a17;" align="right">{{ $appointment->service?->name ?? 'Service' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; color:#5c4d40;">Barber</td>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; font-weight:700; color:#1f1a17;" align="right">{{ $appointment->employee?->user?->name ?? 'Barber' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; color:#5c4d40;">Date</td>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; font-weight:700; color:#1f1a17;" align="right">{{ $appointment->start_datetime?->format('l, Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; color:#5c4d40;">Time</td>
                        <td style="padding:10px 0; border-top:1px solid #eadbc7; font-size:14px; font-weight:700; color:#1f1a17;" align="right">{{ $appointment->start_datetime?->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0 0; border-top:1px solid #eadbc7; font-size:14px; color:#5c4d40;">Total</td>
                        <td style="padding:10px 0 0; border-top:1px solid #eadbc7; font-size:18px; font-weight:700; color:#1f1a17;" align="right">${{ $appointment->price }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td align="center" style="border-radius:999px; background-color:#1f1a17;">
                <a
                    href="{{ $confirmationLink }}"
                    style="display:inline-block; padding:15px 28px; font-size:14px; font-weight:700; letter-spacing:0.04em; text-decoration:none; color:#fffaf2;"
                >
                    Confirm Booking
                </a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #eadbc7; border-radius:18px; background-color:#fff7ec;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 8px; font-size:13px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#8a5a31;">
                    Need the full link?
                </p>
                <p style="margin:0; font-size:14px; line-height:1.7; color:#5c4d40; word-break:break-word;">
                    <a href="{{ $confirmationLink }}" style="color:#8a5a31; text-decoration:underline;">{{ $confirmationLink }}</a>
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:14px; line-height:1.8; color:#5c4d40;">
        If you did not request this appointment, you can ignore this email and the slot will stay unconfirmed.
    </p>
</x-mail.shell>
