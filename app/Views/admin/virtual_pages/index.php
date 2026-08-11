<?php $title = $title ?? 'Virtual Pages'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Virtual Pages</h3>
        <a href="/admin/virtual-pages/create" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">+ New Virtual Page</a>
    </div>

    <p class="ck-text-sm ck-text-gray-500 ck-mb-6">
        Virtual pages are URL → content mappings with no backing content row. The slug is
        served directly at its URL (e.g. <code>/features</code>). Handlers: <code>template</code>
        (render a theme view), <code>markdown</code> (render a body), <code>redirect</code> (302).
    </p>

    <?php if (!empty($items)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Slug</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Title</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Handler</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4">
                            <a href="<?= base_url('/' . esc($item['slug'], 'url')) ?>" target="_blank" rel="noopener">/<?= esc($item['slug']) ?></a>
                        </td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item['title']) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item['handler']) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item['status']) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <a href="/admin/virtual-pages/edit/<?= $item['id'] ?>" class="ck-text-blue-600 hover:ck-text-blue-800">Edit</a>
                            <form action="/admin/virtual-pages/delete/<?= $item['id'] ?>" method="post" class="ck-inline" onsubmit="return confirm('Delete this virtual page?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-ml-4">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No virtual pages yet.</p>
    <?php endif; ?>
</div>