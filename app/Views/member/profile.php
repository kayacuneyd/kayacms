<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Member profile') ?></title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:480px;margin:64px auto;padding:0 24px;color:#1f2933;line-height:1.6}
        .flash{margin:16px 0;padding:12px 16px;border-radius:8px;background:#ecfdf5;color:#065f46}
        .flash-error{background:#fef2f2;color:#991b1b}
        .input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:12px;box-sizing:border-box}
        .button{display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;cursor:pointer;text-decoration:none;margin-right:8px}
        .button-ghost{background:transparent;color:#2563eb;border:1px solid #2563eb}
        .muted{color:#6b7280;font-size:.9rem}
    </style>
</head>
<body>
    <h1>Member profile</h1>
    <?php if (session()->getFlashdata('success')): ?><div class="flash"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <p class="muted">Signed in as: <strong><?= esc($member['email'] ?? '') ?></strong></p>
    <form action="<?= site_url('member/profile') ?>" method="post">
        <?= csrf_field() ?>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-10000px" aria-hidden="true">
        <input class="input" name="username" value="<?= esc($member['name'] ?? '') ?>" placeholder="Display name" required>
        <button class="button" type="submit">Update profile</button>
    </form>
    <form action="<?= site_url('member/avatar') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input class="input" type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
        <button class="button" type="submit">Upload avatar</button>
    </form>
    <p><a class="button" href="<?= site_url('member/bookmarks') ?>">Bookmarks</a> <a class="button button-ghost" href="<?= site_url('member/logout') ?>">Sign out</a></p>
</body>
</html>
