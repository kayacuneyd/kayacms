<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 0;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="background:#1f2937;color:#ffffff;padding:20px 32px;font-size:20px;font-weight:bold;">
                        New Comment
                    </td>
                </tr>
                <tr><td style="padding:32px;">
                    <p style="margin:0 0 16px;color:#4b5563;line-height:1.6;">
                        A new comment by <strong><?= esc($author) ?></strong> on
                        <strong><?= esc($contentTitle) ?></strong> is awaiting approval.
                    </p>
                    <blockquote style="margin:0 0 24px;padding:16px;background:#f9fafb;border-left:4px solid #2563eb;color:#374151;">
                        <?= nl2br(esc($body)) ?>
                    </blockquote>
                    <p style="margin:0;text-align:center;">
                        <a href="<?= base_url('admin/comments') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;">Review Comment</a>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>