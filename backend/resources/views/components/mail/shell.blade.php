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
<body style="margin:0; padding:0; background-color:#f5f5f5; color:#1f1a17; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;">
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #efdfcc; border-radius:24px; overflow:hidden; box-shadow:0 10px 24px rgba(15, 23, 42, 0.06);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:38px 40px 18px; text-align:center;">
                                        @if ($eyebrow)
                                            <p style="margin:0 0 12px; font-size:12px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#8a5a31;">
                                                {{ $eyebrow }}
                                            </p>
                                        @endif
                                        <h1 style="margin:0; font-size:28px; line-height:1.2; color:#000000;">
                                            {{ $title }}
                                        </h1>
                                        @if ($subtitle)
                                            <p style="margin:14px auto 0; max-width:460px; font-size:16px; line-height:1.7; color:#667085;">
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
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
