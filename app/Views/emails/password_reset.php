<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:#1f2937;color:#ffffff;padding:20px 32px;font-size:20px;font-weight:bold;">
                            <?= esc($siteName ?? 'KayaCMS') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px;font-size:24px;color:#111827;">Password Reset Request</h1>
                            <p style="margin:0 0 16px;color:#4b5563;font-size:15px;line-height:1.6;">
                                You requested a password reset. Click the button below to choose a new password.
                                This link is valid for <strong>30 minutes</strong>.
                            </p>
                            <p style="margin:0 0 24px;text-align:center;">
                                <a href="<?= base_url('admin/reset-password/' . $token) ?>"
                                   style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;">Reset Password</a>
                            </p>
                            <p style="margin:0 0 16px;color:#9ca3af;font-size:13px;line-height:1.6;">
                                If you did not request this, you can safely ignore this email. Your password will not change.
                            </p>
                            <p style="margin:24px 0 0;color:#9ca3af;font-size:12px;border-top:1px solid #e5e7eb;padding-top:16px;">
                                &copy; <?= date('Y') ?> <?= esc($siteName ?? 'KayaCMS') ?> &middot; Admin Panel Notification
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>