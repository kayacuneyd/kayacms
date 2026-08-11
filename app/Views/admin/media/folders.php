<?php $title = $title ?? 'Media Folders'; ?>
<div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
    <h3 class="ck-text-xl ck-font-bold">Media Folders</h3>
    <a href="/admin/media/folders/create" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">+ New Folder</a>
</div>

<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <?php if (!empty($folders)): ?>
        <table class="ck-w-full ck-text-left">
            <thead>
                <tr class="ck-border-b ck-border-gray-200">
                    <th class="ck-px-4 ck-py-3 ck-text-gray-500">Name</th>
                    <th class="ck-px-4 ck-py-3 ck-text-gray-500">Slug</th>
                    <th class="ck-px-4 ck-py-3 ck-text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($folders as $folder): ?>
                    <tr class="ck-border-b ck-border-gray-100">
                        <td class="ck-px-4 ck-py-3"><?= esc($folder['label']) ?></td>
                        <td class="ck-px-4 ck-py-3 ck-text-gray-500"><?= esc($folder['slug']) ?></td>
                        <td class="ck-px-4 ck-py-3">
                            <a href="/admin/media?folder=<?= $folder['id'] ?>" class="ck-text-xs ck-text-blue-600">View files</a>
                            <form action="/admin/media/folders/delete/<?= $folder['id'] ?>" method="post" class="ck-inline ck-ml-2" onsubmit="return confirm('Delete this folder? Media inside will be moved to root.')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-xs ck-text-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No folders yet</p>
    <?php endif; ?>
</div>