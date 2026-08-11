<?php $title = $title ?? 'Security'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Security Overview</h3>
    </div>

    <div class="ck-grid ck-grid-cols-2 md:ck-grid-cols-4 ck-gap-4 ck-mb-6">
        <div class="ck-bg-red-50 ck-p-4 ck-rounded">
            <span class="ck-text-sm ck-text-gray-500">Critical (24h)</span>
            <p class="ck-text-xl ck-font-bold"><?= (int) $critical24h ?></p>
        </div>
        <div class="ck-bg-yellow-50 ck-p-4 ck-rounded">
            <span class="ck-text-sm ck-text-gray-500">Warnings (24h)</span>
            <p class="ck-text-xl ck-font-bold"><?= (int) $warning24h ?></p>
        </div>
        <div class="ck-bg-blue-50 ck-p-4 ck-rounded">
            <span class="ck-text-sm ck-text-gray-500">Info Events (24h)</span>
            <p class="ck-text-xl ck-font-bold"><?= (int) $info24h ?></p>
        </div>
        <div class="ck-bg-gray-50 ck-p-4 ck-rounded">
            <span class="ck-text-sm ck-text-gray-500">Blocked IPs (24h)</span>
            <p class="ck-text-xl ck-font-bold"><?= (int) $blockedIps ?></p>
        </div>
    </div>

    <?php if (!empty($logs)): ?>
        <div class="ck-overflow-x-auto">
            <table class="ck-w-full">
                <thead class="ck-bg-gray-50">
                    <tr>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Time</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Severity</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Type</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">Message</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">IP</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left">User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="ck-border-t">
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($log['created_at'] ?? '-') ?></td>
                            <td class="ck-px-4 ck-py-3">
                                <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs ck-font-medium <?= getSecurityBadge($log['severity']) ?>">
                                    <?= ucfirst(esc($log['severity'])) ?>
                                </span>
                            </td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($log['type']) ?></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($log['message']) ?></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= esc($log['ip_address'] ?? '-') ?></td>
                            <td class="ck-px-4 ck-py-3 ck-text-sm"><?= $log['user_id'] ? (int) $log['user_id'] : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No security events recorded yet.</p>
    <?php endif; ?>
</div>

<?php
function getSecurityBadge(string $severity): string
{
    return match ($severity) {
        'critical' => 'ck-bg-red-100 ck-text-red-800',
        'warning'  => 'ck-bg-yellow-100 ck-text-yellow-800',
        default    => 'ck-bg-blue-100 ck-text-blue-800',
    };
}
?>