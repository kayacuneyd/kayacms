<?php $ok = (int) ($summary['ok'] ?? 0); $failed = (int) ($summary['failed'] ?? 0); ?>
<section class="bp-dashboard">
    <div class="bp-dashboard-grid">
        <article class="bp-stat-card"><span>Checks OK</span><strong><?= $ok ?></strong><small><?= (int) ($summary['total'] ?? 0) ?> total checks</small></article>
        <article class="bp-stat-card"><span>Needs Attention</span><strong><?= $failed ?></strong><small><?= $failed ? 'Review failed checks below' : 'All green' ?></small></article>
        <?php foreach (($metrics ?? []) as $metric): ?>
            <article class="bp-stat-card"><span><?= esc($metric['label']) ?></span><strong><?= esc((string) $metric['value']) ?></strong></article>
        <?php endforeach; ?>
    </div>

    <section class="bp-admin-panel bp-admin-panel-wide">
        <div class="bp-panel-head"><h2>Runtime Checks</h2><span>Live v2 health</span></div>
        <table class="ck-w-full">
            <tbody>
            <?php foreach (($checks ?? []) as $check): ?>
                <tr class="ck-border-t">
                    <td class="ck-px-4 ck-py-3"><strong><?= esc($check['label']) ?></strong><br><small class="ck-text-gray-500"><?= esc($check['detail']) ?></small></td>
                    <td class="ck-px-4 ck-py-3 ck-text-right"><span class="bp-status <?= $check['ok'] ? 'bp-status-published' : 'bp-status-archived' ?>"><?= $check['ok'] ? 'OK' : 'FAIL' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="bp-admin-panel bp-admin-panel-wide ck-mt-6">
        <div class="bp-panel-head"><h2>Recent Log Lines</h2><span>No secrets shown</span></div>
        <?php if (! empty($logs)): ?>
            <pre class="ck-bg-gray-900 ck-text-gray-100 ck-p-4 ck-rounded ck-overflow-auto ck-text-xs"><?php foreach ($logs as $line): ?><?= esc($line) . "\n" ?><?php endforeach; ?></pre>
        <?php else: ?><p class="bp-empty-state">No readable log lines found.</p><?php endif; ?>
    </section>
</section>
