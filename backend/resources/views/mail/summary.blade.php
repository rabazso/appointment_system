<x-mail.shell
    title="Booking summary"
    subtitle="Your appointment details."
>
    <x-mail.appointment-details :appointment="$appointment" />

    <p style="margin:0; text-align:center; font-size:15px; line-height:1.7; color: black; font-weight: bold; font-size: 150%">
        Thank you for your booking.
    </p>
</x-mail.shell>
