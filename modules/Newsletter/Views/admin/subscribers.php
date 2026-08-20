<div class="bp-admin-stack">
    <div class="bp-admin-actionbar">
        <form method="get" class="bp-admin-search">
            <input name="q" value="<?= esc($q ?? '') ?>" placeholder="Search subscribers...">
            <button class="bp-admin-button" type="submit">Search</button>
        </form>
        <a href="/admin/newsletter/subscribers/export" class="bp-admin-secondary-button">Export CSV</a>
    </div>

    <section class="bp-admin-panel">
        <div class="bp-panel-head">
            <h2>Add Subscriber</h2>
            <span>Only confirmed subscribers should remain here</span>
        </div>
        <form action="/admin/newsletter/subscribers/store" method="post" class="bp-admin-form-grid">
            <input type="email" name="email" placeholder="email@example.com" required>
            <input type="text" name="name" placeholder="Name">
            <button class="bp-admin-button" type="submit">Add Subscriber</button>
        </form>
        <form action="/admin/newsletter/subscribers/import" method="post" enctype="multipart/form-data" class="bp-admin-import">
            <input type="file" name="csv" accept=".csv,text/csv">
            <button class="bp-admin-secondary-button" type="submit">Import CSV</button>
        </form>
    </section>

    <section class="bp-admin-panel">
        <div class="bp-panel-head">
            <h2>Subscribers</h2>
            <span><?= count($items ?? []) ?> visible</span>
        </div>
        <div class="bp-admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Quality</th>
                        <th>Source</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= esc($item['email']) ?></td>
                        <td><?= esc($item['name'] ?? '') ?></td>
                        <td><span class="bp-status bp-status-<?= esc($item['status']) ?>"><?= esc($item['status']) ?></span></td>
                        <td><span class="bp-status bp-status-<?= esc($item['quality_status'] ?? 'unreviewed') ?>"><?= esc($item['quality_status'] ?? 'unreviewed') ?></span></td>
                        <td><?= esc($item['source']) ?></td>
                        <td>
                            <?php if ($item['status'] === 'subscribed'): ?>
                                <form action="/admin/newsletter/subscribers/unsubscribe/<?= (int) $item['id'] ?>" method="post">
                                    <button class="bp-admin-link-danger" type="submit">Unsubscribe</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?><tr><td colspan="6" class="bp-empty-cell">No subscribers yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
