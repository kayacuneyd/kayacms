<?php $title = $title ?? 'Media Picker'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-4">
        <h3 class="ck-text-xl ck-font-bold">Insert Media</h3>
        <button type="button" class="ck-px-4 ck-py-2 ck-bg-gray-500 ck-text-white ck-rounded" onclick="window.close()">Close</button>
    </div>

    <form method="get" onsubmit="event.preventDefault(); pickerLoad(1);" class="ck-flex ck-gap-3 ck-mb-4">
        <input type="text" id="picker-search" value="<?= esc($search ?? '') ?>" placeholder="Search media..." class="ck-flex-1 ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        <select id="picker-folder" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <option value="">All folders</option>
            <?php foreach (($folders ?? []) as $folder): ?>
                <option value="<?= $folder['id'] ?>"><?= $folder['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-white rounded hover:ck-bg-blue-700" onclick="pickerLoad(1)">Search</button>
    </form>

    <div id="picker-grid" class="ck-grid ck-grid-cols-2 md:ck-grid-cols-3 lg:ck-grid-cols-4 ck-gap-4">
        <?php foreach (($items ?? []) as $item): ?>
            <div class="ck-border ck-rounded-lg ck-overflow-hidden ck-cursor-pointer ck-hover:border-blue-400" onclick="picker('<?= ($item['is_image'] ?? false) ? 'image' : 'file' ?>', <?= esc(json_encode($item['url'])) ?>, <?= esc(json_encode($item['alt_text'] ?? '')) ?>)">
                <div class="ck-aspect-square ck-bg-gray-100 ck-flex ck-items-center ck-justify-center">
                    <?php if ($item['is_image'] ?? false): ?>
                        <img src="<?= esc($item['thumbnail_path'] ? '/' . $item['thumbnail_path'] : $item['url']) ?>" class="ck-w-full ck-h-full ck-object-cover">
                    <?php else: ?>
                        <span class="ck-text-gray-400 ck-text-xs"><?= esc($item['mime_type'] ?? '') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div id="picker-pagination" class="ck-mt-4 ck-flex ck-gap-2 ck-justify-center">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <button type="button" class="ck-px-3 ck-py-1 ck-rounded ck-border" onclick="pickerLoad(<?= $p ?>)"><?= $p ?></button>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function picker(type, url, alt) {
        if (window.opener && typeof window.opener.kayaCmsMediaSelect === 'function') {
            window.opener.kayaCmsMediaSelect({ url: url, alt: alt || '', type: type });
        }
        window.close();
    }
</script>