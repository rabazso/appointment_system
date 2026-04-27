@props([
    'appointment',
    'showDuration' => true,
    'showTotal' => true,
])

@php
    $serviceNames = $appointment->appointmentServices
        ?->map(fn ($appointmentService) => $appointmentService->service?->name)
        ->filter()
        ->implode(', ');
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #efdfcc; border-radius:18px; background-color:#fbefe1;">
    <tr>
        <td style="padding:20px 22px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:0 0 12px; font-size:15px; color:#667085;">Service:</td>
                    <td style="padding:0 0 12px; font-size:15px; font-weight:700; color:#000000;" align="right">{{ $serviceNames !== '' ? $serviceNames : 'Service' }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Barber:</td>
                    <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ $appointment->employee?->name ?? 'Barber' }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Date:</td>
                    <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ optional($appointment->start_datetime)->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Time:</td>
                    <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ optional($appointment->start_datetime)->format('H:i') }}</td>
                </tr>
                @if ($showDuration)
                    <tr>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Total duration:</td>
                        <td style="padding:12px 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#000000;" align="right">{{ $appointment->total_duration ? $appointment->total_duration . ' min' : '-' }}</td>
                    </tr>
                @endif
                @if ($showTotal)
                    <tr>
                        <td style="padding:12px 0 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; font-weight:700; color:#667085;">Total:</td>
                        <td style="padding:12px 0 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:18px; font-weight:700; color:#000000;" align="right">${{ $appointment->total_price }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
