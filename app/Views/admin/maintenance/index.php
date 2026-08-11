<?php $title = $title ?? 'Backup & Maintenance'; ?>
<div class="ck-grid ck-grid-cols-1 lg:ck-grid-cols-3 ck-gap-6 ck-mb-6">
    <div class="ck-bg-white ck-rounded-lg ck-shadow">
        <div class="ck-p-6 ck-border-b">
            <h3 class="ck-text-lg ck-font-bold">Create Backup</h3>
        </div>
        <div class="ck-p-6">
            <p class="ck-text-sm ck-text-gray-500 ck-mb-4">
                Take a snapshot of the current database. Backups are stored in
                <code>writable/backups</code>.
            </p>
            <form method="post" action="/admin/maintenance/backup">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm">Run Backup Now</button>
            </form>
            <?php if (isset($backup_due) && $backup_due !== null): ?>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-3">Last backup: <?= (int) $backup_due ?> min ago</p>
            <?php else: ?>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-3">No backups yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="ck-bg-white ck-rounded-lg ck-shadow">
        <div class="ck-p-6 ck-border-b">
            <h3 class="ck-text-lg ck-font-bold">Maintenance Mode</h3>
        </div>
        <div class="ck-p-6">
            <?php if ($maintenance_enabled): ?>
                <p class="ck-text-sm ck-text-red-600 ck-mb-3">Maintenance is ON — the public site returns 503.</p>
            <?php else: ?>
                <p class="ck-text-sm ck-text-gray-500 ck-mb-3">Maintenance is OFF.</p>
            <?php endif; ?>
            <form method="POST" action="/admin/maintenance/toggle">
                <?= csrf_field() ?>
                <input type="hidden" name="enabled" value="<?= $maintenance_enabled ? '0' : '1' ?>">
                <button type="submit" class="ck-px-4 ck-py-2 <?= $maintenance_enabled ? 'ck-bg-red-600' : 'ck-bg-green-600' ?> ck-text-white ck-rounded ck-text-sm">
                    <?= $maintenance_enabled ? 'Disable Maintenance' : 'Enable Maintenance' ?>
                </button>
            </form>
        </div>
    </div>

    <div class="ck-bg-white ck-rounded-lg ck-shadow">
        <div class="ck-p-6 ck-border-b">
            <h3 class="ck-text-lg ck-font-bold">Web Cron</h3>
        </div>
        <div class="ck-p-6">
            <p class="ck-text-sm ck-text-gray-500 ck-mb-4">
                HTTP-only endpoint for shared hosting. Call it from a remote
                scheduler with <code>curl</code>/<code>wget</code> to process the
                media queue and create backups — no shell cron required.
            </p>

            <form method="POST" action="/admin/maintenance/cron/token" class="ck-mb-4">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-gray-800 ck-text-white ck-rounded ck-text-sm">Generate New Token</button>
            </form>

            <form method="POST" action="/admin/maintenance/cron" class="ck-space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Cron Token</label>
                    <input type="text" name="cron_token" value="<?= esc($cron_token) ?>" placeholder="empty = disabled" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-font-mono">
                </div>
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Tasks (comma-separated)</label>
                    <input type="text" name="cron_tasks" value="<?= esc($cron_tasks) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-font-mono">
                    <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Valid tasks: <code>media:queue</code>, <code>backup:create</code>.</p>
                </div>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm">Save Cron Settings</button>
            </form>

            <?php if (! empty($cron_token)): ?>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-4 ck-break-all">
                    Endpoint: <code><?= esc(base_url('/cron/run/' . $cron_token)) ?></code>
                </p>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-2">
                    e.g. <code>wget -q -O /dev/null '<?= esc(base_url('/cron/run/' . $cron_token)) ?>'</code>
                </p>
            <?php else: ?>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-4">Set a token to enable the endpoint.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="ck-bg-white ck-rounded-lg ck-shadow">
        <div class="ck-p-6 ck-border-b">
            <h3 class="ck-text-lg ck-font-bold">Retention</h3>
        </div>
        <div class="ck-p-6">
            <form method="POST" action="/admin/maintenance/settings">
                <?= csrf_field() ?>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Keep last N backups</label>
                <div class="ck-flex ck-gap-2">
                    <input type="number" name="backup_keep_count" min="1" value="<?= (int) $backup_keep_count ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <button type="submit" class="ck-px-4 ck-py-2 ck-bg-gray-800 ck-text-white ck-rounded ck-text-sm">Save</button>
                </div>
            </form>
            <p class="ck-text-xs ck-text-gray-500 ck-mt-3">
                CLI: <code>php spark backup:create</code> schedule via cron, e.g.
                <code>0 3 * * *</code>.
            </p>
        </div>
    </div>
</div>

<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-lg ck-font-bold">Backups (<?= count($backups ?? []) ?>)</h3>
    </div>
    <?php if (!empty($backups)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">File</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Type</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Size</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Created</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><code><?= esc($backup['filename']) ?></code></td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><?= esc($backup['type'] ?? 'db') ?></td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><?= esc($backup['size_human'] ?? '—') ?></td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><?= esc($backup['created_at'] ?? '—') ?></td>
                        <td class="ck-px-6 ck-py-3">
                            <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs <?= ($backup['status'] ?? '') === 'error' ? 'ck-bg-red-100 ck-text-red-800' : 'ck-bg-green-100 ck-text-green-800' ?>">
                                <?= esc($backup['status'] ?? 'success') ?>
                            </span>
                        </td>
                        <td class="ck-px-6 ck-py-3">
                            <?php if (($backup['status'] ?? '') !== 'error'): ?>
                                <a href="/admin/maintenance/download/<?= (int) $backup['id'] ?>" class="ck-text-blue-600 hover:ck-underline ck-text-sm">Download</a>
                            <?php endif; ?>
                            <form action="/admin/maintenance/delete/<?= (int) $backup['id'] ?>" method="post" class="ck-inline ck-ml-2" onsubmit="return confirm('Delete this backup?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php if (!empty($backup['message']) && ($backup['status'] ?? '') === 'error'): ?>
                        <tr class="ck-border-t">
                            <td colspan="6" class="ck-px-6 ck-py-2 ck-text-xs ck-text-red-600"><?= esc($backup['message']) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No backups have been created yet.</p>
    <?php endif; ?>
</div>