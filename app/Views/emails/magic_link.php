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
                            <h1 style="margin:0 0 16px;font-size:24px;color:#111827;">Your sign-in link</h1>
                            <p style="margin:0 0 16px;color:#4b5563;font-size:15px;line-height:1.6;">
                                Someone requested a passwordless sign-in link for your account.
                                Click the button below to sign in. If you did not request this, you can ignore this email.
                            </p>
                            <p style="margin:0 0 24px;text-align:center;">
                                <a href="<?= esc($link) ?>"
                                   style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;">Sign In</a>
                            </p>
                            <p style="margin:0;color:#9ca3af;font-size:13px;line-height:1.5;">
                                This link is valid for a single use and expires in a short time. If the button does not work,
                                copy and paste this URL into your browser:<br>
                                <span style="color:#6b7280;"><?= esc($link) ?></span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f9fafb;padding:16px 32px;color:#9ca3af;font-size:12px;text-align:center;">
                            © <?= date('Y') ?> <?= esc($siteName ?? 'KayaCMS') ?> — Admin Panel
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>