<?php $title = $title ?? 'New Folder'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold">New Media Folder</h3>
    </div>

    <form method="post" action="/admin/media/folders/store" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Folder Name</label>
            <input type="text" name="name" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Parent Folder</label>
            <select name="parent_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <option value="">Root</option>
                <?php foreach (($folders ?? []) as $folder): ?>
                    <option value="<?= $folder['id'] ?>"><?= $folder['label'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Create Folder</button>
            <a href="/admin/media" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>