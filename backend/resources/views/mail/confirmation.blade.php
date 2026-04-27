<x-mail.shell
    title="Confirm your appointment"
    subtitle="We sent a confirmation link. Please confirm your booking."
>
    <x-mail.appointment-details :appointment="$appointment" />

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
