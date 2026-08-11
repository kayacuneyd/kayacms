<?php $title = $title ?? 'Collections'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <h3 class="ck-text-xl ck-font-bold">Content Collections</h3>
    </div>

    <div class="ck-p-6 ck-border-b ck-bg-gray-50">
        <form method="post" action="/admin/collections/store" class="ck-grid ck-grid-cols-1 md:ck-grid-cols-3 ck-gap-4">
            <?= csrf_field() ?>
            <div>
                <input type="text" name="name" required placeholder="Collection name" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div>
                <input type="text" name="slug" required placeholder="slug" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div>
                <input type="text" name="description" placeholder="Description (optional)" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div class="md:ck-col-span-3">
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm">Add Collection</button>
            </div>
        </form>
    </div>

    <?php if (!empty($collections)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Name</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Slug</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Items</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($collections as $collection): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4 ck-font-medium"><?= esc($collection['name']) ?></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><code><?= esc($collection['slug']) ?></code></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><?= (int) $collection['item_count'] ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <a href="/admin/collections/edit/<?= (int) $collection['id'] ?>" class="ck-text-blue-600 hover:ck-underline ck-text-sm">Manage</a>
                            <form action="/admin/collections/delete/<?= (int) $collection['id'] ?>" method="post" class="ck-inline ck-ml-2" onsubmit="return confirm('Delete collection?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No collections yet.</p>
    <?php endif; ?>
</div>