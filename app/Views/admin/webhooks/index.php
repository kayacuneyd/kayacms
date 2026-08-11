<?php $title = $title ?? 'Webhooks'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center ck-flex-wrap ck-gap-4">
        <div>
            <h3 class="ck-text-xl ck-font-bold">Webhooks</h3>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-1">Deliver real-time event payloads to external HTTPS endpoints.</p>
        </div>
        <a href="/admin/webhooks/deliveries" class="ck-text-sm ck-text-blue-600 hover:ck-underline">Delivery history</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="ck-bg-green-50 ck-border ck-border-green-300 ck-text-green-800 ck-p-4 ck-mx-6 ck-mt-4 ck-rounded">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="ck-p-6 ck-border-b ck-bg-gray-50">
        <form method="post" action="/admin/webhooks/store" class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-4">
            <?= csrf_field() ?>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Name</label>
                <input type="text" name="name" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Event</label>
                <select name="event" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
                    <?php foreach (['content.created', 'content.updated', 'content.deleted', 'comment.created', 'contact.submitted', 'user.created'] as $ev): ?>
                        <option value="<?= $ev ?>"><?= $ev ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:ck-col-span-2">
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Endpoint URL</label>
                <input type="url" name="url" required placeholder="https://example.com/hooks/kayacms" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm hover:ck-bg-blue-700">Add Webhook</button>
            </div>
        </form>
    </div>

    <?php if (!empty($webhooks)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Name</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Event</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">URL</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($webhooks as $hook): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4 ck-font-medium"><?= esc($hook['name']) ?></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><code><?= esc($hook['event']) ?></code></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm ck-truncate ck-max-w-xs"><?= esc($hook['url']) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs <?= $hook['is_active'] ? 'ck-bg-green-100 ck-text-green-800' : 'ck-bg-gray-100 ck-text-gray-600' ?>">
                                <?= $hook['is_active'] ? 'Active' : 'Paused' ?>
                            </span>
                        </td>
                        <td class="ck-px-6 ck-py-4">
                            <form action="/admin/webhooks/toggle/<?= (int) $hook['id'] ?>" method="post" class="ck-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-blue-600 hover:ck-text-blue-800 ck-text-sm"><?= $hook['is_active'] ? 'Pause' : 'Activate' ?></button>
                            </form>
                            <form action="/admin/webhooks/delete/<?= (int) $hook['id'] ?>" method="post" class="ck-inline ck-ml-2" onsubmit="return confirm('Delete this webhook?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No webhooks configured.</p>
    <?php endif; ?>
</div>