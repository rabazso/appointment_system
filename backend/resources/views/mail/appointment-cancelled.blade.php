<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f9fafb; padding:40px;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:520px; margin:0 auto; border-collapse:collapse;">
<tr>
<td align="center" style="padding:0;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.05); padding:32px;">
  <tr>
    <td align="center" style="padding-bottom:24px;">
      <h2 style="margin:0; font-weight:700; font-size:22px; color:#000000; line-height:1.2;">
        Hi {{ $appointment->customer?->name ?? 'Guest' }},
      </h2>
      <p style="color:#6b7280; margin-top:8px; font-size:14px; line-height:1.4;">
        Your appointment has been cancelled by your barber.
      </p>
    </td>
  </tr>

  <tr>
    <td style="background:#ffedd8; border-radius:8px; padding:24px;">
      <table width="100%" cellpadding="8" cellspacing="0" role="presentation" style="border-collapse:collapse;">
        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Service:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ $appointment->appointmentServices->first()?->service?->name ?? 'Service' }}</td>
        </tr>
        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Barber:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ $appointment->employee?->name }}</td>
        </tr>
        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Date:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ optional($appointment->start_datetime)->format('Y-m-d') }}</td>
        </tr>
        <tr>
          <td style="color:#6b7280; font-size:14px;">Time:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ optional($appointment->start_datetime)->format('H:i') }}</td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style="padding-top:20px;">
      <p style="margin:0 0 8px; color:#111827; font-weight:700; font-size:14px;">
        Reason from the barber:
      </p>
      <p style="margin:0; color:#374151; font-size:14px; line-height:1.5; white-space:pre-line;">
        {{ $cancellationReason }}
      </p>
    </td>
  </tr>
</table>

</td>
</tr>
</table>

</body>
</html>
