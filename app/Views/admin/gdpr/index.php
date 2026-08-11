<?php $title = $title ?? 'GDPR Export'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">GDPR Export</h3>
    </div>

    <p class="ck-text-sm ck-text-gray-500 ck-mb-6">
        Search a user to export every piece of personal data the CMS holds about them
        (profile, content, comments, media, notifications, logs, tokens, resets, attempts).
        Password hashes, TOTP secrets and raw API/magic-link tokens are never exported.
    </p>

    <form method="get" action="/admin/gdpr" class="ck-flex ck-gap-3 ck-items-center ck-mb-6">
        <input type="search" name="q" value="<?= esc($q ?? '') ?>" placeholder="Search by username or email..."
               class="ck-px-4 ck-py-2 ck-border ck-rounded ck-w-96">
        <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Search</button>
        <?php if (!empty($q)): ?>
            <a href="/admin/gdpr" class="ck-px-4 ck-py-2 ck-border ck-rounded ck-text-gray-600">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($q) && empty($users)): ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No users match "<?= esc($q) ?>".</p>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Username</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Email</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $item): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4"><?= esc($item->username ?? '') ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item->email ?? '') ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item->status ?? '') ?></td>
                        <td class="ck-px-6 ck-py-4 ck-flex ck-gap-3">
                            <a href="/admin/gdpr/export/<?= $item->id ?>?format=json" class="ck-px-3 ck-py-1 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm">Export JSON</a>
                            <a href="/admin/gdpr/export/<?= $item->id ?>?format=csv" class="ck-px-3 ck-py-1 ck-bg-emerald-600 ck-text-white ck-rounded ck-text-sm">Export CSV</a>
                            <?php if ((int) ($item->id ?? 0) !== (int) session()->get('user_id')): ?>
                                <form action="/admin/gdpr/delete-data/<?= $item->id ?>" method="post" class="ck-inline"
                                      onsubmit="return confirm('This permanently removes the account. Continue?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-px-3 ck-py-1 ck-bg-red-600 ck-text-white ck-rounded ck-text-sm">Erase Account</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>