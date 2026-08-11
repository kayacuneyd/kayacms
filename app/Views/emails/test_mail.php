<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 0;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="background:#1f2937;color:#ffffff;padding:20px 32px;font-size:20px;font-weight:bold;">
                        <?= esc($siteName ?? 'KayaCMS') ?>
                    </td>
                </tr>
                <tr><td style="padding:32px;">
                    <h1 style="margin:0 0 16px;font-size:22px;color:#111827;">Test Email</h1>
                    <p style="margin:0;color:#4b5563;line-height:1.6;">
                        This is a test email. If you are reading this, your SMTP configuration is working correctly.
                    </p>
                    <p style="margin:24px 0 0;color:#9ca3af;font-size:12px;border-top:1px solid #e5e7eb;padding-top:16px;">
                        Sent at <?= date('Y-m-d H:i:s') ?>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>