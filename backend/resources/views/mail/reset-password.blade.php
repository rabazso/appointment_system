<x-mail.shell
    title="Reset your password"
    subtitle="We received a password reset request for your customer account."
>
    <p style="margin:0 0 18px; font-size:16px; line-height:1.7; color:#1f1a17; text-align:center;">
        Hi {{ $user->customer?->name ?? $user->email }},
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:12px; background-color:#f69436;">
                <a
                    href="{{ $resetUrl }}"
                    style="display:inline-block; padding:15px 28px; font-size:14px; font-weight:700; text-decoration:none; color:#000000;"
                >
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px; font-size:14px; line-height:1.8; color:#667085; text-align:center;">
        This link will expire in {{ $expiresInMinutes }} minutes.
    </p>

    <p style="margin:0; font-size:14px; line-height:1.8; color:#667085; text-align:center;">
        If you did not request a password reset, you can safely ignore this message.
    </p>
</x-mail.shell>
