<x-mail.shell
    title="Your employee account is ready"
    subtitle="Use the temporary password below to sign in to the employee dashboard."
>
    <p style="margin:0 0 18px; font-size:16px; line-height:1.7; color:#1f1a17; text-align:center;">
        Hi {{ $employeeName }},
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #efdfcc; border-radius:18px; background-color:#fbefe1;">
        <tr>
            <td style="padding:20px 22px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:0 0 12px; font-size:15px; color:#667085;">Email:</td>
                        <td style="padding:0 0 12px; font-size:15px; font-weight:500; color:#000000;" align="right">{{ $email }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:15px; color:#667085;">Temporary password:</td>
                        <td style="padding:12px 0 0; border-top:1px solid rgba(0, 0, 0, 0.08); font-size:18px; font-weight:500; color:#000000;" align="right">{{ $temporaryPassword }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:12px; background-color:#f69436;">
                <a
                    href="{{ $loginUrl }}"
                    style="display:inline-block; padding:15px 28px; font-size:14px; font-weight:500; text-decoration:none; color:#000000;"
                >
                    Open employee login
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:14px; line-height:1.8; color:#667085; text-align:center;">
        You can change your password later from the reset password flow if needed.
    </p>
</x-mail.shell>

