<?php $title = $title ?? 'Media'; ?>
<div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
    <h3 class="ck-text-xl ck-font-bold">Media Library</h3>
    <div class="ck-flex ck-gap-2">
        <?php if ($canEdit ?? false): ?>
            <a href="/admin/media/folders/create" class="ck-px-4 ck-py-2 ck-border ck-border-blue-300 ck-text-blue-700 ck-rounded hover:ck-bg-blue-50">+ Folder</a>
        <?php endif; ?>
        <?php if ($canUpload ?? false): ?>
            <a href="/admin/media/upload" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">+ Upload</a>
        <?php endif; ?>
    </div>
</div>

<form method="get" action="/admin/media" class="ck-bg-white ck-rounded-lg ck-shadow ck-p-4 ck-mb-6 ck-flex ck-flex-wrap ck-gap-4 ck-items-end">
    <div class="ck-flex-1">
        <label class="ck-block ck-text-sm ck-text-gray-600 ck-mb-1">Search</label>
        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
    </div>
    <div>
        <label class="ck-block ck-text-sm ck-text-gray-600 ck-mb-1">Folder</label>
        <select name="folder" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <option value="">All folders</option>
            <?php foreach (($folders ?? []) as $folder): ?>
                <option value="<?= $folder['id'] ?>" <?= ($currentFolder ?? null) === $folder['id'] ? 'selected' : '' ?>><?= $folder['label'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="ck-px-4 ck-py-2 ck-bg-gray-800 ck-text-white ck-rounded hover:ck-bg-gray-900">Filter</button>
</form>

<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <?php if (!empty($items)): ?>
        <div class="ck-grid ck-grid-cols-2 md:ck-grid-cols-3 lg:ck-grid-cols-4 xl:ck-grid-cols-5 ck-gap-4">
            <?php foreach ($items as $item): ?>
                <div class="ck-border ck-rounded-lg ck-overflow-hidden">
                    <a href="/admin/media/edit/<?= $item['id'] ?>" class="ck-block">
                        <div class="ck-aspect-square ck-bg-gray-100 ck-flex ck-items-center ck-justify-center">
                            <?php if ($item['is_image'] ?? false): ?>
                                <img src="<?= esc($item['thumbnail_path'] ? '/' . $item['thumbnail_path'] : $item['url']) ?>" alt="<?= esc($item['alt_text'] ?? '') ?>" class="ck-w-full ck-h-full ck-object-cover">
                            <?php else: ?>
                                <span class="ck-text-gray-400 ck-text-xs ck-px-2"><?= esc($item['mime_type'] ?? '') ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="ck-p-3">
                        <p class="ck-text-xs ck-text-gray-600 ck-truncate"><?= esc($item['original_name'] ?? $item['filename']) ?></p>
                        <p class="ck-text-xs ck-text-gray-400"><?= esc($item['formatted_size'] ?? '') ?></p>
                        <div class="ck-flex ck-gap-3 ck-mt-2">
                            <?php if ($canEdit ?? false): ?>
                                <a href="/admin/media/edit/<?= $item['id'] ?>" class="ck-text-xs ck-text-blue-600 hover:ck-text-blue-800">Edit</a>
                            <?php endif; ?>
                            <?php if ($canDelete ?? false): ?>
                                <form action="/admin/media/delete/<?= $item['id'] ?>" method="post" onsubmit="return confirm('Are you sure?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-xs ck-text-red-600 hover:ck-text-red-800">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <div class="ck-mt-6 ck-flex ck-gap-2 ck-justify-center">
                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                    <a href="<?= site_url('admin/media?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>"
                       class="ck-px-3 ck-py-1 ck-rounded ck-border <?= $p === $pagination['current_page'] ? 'ck-bg-blue-600 ck-text-white' : 'ck-bg-white ck-text-gray-700' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No media files yet</p>
    <?php endif; ?>
</div>