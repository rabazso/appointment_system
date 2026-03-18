<x-mail.shell
    eyebrow="Account Security"
    title="Verify your email address"
    subtitle="Confirm your email to finish setting up your Barber Shop account and keep your bookings easy to manage."
>
    <p style="margin:0 0 18px; font-size:16px; line-height:1.7; color:#2f2925;">
        Hi {{ $user->name }},
    </p>

    <p style="margin:0 0 24px; font-size:15px; line-height:1.8; color:#5c4d40;">
        Tap the button below to verify your email address. This link stays active for {{ $expiresInMinutes }} minutes.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td align="center" style="border-radius:999px; background-color:#1f1a17;">
                <a
                    href="{{ $verificationUrl }}"
                    style="display:inline-block; padding:15px 28px; font-size:14px; font-weight:700; letter-spacing:0.04em; text-decoration:none; color:#fffaf2;"
                >
                    Verify Email
                </a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #eadbc7; border-radius:18px; background-color:#f8f1e7;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 8px; font-size:13px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#8a5a31;">
                    Need the full link?
                </p>
                <p style="margin:0; font-size:14px; line-height:1.7; color:#5c4d40; word-break:break-word;">
                    <a href="{{ $verificationUrl }}" style="color:#8a5a31; text-decoration:underline;">{{ $verificationUrl }}</a>
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:14px; line-height:1.8; color:#5c4d40;">
        If you did not create this account, you can safely ignore this message.
    </p>
</x-mail.shell>
