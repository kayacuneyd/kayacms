<?php $title = $title ?? 'Media Queue'; ?>
<div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-4 ck-gap-6 ck-mb-6">
    <?php foreach (['pending', 'processing', 'done', 'failed'] as $state): ?>
        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
            <p class="ck-text-sm ck-text-gray-500"><?= ucfirst($state) ?></p>
            <p class="ck-text-3xl ck-font-bold <?= $state === 'failed' && ($stats[$state] ?? 0) > 0 ? 'ck-text-red-600' : ($state === 'done' ? 'ck-text-green-600' : '') ?>">
                <?= (int) ($stats[$state] ?? 0) ?>
            </p>
        </div>
    <?php endforeach; ?>
</div>

<div class="ck-bg-white ck-rounded-lg ck-shadow ck-mb-6">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <div>
            <h3 class="ck-text-lg ck-font-bold">Run Queue</h3>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-1">
                Process pending jobs now. For periodic runs, schedule
                <code>php spark media:queue</code> via cron.
            </p>
        </div>
        <?php if ($canRun ?? false): ?>
            <form method="post" action="/admin/media/queue/run">
                <?= csrf_field() ?>
                <input type="hidden" name="limit" value="25">
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Run Queue Now</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-lg ck-font-bold">Jobs (<?= count($jobs ?? []) ?>)</h3>
    </div>
    <?php if (!empty($jobs)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">ID</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Type</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Media</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Attempts</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Error / Result</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Updated</th>
                    <th class="ck-px-6 ck-py-3 ck-text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <?php
                        $status = $job['status'] ?? 'pending';
                        $badge = match ($status) {
                            'done'       => 'ck-bg-green-100 ck-text-green-800',
                            'failed'     => 'ck-bg-red-100 ck-text-red-800',
                            'processing' => 'ck-bg-yellow-100 ck-text-yellow-800',
                            default      => 'ck-bg-gray-100 ck-text-gray-800',
                        };
                        $media = \Media\Models\MediaModel::class;
                    ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-3 ck-text-sm">#<?= (int) $job['id'] ?></td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><code><?= esc($job['type']) ?></code></td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm">#<?= (int) ($job['media_id'] ?? 0) ?></td>
                        <td class="ck-px-6 ck-py-3">
                            <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs <?= $badge ?>"><?= esc($status) ?></span>
                        </td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><?= (int) $job['attempts'] ?>/<?= (int) ($job['max_attempts'] ?? 3) ?></td>
                        <td class="ck-px-6 ck-py-3 ck-text-xs ck-text-gray-600">
                            <?php if (!empty($job['error'])): ?>
                                <span class="ck-text-red-600"><?= esc($job['error']) ?></span>
                            <?php elseif (!empty($job['result'])): ?>
                                <code><?= esc($job['result']) ?></code>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="ck-px-6 ck-py-3 ck-text-sm"><?= esc($job['updated_at'] ?? $job['created_at'] ?? '—') ?></td>
                        <td class="ck-px-6 ck-py-3 ck-text-right">
                            <?php if ($status === 'failed' && ($canRun ?? false)): ?>
                                <form method="post" action="/admin/media/queue/retry/<?= (int) $job['id'] ?>" class="ck-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-px-3 ck-py-1 ck-bg-gray-200 ck-text-gray-700 ck-rounded hover:ck-bg-gray-300 ck-text-xs">Retry</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No jobs in the queue.</p>
    <?php endif; ?>
</div>