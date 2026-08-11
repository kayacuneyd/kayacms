<?php $title = $title ?? 'Content Revisions'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <div>
            <h3 class="ck-text-xl ck-font-bold">Revisions: <?= esc($item->title ?? '') ?></h3>
            <p class="ck-text-sm ck-text-gray-500">Current version and previous snapshots</p>
        </div>
        <a href="/admin/content/edit/<?= $item->id ?? '' ?>" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Back to Edit</a>
    </div>

    <?php if (!empty($revisions)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Version</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Title</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Saved At</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revisions as $revision): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4"><?= (int) $revision['version'] ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($revision['title']) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($revision['status']) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($revision['created_at']) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <?php if ($canRestore): ?>
                                <form action="/admin/content/restore/<?= $item->id ?? '' ?>/<?= $revision['id'] ?>" method="post" class="ck-inline" onsubmit="return confirm('Restore this revision? Current content will be saved as a new revision first.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-blue-600 hover:ck-text-blue-800">Restore</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No revisions yet. Revisions are created automatically when content is updated.</p>
    <?php endif; ?>
</div>
