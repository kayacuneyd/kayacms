<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Bookmarks') ?></title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:560px;margin:64px auto;padding:0 24px;color:#1f2933;line-height:1.6}
        ul{list-style:none;padding:0;margin:16px 0}
        li{padding:12px 0;border-bottom:1px solid #e5e7eb}
        li small{display:block;color:#6b7280;font-size:.85rem}
        .button{display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;margin-right:8px}
        .button-ghost{background:transparent;color:#2563eb;border:1px solid #2563eb}
        .muted{color:#6b7280;font-size:.9rem}
    </style>
</head>
<body>
    <h1>Bookmarks</h1>
    <?php if (! empty($bookmarks)): ?>
        <ul>
            <?php foreach ($bookmarks as $item): ?>
                <li><a href="<?= site_url('content/' . $item['slug']) ?>"><strong><?= esc($item['title']) ?></strong></a><small><?= esc($item['published_at'] ?? '') ?></small></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">You haven't bookmarked anything yet.</p>
    <?php endif; ?>
    <p><a class="button" href="<?= site_url('/') ?>">Back to content</a> <a class="button button-ghost" href="<?= site_url('member/profile') ?>">Profile</a></p>
</body>
</html>
