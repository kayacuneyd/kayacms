<?php $title = $title ?? 'Webhook Deliveries'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <h3 class="ck-text-xl ck-font-bold">Webhook Deliveries</h3>
        <a href="/admin/webhooks" class="ck-text-sm ck-text-blue-600 hover:ck-underline">Back to Webhooks</a>
    </div>

    <?php if (!empty($deliveries)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Time</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Event</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Payload</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Response</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $delivery): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><?= esc($delivery['created_at'] ?? '-') ?></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><code><?= esc($delivery['event']) ?></code></td>
                        <td class="ck-px-6 ck-py-4 ck-text-xs ck-truncate ck-max-w-xs"><?= esc($delivery['payload']) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs <?= $delivery['status'] === 'success' ? 'ck-bg-green-100 ck-text-green-800' : 'ck-bg-red-100 ck-text-red-800' ?>">
                                <?= esc($delivery['status']) ?>
                            </span>
                        </td>
                        <td class="ck-px-6 ck-py-4 ck-text-xs"><?= $delivery['response_code'] ?: '-' ?> <?= $delivery['response_body'] ? '— ' . esc(substr($delivery['response_body'], 0, 80)) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No deliveries recorded yet.</p>
    <?php endif; ?>
</div>