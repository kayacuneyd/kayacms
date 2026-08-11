<?php $title = $title ?? 'Activity Logs'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Activity Logs</h3>
    </div>

    <?php if (!empty($summary)): ?>
        <div class="ck-grid ck-grid-cols-2 md:ck-grid-cols-4 ck-gap-4 ck-mb-6">
            <?php foreach ($summary as $row): ?>
                <div class="ck-bg-gray-50 ck-p-4 ck-rounded">
                    <span class="ck-text-sm ck-text-gray-500"><?= ucfirst(esc($row['action'])) ?></span>
                    <p class="ck-text-xl ck-font-bold"><?= (int) $row['total'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
        <div class="ck-overflow-x-auto">
            <table class="ck-w-full">
                <thead class="ck-bg-gray-50">
                    <tr>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Time</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">User</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Action</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Entity</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Description</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="ck-border-t">
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($item['created_at'] ?? '') ?></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($item['username'] ?? '') ?></td>
                            <td class="ck-px-4 ck-py-3"><span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs ck-font-medium <?= getLogBadgeClass($item['action']) ?>"><?= ucfirst(esc($item['action'])) ?></span></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($item['entity_type']) ?> #<?= (int) $item['entity_id'] ?></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($item['description'] ?? '') ?></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($item['ip_address'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No activity logs found</p>
    <?php endif; ?>
</div>

<?php
function getLogBadgeClass(string $action): string
{
    return match ($action) {
        'created' => 'ck-bg-green-100 ck-text-green-800',
        'updated' => 'ck-bg-blue-100 ck-text-blue-800',
        'deleted' => 'ck-bg-red-100 ck-text-red-800',
        'login'   => 'ck-bg-purple-100 ck-text-purple-800',
        'logout'  => 'ck-bg-gray-100 ck-text-gray-800',
        default   => 'ck-bg-gray-100 ck-text-gray-800',
    };
}
?>
