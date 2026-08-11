<?php
$item = $item ?? [];
$menuItems = $menuItems ?? [];
$contentOptions = $contentOptions ?? [];

$children = array_filter($menuItems, static fn($m) => ($m['parent_id'] ?? null) == $item['id']);
$hasChildren = ! empty($children);
?>
<li data-id="<?= $item['id'] ?>" class="ck-border ck-rounded ck-p-4 ck-bg-gray-50">
    <div class="ck-flex ck-justify-between ck-items-start">
        <div>
            <p class="ck-font-bold"><?= esc($item['title']) ?></p>
            <p class="ck-text-sm ck-text-gray-500"><?= esc($item['url']) ?> <?= $item['target'] === '_blank' ? '(new window)' : '' ?></p>
        </div>
        <div class="ck-flex ck-gap-2">
            <button type="button" class="edit-item-btn ck-px-3 ck-py-1 ck-text-sm ck-bg-gray-200 ck-rounded hover:ck-bg-gray-300" data-id="<?= $item['id'] ?>">Edit</button>
            <form method="post" action="/admin/menus/items/delete/<?= $item['id'] ?>" class="ck-inline" onsubmit="return confirm('Delete this item?')">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-3 ck-py-1 ck-text-sm ck-bg-red-100 ck-text-red-700 ck-rounded hover:ck-bg-red-200">Delete</button>
            </form>
        </div>
    </div>

    <form id="edit-form-<?= $item['id'] ?>" method="post" action="/admin/menus/items/update/<?= $item['id'] ?>" class="ck-hidden ck-mt-4 ck-p-4 ck-bg-white ck-rounded ck-border ck-space-y-4">
        <?= csrf_field() ?>
        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-4">
            <div>
                <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Title</label>
                <input type="text" name="title" value="<?= esc($item['title']) ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>
            <div>
                <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">URL</label>
                <input type="text" name="url" value="<?= esc($item['url']) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>
        </div>
        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-4">
            <div>
                <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Content</label>
                <select name="content_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">-- None --</option>
                    <?php foreach ($contentOptions as $content): ?>
                        <option value="<?= $content->id ?? '' ?>" <?= ($item['content_id'] ?? null) == ($content->id ?? null) ? 'selected' : '' ?>><?= esc($content->title ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Target</label>
                <select name="target" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="_self" <?= ($item['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>Same window</option>
                    <option value="_blank" <?= ($item['target'] ?? '_self') === '_blank' ? 'selected' : '' ?>>New window</option>
                </select>
            </div>
        </div>
        <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Update Item</button>
    </form>

    <?php if ($hasChildren): ?>
        <ul class="menu-children ck-mt-4 ck-ml-6 ck-space-y-2">
            <?php foreach ($children as $child): ?>
                <?= view('admin/menus/_item', ['item' => $child, 'menuItems' => $menuItems, 'contentOptions' => $contentOptions]) ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <ul class="menu-children ck-mt-4 ck-ml-6 ck-space-y-2"></ul>
    <?php endif; ?>
</li>
