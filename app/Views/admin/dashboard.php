<?php $title = $title ?? 'Dashboard'; ?>
<!-- Stat Cards -->
<div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 lg:ck-grid-cols-4 ck-gap-6 ck-mb-8">
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <h3 class="ck-text-gray-500 ck-text-sm">Total Content</h3>
        <p class="ck-text-3xl ck-font-bold ck-mt-2"><?= (int) ($stats['total_content'] ?? 0) ?></p>
        <p class="ck-text-xs ck-text-green-600 ck-mt-1"><?= (int) ($stats['published_content'] ?? 0) ?> published, <?= (int) ($stats['draft_content'] ?? 0) ?> drafts</p>
    </div>
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <h3 class="ck-text-gray-500 ck-text-sm">Media Files</h3>
        <p class="ck-text-3xl ck-font-bold ck-mt-2"><?= (int) ($stats['total_media'] ?? 0) ?></p>
    </div>
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <h3 class="ck-text-gray-500 ck-text-sm">Users</h3>
        <p class="ck-text-3xl ck-font-bold ck-mt-2"><?= (int) ($stats['total_users'] ?? 0) ?></p>
    </div>
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <h3 class="ck-text-gray-500 ck-text-sm">Pending Comments</h3>
        <p class="ck-text-3xl ck-font-bold ck-mt-2">
            <?php $pc = (int) ($stats['pending_comments'] ?? 0); ?>
            <a href="/admin/comments" class="<?= $pc > 0 ? 'ck-text-orange-600' : 'ck-text-gray-900' ?>"><?= $pc ?></a>
        </p>
    </div>
</div>

<div class="ck-grid ck-grid-cols-1 lg:ck-grid-cols-3 ck-gap-6 ck-mb-8">
    <!-- Content chart -->
    <div class="ck-col-span-2 ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <div class="ck-flex ck-justify-between ck-items-center ck-mb-4">
            <h3 class="ck-text-lg ck-font-bold">Content Added (14 days)</h3>
        </div>
        <div class="ck-flex ck-items-end ck-gap-2 ck-h-40">
            <?php $maxVal = max(1, max($chart['values'])); ?>
            <?php foreach ($chart['values'] as $i => $val): ?>
                <?php $h = (int) (($val / $maxVal) * 100); ?>
                <div class="ck-flex-1 ck-flex ck-flex-col ck-items-center ck-justify-end" title="<?= esc($chart['labels'][$i]) ?>: <?= (int) $val ?>">
                    <div class="ck-w-full ck-bg-blue-500 hover:ck-bg-blue-600 ck-rounded-t" style="height: <?= max(4, $h) ?>%"></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="ck-flex ck-gap-2 ck-mt-1">
            <?php foreach ($chart['labels'] as $label): ?>
                <div class="ck-flex-1 ck-text-center ck-text-xs ck-text-gray-400 ck-truncate"><?= esc($label) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent activity -->
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <div class="ck-flex ck-justify-between ck-items-center ck-mb-4">
            <h3 class="ck-text-lg ck-font-bold">Recent Activity</h3>
            <a href="/admin/activity-logs" class="ck-text-xs ck-text-blue-600 hover:ck-underline">View all</a>
        </div>
        <?php if (!empty($recentActivity)): ?>
            <ul class="ck-space-y-3">
                <?php foreach ($recentActivity as $log): ?>
                    <li class="ck-text-sm ck-flex ck-justify-between ck-gap-2">
                        <span class="ck-truncate">
                            <span class="ck-font-medium"><?= esc($log['username'] ?? 'System') ?></span>
                            <?= esc($log['action']) ?>
                        </span>
                        <span class="ck-text-xs ck-text-gray-400 ck-shrink-0"><?= esc(timeAgo($log['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="ck-text-gray-500 ck-text-center">No recent activity.</p>
        <?php endif; ?>
    </div>
</div>

<div class="ck-grid ck-grid-cols-1 lg:ck-grid-cols-2 ck-gap-6 ck-mb-8">
    <!-- Recent content -->
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <div class="ck-flex ck-justify-between ck-items-center ck-mb-4">
            <h3 class="ck-text-lg ck-font-bold">Recent Content</h3>
            <a href="/admin/content" class="ck-text-xs ck-text-blue-600 hover:ck-underline">View all</a>
        </div>
        <?php if (!empty($recentContent)): ?>
            <ul class="ck-space-y-3">
                <?php foreach ($recentContent as $item): ?>
                    <li class="ck-flex ck-items-center ck-justify-between ck-gap-2">
                        <div class="ck-truncate">
                            <a href="/admin/content/edit/<?= (int) $item->id ?>" class="ck-text-sm ck-font-medium hover:ck-underline"><?= esc($item->title) ?></a>
                            <span class="ck-text-xs ck-text-gray-400 ck-block"><?= esc($item->content_type) ?></span>
                        </div>
                        <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs ck-font-medium <?= getStatusBadge($item->status) ?>"><?= esc($item->status) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="ck-text-gray-500 ck-text-center">No content yet.</p>
        <?php endif; ?>
    </div>

    <!-- Recent comments -->
    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
        <div class="ck-flex ck-justify-between ck-items-center ck-mb-4">
            <h3 class="ck-text-lg ck-font-bold">Recent Comments</h3>
            <a href="/admin/comments" class="ck-text-xs ck-text-blue-600 hover:ck-underline">View all</a>
        </div>
        <?php if (!empty($recentComments)): ?>
            <ul class="ck-space-y-3">
                <?php foreach ($recentComments as $c): ?>
                    <li class="ck-text-sm">
                        <span class="ck-font-medium"><?= esc($c['author_name']) ?></span>
                        <span class="ck-text-xs ck-px-2 ck-py-0 ck-rounded <?= getStatusBadge($c['status']) ?>"><?= esc($c['status']) ?></span>
                        <p class="ck-text-xs ck-text-gray-600 ck-truncate ck-mt-1"><?= esc($c['body']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="ck-text-gray-500 ck-text-center">No comments yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php
function getStatusBadge(string $status): string
{
    return match ($status) {
        'published' => 'ck-bg-green-100 ck-text-green-800',
        'draft'     => 'ck-bg-yellow-100 ck-text-yellow-800',
        'pending'   => 'ck-bg-orange-100 ck-text-orange-800',
        'approved'  => 'ck-bg-green-100 ck-text-green-800',
        default     => 'ck-bg-gray-100 ck-text-gray-800',
    };
}

function timeAgo(string $date): string
{
    $diff = time() - strtotime($date);
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        return round($diff / 60) . 'm ago';
    }
    if ($diff < 86400) {
        return round($diff / 3600) . 'h ago';
    }

    return round($diff / 86400) . 'd ago';
}
?>