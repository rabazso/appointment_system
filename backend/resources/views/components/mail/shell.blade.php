@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4ede3; color:#1f1a17; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(180deg, #efe2cf 0%, #f9f5ef 100%); padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;">
                    <tr>
                        <td style="padding-bottom:18px; text-align:center; font-size:12px; letter-spacing:0.28em; text-transform:uppercase; color:#6e5c4d;">
                            Barber Shop
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#fffdfa; border:1px solid #e7d8c5; border-radius:24px; overflow:hidden; box-shadow:0 18px 45px rgba(32, 22, 15, 0.12);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height:8px; background:linear-gradient(90deg, #1f1a17 0%, #8a5a31 50%, #d6a55a 100%);"></td>
                                </tr>
                                <tr>
                                    <td style="padding:40px 40px 18px;">
                                        @if ($eyebrow)
                                            <p style="margin:0 0 12px; font-size:12px; font-weight:700; letter-spacing:0.26em; text-transform:uppercase; color:#8a5a31;">
                                                {{ $eyebrow }}
                                            </p>
                                        @endif
                                        <h1 style="margin:0; font-size:32px; line-height:1.15; color:#1f1a17;">
                                            {{ $title }}
                                        </h1>
                                        @if ($subtitle)
                                            <p style="margin:14px 0 0; font-size:16px; line-height:1.7; color:#5c4d40;">
                                                {{ $subtitle }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 40px 40px;">
                                        {{ $slot }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 12px 0; text-align:center; font-size:12px; line-height:1.7; color:#6e5c4d;">
                            Crafted for clean cuts, smooth bookings, and zero inbox confusion.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
