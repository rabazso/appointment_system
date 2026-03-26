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
        Thank you for visiting us, {{ $appointment->customer?->name }}!
      </h2>
      <p style="color:#6b7280; margin-top:8px; font-size:14px; line-height:1.4;">
        We hope you loved your haircut. Your feedback helps us improve.
      </p>
    </td>
  </tr>

  <tr>
    <td style="background:#ffedd8; border-radius:8px; padding:20px;">
      <p style="margin:0 0 6px; color:#6b7280; font-size:14px;">Service</p>
      <p style="margin:0 0 14px; font-weight:700; font-size:15px; color:#000000;">{{ $appointment->service?->name }}</p>

      <p style="margin:0 0 6px; color:#6b7280; font-size:14px;">Barber</p>
      <p style="margin:0; font-weight:700; font-size:15px; color:#000000;">{{ $appointment->employee?->name }}</p>
    </td>
  </tr>

  <tr>
    <td align="center" style="padding-top:24px;">
      <a href="{{ $reviewLink }}" style="display:inline-block; background:#f97316; color:#ffffff; text-decoration:none; font-weight:700; font-size:14px; padding:12px 18px; border-radius:8px;">
        Leave a review
      </a>
      <p style="margin-top:14px; color:#6b7280; font-size:12px; line-height:1.5;">
        This link opens the review section on our website.
      </p>
    </td>
  </tr>
</table>

</td>
</tr>
</table>

</body>
</html>
