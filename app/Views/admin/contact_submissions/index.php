<?php $title = $title ?? 'Submissions'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Submissions: <?= esc($form['name'] ?? '') ?></h3>
        <a href="/admin/contact-forms" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Back to Forms</a>
    </div>

    <div class="ck-flex ck-gap-2 ck-mb-4">
        <a href="?status=" class="ck-px-3 ck-py-1 ck-text-sm ck-rounded <?= $status === '' ? 'ck-bg-blue-600 ck-text-white' : 'ck-bg-gray-100 ck-text-gray-700' ?>">All</a>
        <a href="?status=new" class="ck-px-3 ck-py-1 ck-text-sm ck-rounded <?= $status === 'new' ? 'ck-bg-blue-600 ck-text-white' : 'ck-bg-gray-100 ck-text-gray-700' ?>">New</a>
        <a href="?status=read" class="ck-px-3 ck-py-1 ck-text-sm ck-rounded <?= $status === 'read' ? 'ck-bg-blue-600 ck-text-white' : 'ck-bg-gray-100 ck-text-gray-700' ?>">Read</a>
        <a href="?status=archived" class="ck-px-3 ck-py-1 ck-text-sm ck-rounded <?= $status === 'archived' ? 'ck-bg-blue-600 ck-text-white' : 'ck-bg-gray-100 ck-text-gray-700' ?>">Archived</a>
    </div>

    <?php if (!empty($items)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">ID</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Preview</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Date</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item):
                    $item = is_array($item) ? $item : (array) $item;
                    $preview = '';
                    if (!empty($item['data']) && is_array($item['data'])) {
                        $first = reset($item['data']);
                        $preview = is_array($first) ? json_encode($first) : (string) $first;
                    }
                ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4">#<?= $item['id'] ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc(substr($preview, 0, 60)) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <?php if ($item['status'] === 'new'): ?>
                                <span class="ck-px-2 ck-py-1 ck-bg-red-100 ck-text-red-700 ck-rounded ck-text-xs">New</span>
                            <?php elseif ($item['status'] === 'read'): ?>
                                <span class="ck-px-2 ck-py-1 ck-bg-blue-100 ck-text-blue-700 ck-rounded ck-text-xs">Read</span>
                            <?php else: ?>
                                <span class="ck-px-2 ck-py-1 ck-bg-gray-100 ck-text-gray-600 ck-rounded ck-text-xs">Archived</span>
                            <?php endif; ?>
                        </td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item['created_at']) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <a href="/admin/contact-forms/submissions/show/<?= $item['id'] ?>" class="ck-text-blue-600 hover:ck-text-blue-800 ck-mr-4">View</a>
                            <?php if ($canUpdate ?? false): ?>
                                <?php if ($item['status'] !== 'archived'): ?>
                                    <form action="/admin/contact-forms/submissions/status/<?= $item['id'] ?>/archived" method="post" class="ck-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="ck-text-gray-600 hover:ck-text-gray-800 ck-mr-4">Archive</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($canDelete ?? false): ?>
                                <form action="/admin/contact-forms/submissions/delete/<?= $item['id'] ?>" method="post" class="ck-inline" onsubmit="return confirm('Delete this submission?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-red-600 hover:ck-text-red-800">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No submissions yet.</p>
    <?php endif; ?>
</div>
