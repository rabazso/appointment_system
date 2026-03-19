<x-mail.shell
    title="Confirm your appointment"
    subtitle="We sent a confirmation link. Please confirm your booking."
>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #efdfcc; border-radius:18px; background-color:#fbefe1;">
        <tr>
            <td style="padding:20px 22px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:0 0 12px; font-size:15px; color:#667085;">Service:</td>
                        <td style="padding:0 0 12px; font-size:15px; font-weight:700; color:#000000;" align="right">{{ $appointment->service?->name ?? 'Service' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Barber:</td>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ $appointment->employee?->user?->name ?? 'Barber' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Date:</td>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ $appointment->start_datetime?->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Time:</td>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ $appointment->start_datetime?->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#667085;">Total:</td>
                        <td style="padding:12px 0 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:18px; font-weight:700; color:#000000;" align="right">${{ $appointment->price }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:12px; background-color:#f69436;">
                <a
                    href="{{ $confirmationLink }}"
                    style="display:inline-block; padding:15px 28px; font-size:14px; font-weight:700; text-decoration:none; color:#000000;"
                >
                    Confirm Booking
                </a>
            </td>
        </tr>
    </table>

</x-mail.shell>
