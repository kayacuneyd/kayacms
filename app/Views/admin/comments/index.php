<?php $title = $title ?? 'Comments'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold">Comments</h3>
        <p class="ck-text-sm ck-text-gray-500 ck-mt-1"><?= (int) ($pendingCount ?? 0) ?> pending moderation</p>
    </div>

    <div class="ck-p-6 ck-border-b ck-flex ck-flex-wrap ck-items-center ck-gap-3 ck-bg-gray-50">
        <form method="get" class="ck-flex ck-flex-wrap ck-items-center ck-gap-3 ck-w-full">
            <select name="status" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
                <option value="all">All statuses</option>
                <?php foreach (['pending', 'approved', 'spam'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($status ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="q" value="<?= esc($keyword ?? '') ?>" placeholder="Search author or comment..." class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm ck-flex-1">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded-md ck-text-sm">Filter</button>
            <?php if ($status || $keyword): ?>
                <a href="/admin/comments" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm hover:ck-bg-gray-100">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!empty($items)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Author</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Comment</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4">
                            <div class="ck-font-medium"><?= esc($item['author_name']) ?></div>
                            <div class="ck-text-sm ck-text-gray-500"><?= esc($item['author_email']) ?></div>
                        </td>
                        <td class="ck-px-6 ck-py-4"><?= nl2br(esc($item['body'])) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <span class="ck-px-2 ck-py-1 ck-rounded ck-text-sm <?= $item['status'] === 'approved' ? 'ck-bg-green-100 ck-text-green-800' : ($item['status'] === 'spam' ? 'ck-bg-red-100 ck-text-red-800' : 'ck-bg-yellow-100 ck-text-yellow-800') ?>">
                                <?= esc($item['status']) ?>
                            </span>
                        </td>
                        <td class="ck-px-6 ck-py-4">
                            <?php if ($item['status'] !== 'approved'): ?>
                                <form action="/admin/comments/approve/<?= $item['id'] ?>" method="post" class="ck-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-green-600 hover:ck-text-green-800">Approve</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($item['status'] !== 'spam'): ?>
                                <form action="/admin/comments/spam/<?= $item['id'] ?>" method="post" class="ck-inline ck-ml-2">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-yellow-600 hover:ck-text-yellow-800">Spam</button>
                                </form>
                            <?php endif; ?>
                            <form action="/admin/comments/delete/<?= $item['id'] ?>" method="post" class="ck-inline ck-ml-2" onsubmit="return confirm('Are you sure?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No comments yet</p>
    <?php endif; ?>
</div>
