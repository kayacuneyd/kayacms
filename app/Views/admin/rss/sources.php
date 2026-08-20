<div class="bp-admin-stack">
    <div class="bp-admin-actionbar">
        <div>
            <h2 class="ck-text-xl ck-font-bold ck-m-0">RSS Sources</h2>
            <p class="bp-admin-help">Seeded sources can be disabled without deleting their inbox history.</p>
        </div>
        <a class="bp-admin-secondary-button" href="<?= site_url('admin/rss/inbox') ?>">Inbox</a>
    </div>

    <div class="bp-admin-table-wrap">
        <table class="bp-admin-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Country / language</th>
                    <th>Last fetch</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($sources ?? []) as $source): ?>
                    <tr>
                        <td><strong><?= esc($source['name']) ?></strong><br><a href="<?= esc($source['feed_url']) ?>" target="_blank" rel="noopener"><?= esc($source['feed_url']) ?></a></td>
                        <td><?= esc($source['country'] ?? '') ?> / <?= esc($source['language'] ?? '') ?></td>
                        <td><?= esc($source['last_fetched_at'] ?? 'Never') ?><?php if (! empty($source['last_error'])): ?><br><span class="ck-text-red-600"><?= esc($source['last_error']) ?></span><?php endif; ?></td>
                        <td><span class="bp-status-pill <?= ! empty($source['is_active']) ? 'bp-status-subscribed' : 'bp-status-failed' ?>"><?= ! empty($source['is_active']) ? 'active' : 'inactive' ?></span></td>
                        <td><form method="post" action="<?= site_url('admin/rss/sources/toggle/' . $source['id']) ?>"><?= csrf_field() ?><button class="bp-admin-secondary-button" type="submit"><?= ! empty($source['is_active']) ? 'Disable' : 'Enable' ?></button></form></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
