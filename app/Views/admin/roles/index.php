<?php $title = $title ?? 'Roles'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Roles</h3>
        <a href="/admin/roles/create" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">+ New Role</a>
    </div>

    <?php if (!empty($items)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Name</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Permissions</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4"><?= esc($item->name ?? '') ?></td>
                        <td class="ck-px-6 ck-py-4"><?= count($item->permissions ?? []) ?> permissions</td>
                        <td class="ck-px-6 ck-py-4">
                            <a href="/admin/roles/edit/<?= $item->id ?>" class="ck-text-blue-600 hover:ck-text-blue-800">Edit</a>
                            <?php if (($item->name ?? '') !== 'admin'): ?>
                                <form action="/admin/roles/delete/<?= $item->id ?>" method="post" class="ck-inline" onsubmit="return confirm('Are you sure?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-ml-4">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No roles configured yet</p>
    <?php endif; ?>
</div>
