<?php $title = $title ?? 'Taxonomy'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold"><?= esc($item ? 'Edit Term' : 'New Term') ?></h3>
    </div>

    <form method="post" action="<?= $item ? '/admin/taxonomy/update/' . $item['id'] : '/admin/taxonomy/store' ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>
        <?php if ($item): ?>
            
        <?php endif; ?>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Name</label>
            <input type="text" name="name" value="<?= esc($item['name'] ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Slug</label>
            <input type="text" name="slug" value="<?= esc($item['slug'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Type</label>
            <select name="taxonomy_type" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <?php foreach (['category', 'tag', 'custom'] as $type): ?>
                    <option value="<?= $type ?>" <?= ($item['taxonomy_type'] ?? 'category') === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Description</label>
            <textarea name="description" rows="3" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md"><?= esc($item['description'] ?? '') ?></textarea>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            <a href="/admin/taxonomy" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
