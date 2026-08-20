<div class="bp-admin-stack">
    <div class="bp-dashboard-grid">
        <article class="bp-stat-card"><span>Subscribers</span><strong><?= (int) $subscriberCount ?></strong><small>Clean list</small></article>
        <article class="bp-stat-card"><span>Pending Queue</span><strong><?= (int) ($queueStats['pending'] ?? 0) ?></strong><small>Ready to send</small></article>
        <article class="bp-stat-card"><span>Sent</span><strong><?= (int) ($queueStats['sent'] ?? 0) ?></strong><small>Delivered jobs</small></article>
        <article class="bp-stat-card"><span>Failed</span><strong><?= (int) ($queueStats['failed'] ?? 0) ?></strong><small>Needs attention</small></article>
    </div>

    <div class="bp-admin-actionbar">
        <a href="/admin/newsletter/subscribers" class="bp-admin-button">Subscribers</a>
        <a href="/admin/newsletter/campaigns/create" class="bp-admin-button">New Campaign</a>
        <form action="/admin/newsletter/queue/run" method="post"><button class="bp-admin-secondary-button" type="submit">Run Queue</button></form>
    </div>

    <section class="bp-admin-panel">
        <div class="bp-panel-head">
            <h2>Campaigns</h2>
            <span><?= count($campaigns ?? []) ?> total</span>
        </div>
        <div class="bp-admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td><?= esc($campaign['subject']) ?></td>
                        <td><?= esc($campaign['provider']) ?></td>
                        <td><span class="bp-status bp-status-<?= esc($campaign['status']) ?>"><?= esc($campaign['status']) ?></span></td>
                        <td class="bp-admin-row-actions">
                            <a href="/admin/newsletter/campaigns/edit/<?= (int) $campaign['id'] ?>">Edit</a>
                            <form action="/admin/newsletter/campaigns/enqueue/<?= (int) $campaign['id'] ?>" method="post"><button type="submit">Queue now</button></form>
                            <?php if (! empty($campaign['scheduled_at'])): ?>
                                <form action="/admin/newsletter/campaigns/schedule/<?= (int) $campaign['id'] ?>" method="post"><button type="submit">Schedule</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($campaigns)): ?><tr><td colspan="4" class="bp-empty-cell">No campaigns yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
