<?php $title = $title ?? 'Notifications'; ?>
<div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
    <h3 class="ck-text-xl ck-font-bold">Notifications</h3>
    <form action="/admin/notifications/clear" method="post" onsubmit="return confirm('Clear read notifications?')">
        <?= csrf_field() ?>
        <button type="submit" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-text-gray-600 ck-rounded hover:ck-bg-gray-50">Clear read</button>
    </form>
</div>

<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <?php if (! empty($items)): ?>
        <ul class="ck-divide-y ck-divide-gray-100">
            <?php foreach ($items as $item): ?>
                <li class="ck-py-4 <?= empty($item['is_read']) ? 'ck-bg-blue-50' : '' ?>">
                    <div class="ck-flex ck-justify-between ck-items-start ck-gap-4">
                        <div class="ck-flex-1">
                            <p class="ck-font-medium ck-text-gray-800"><?= esc($item['title']) ?></p>
                            <?php if (! empty($item['body'])): ?>
                                <p class="ck-text-sm ck-text-gray-600 ck-mt-1"><?= nl2br(esc($item['body'])) ?></p>
                            <?php endif; ?>
                            <p class="ck-text-xs ck-text-gray-400 ck-mt-2"><?= date('d M Y H:i', strtotime($item['created_at'])) ?></p>
                        </div>
                        <?php if (! empty($item['url'])): ?>
                            <a href="<?= esc($item['url']) ?>" class="ck-text-sm ck-text-blue-600 hover:ck-underline">View</a>
                        <?php endif; ?>
                        <?php if (empty($item['is_read'])): ?>
                            <form action="/admin/notifications/mark-read/<?= $item['id'] ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-xs ck-text-gray-400 hover:ck-text-gray-600">Mark read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <div class="ck-mt-6 ck-flex ck-gap-2 ck-justify-center">
                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                    <a href="<?= site_url('admin/notifications?page=' . $p) ?>"
                       class="ck-px-3 ck-py-1 ck-rounded ck-border <?= $p === ($pagination['current_page'] ?? 1) ? 'ck-bg-blue-600 ck-text-white' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No notifications</p>
    <?php endif; ?>
</div>