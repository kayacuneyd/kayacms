<?php $title = $title ?? $collection['name'] ?? 'Collection'; ?>
<div class="ck-grid ck-grid-cols-1 lg:ck-grid-cols-2 ck-gap-6">
    <div class="ck-bg-white ck-rounded-lg ck-shadow">
        <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
            <h3 class="ck-text-xl ck-font-bold"><?= esc($collection['name']) ?></h3>
            <a href="/admin/collections" class="ck-text-sm ck-text-blue-600 hover:ck-underline">Back</a>
        </div>

        <form method="post" action="/admin/collections/update/<?= (int) $collection['id'] ?>" class="ck-p-6 ck-space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Name</label>
                <input type="text" name="name" value="<?= esc($collection['name']) ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Slug</label>
                <input type="text" name="slug" value="<?= esc($collection['slug']) ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            </div>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Description</label>
                <textarea name="description" rows="3" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm"><?= esc($collection['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm">Save</button>
        </form>
    </div>

    <div class="ck-bg-white ck-rounded-lg ck-shadow">
        <div class="ck-p-6 ck-border-b">
            <h3 class="ck-text-xl ck-font-bold">Items (<?= count($items) ?>)</h3>
        </div>
        <div class="ck-p-6">
            <form method="post" action="/admin/collections/add/<?= (int) $collection['id'] ?>" class="ck-flex ck-gap-2 ck-mb-4">
                <?= csrf_field() ?>
                <select name="content_id" required class="ck-flex-1 ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
                    <option value="">Add content...</option>
                    <?php foreach ($available as $item): ?>
                        <option value="<?= (int) $item->id ?>"><?= esc($item->title) ?> (<?= esc($item->status) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm">Add</button>
            </form>

            <?php if (!empty($items)): ?>
                <ul class="ck-space-y-2">
                    <?php foreach ($items as $item): ?>
                        <li class="ck-flex ck-items-center ck-justify-between ck-border ck-rounded ck-px-4 ck-py-3">
                            <div>
                                <p class="ck-text-sm ck-font-medium"><?= esc($item->title) ?></p>
                                <p class="ck-text-xs ck-text-gray-500"><?= esc($item->content_type) ?> · <?= esc($item->status) ?></p>
                            </div>
                            <form action="/admin/collections/detach/<?= (int) $collection['id'] ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="content_id" value="<?= (int) $item->id ?>">
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-text-xs">Remove</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="ck-text-gray-500 ck-text-center ck-py-8">No items in this collection.</p>
            <?php endif; ?>
        </div>
    </div>
</div>