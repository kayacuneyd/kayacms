<?php $title = $title ?? 'Contact Forms'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Contact Forms</h3>
        <?php if ($canCreate ?? false): ?>
            <a href="/admin/contact-forms/create" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">+ New Form</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($items)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Name</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Slug</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Fields</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item):
                    $item = is_array($item) ? $item : (array) $item;
                    $fields = is_string($item['fields'] ?? '') ? json_decode($item['fields'], true) : ($item['fields'] ?? []);
                ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4"><?= esc($item['name']) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item['slug']) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= count($fields) ?> field(s)</td>
                        <td class="ck-px-6 ck-py-4">
                            <?php if ($item['is_active']): ?>
                                <span class="ck-px-2 ck-py-1 ck-bg-green-100 ck-text-green-700 ck-rounded ck-text-xs">Active</span>
                            <?php else: ?>
                                <span class="ck-px-2 ck-py-1 ck-bg-gray-100 ck-text-gray-600 ck-rounded ck-text-xs">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="ck-px-6 ck-py-4">
                            <a href="/admin/contact-forms/submissions/<?= $item['id'] ?>" class="ck-text-green-600 hover:ck-text-green-800 ck-mr-4">Submissions</a>
                            <?php if ($canEdit ?? false): ?>
                                <a href="/admin/contact-forms/edit/<?= $item['id'] ?>" class="ck-text-blue-600 hover:ck-text-blue-800 ck-mr-4">Edit</a>
                            <?php endif; ?>
                            <?php if ($canDelete ?? false): ?>
                                <form action="/admin/contact-forms/delete/<?= $item['id'] ?>" method="post" class="ck-inline" onsubmit="return confirm('Are you sure?')">
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
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No contact forms yet.</p>
    <?php endif; ?>
</div>
