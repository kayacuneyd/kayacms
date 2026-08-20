<section class="bp-dashboard">
    <div class="bp-dashboard-grid">
        <article class="bp-stat-card"><span>Items With Issues</span><strong><?= count($items ?? []) ?></strong><small>Latest 150 content records scanned</small></article>
        <?php foreach (array_slice(($summary ?? []), 0, 5, true) as $label => $count): ?>
            <article class="bp-stat-card"><span><?= esc($label) ?></span><strong><?= (int) $count ?></strong><small>actionable fixes</small></article>
        <?php endforeach; ?>
    </div>

    <section class="bp-admin-panel bp-admin-panel-wide">
        <div class="bp-panel-head"><h2>SEO Endpoints</h2><span>Verify public discovery URLs</span></div>
        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-5 ck-gap-3 ck-p-4">
            <?php foreach (($endpoints ?? []) as $endpoint): ?>
                <a class="ck-p-3 ck-bg-gray-100 ck-rounded ck-text-sm ck-block" href="<?= esc($endpoint['url']) ?>" target="_blank" rel="noopener"><strong><?= esc($endpoint['label']) ?></strong><br><small><?= esc($endpoint['hint'] ?? '') ?></small></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="bp-admin-panel bp-admin-panel-wide ck-mt-6">
        <div class="bp-panel-head"><h2>Content SEO Action List</h2><span>Sort by lowest score first</span></div>
        <?php if (! empty($items)): ?>
        <?php usort($items, static fn ($a, $b) => ($a['score'] ?? 100) <=> ($b['score'] ?? 100)); ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50"><tr><th class="ck-px-4 ck-py-3 ck-text-left">Score</th><th class="ck-px-4 ck-py-3 ck-text-left">Content</th><th class="ck-px-4 ck-py-3 ck-text-left">Issues & next actions</th><th class="ck-px-4 ck-py-3 ck-text-left">Links</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr class="ck-border-t">
                    <td class="ck-px-4 ck-py-3"><strong><?= (int) ($item['score'] ?? 0) ?></strong><br><small>/100</small></td>
                    <td class="ck-px-4 ck-py-3"><a href="<?= esc($item['edit_url']) ?>"><strong><?= esc($item['title']) ?></strong></a><br><small><?= esc(($item['content_type'] ?? '') . ' · ' . ($item['locale'] ?? '') . ' · ' . ($item['status'] ?? '')) ?></small></td>
                    <td class="ck-px-4 ck-py-3">
                        <div class="ck-grid ck-gap-2">
                            <?php foreach (($item['issues'] ?? []) as $issue): ?>
                                <div><span class="bp-status bp-status-<?= esc($issue['severity'] ?? 'medium') ?>"><?= esc($issue['label'] ?? 'Issue') ?></span> <small><?= esc($issue['action'] ?? '') ?></small></div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="ck-px-4 ck-py-3"><a href="<?= esc($item['edit_url']) ?>">Edit</a><br><a href="<?= esc($item['public_url']) ?>" target="_blank" rel="noopener">Public</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="bp-empty-state">No SEO issues found in scanned content.</p><?php endif; ?>
    </section>
</section>
