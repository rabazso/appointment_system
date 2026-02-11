<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f9fafb; padding:40px;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:520px; margin:0 auto; border-collapse:collapse;">
<tr>
<td align="center" style="padding:0;">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" 
       style="background:#ffffff; border-radius:12px; box-shadow:0 3px 10px rgba(0,0,0,0.05); padding:32px;">

  <tr>
    <td align="center" style="padding-bottom:24px;">
      <div style="background:#d9f7dd; width:48px; height:48px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#28a745" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:block;">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>
      <h2 style="margin:0; font-weight:700; font-size:22px; color:#000000; line-height:1.2;">
        {{ $appointment->guest_name ?? $appointment->customer?->name }}, your appointment was successful
      </h2>
      <p style="color:#6b7280; margin-top:8px; font-size:14px; line-height:1.4;">
        Your appointment details
      </p>
    </td>
  </tr>

  <tr>
    <td style="background:#ffedd8; border-radius:8px; padding:24px;">

      <table width="100%" cellpadding="8" cellspacing="0" role="presentation" style="border-collapse:collapse;">

        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Service:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ $appointment->service->name }}</td>
        </tr>

        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Barber:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ $appointment->employee->user->name }}</td>
        </tr>

        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Date:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ $appointment->start_datetime->format('Y-m-d') }}</td>
        </tr>

        <tr style="border-bottom:1px solid rgba(0,0,0,0.1);">
          <td style="color:#6b7280; font-size:14px;">Time:</td>
          <td align="right" style="font-weight:700; font-size:14px; color:#000000;">{{ $appointment->start_datetime->format('H:i') }}</td>
        </tr>

        <tr>
          <td style="color:#6b7280; font-weight:700; font-size:14px;">Total:</td>
          <td align="right" style="font-weight:700; font-size:18px; color:#000000;">
            ${{ $appointment->price }}
          </td>
        </tr>

      </table>

    </td>
  </tr>
  <tr>
    <td align="center" style="margin: top 12px; font-weight:700; font-size:22px; color:#000000; line-height:1.2;"><h3>Thank you for your booking!</h3></td>
  </tr>
</table>

</td>
</tr>
</table>

</body>
</html>
