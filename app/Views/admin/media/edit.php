<?php $title = $title ?? 'Edit Media'; ?>
<?php $item = $item ?? []; $isImage = $item['is_image'] ?? false; ?>

<div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
    <h3 class="ck-text-xl ck-font-bold">Edit Media</h3>
    <a href="/admin/media" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">&larr; Back</a>
</div>

<div class="ck-grid ck-grid-cols-1 lg:ck-grid-cols-3 ck-gap-6">
    <!-- Preview -->
    <div class="ck-bg-white ck-p-4 ck-rounded-lg ck-shadow lg:ck-col-span-2">
        <div class="ck-border ck-rounded ck-bg-gray-100 ck-p-4 ck-overflow-auto">
            <?php if ($isImage): ?>
                <img id="preview-image" src="<?= esc($item['url']) ?>" class="ck-max-w-full" alt="<?= esc($item['alt_text'] ?? '') ?>">
            <?php else: ?>
                <p class="ck-text-center ck-text-gray-500 ck-py-16"><?= esc($item['mime_type'] ?? '') ?></p>
            <?php endif; ?>
        </div>
        <p class="ck-text-xs ck-text-gray-500 ck-mt-2">
            <?= esc($item['original_name'] ?? $item['filename']) ?>
            <?php if (!empty($item['width']) && !empty($item['height'])): ?>
                &middot; <?= (int) $item['width'] ?> x <?= (int) $item['height'] ?> px
            <?php endif; ?>
            &middot; <?= esc($item['formatted_size'] ?? '') ?>
        </p>
    </div>

    <!-- Properties -->
    <div class="ck-space-y-6">
        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
            <h4 class="ck-font-bold ck-mb-4">Details</h4>

            <form method="post" action="/admin/media/update/<?= $item['id'] ?>" class="ck-space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="ck-block ck-text-sm ck-text-gray-600 ck-mb-1">Alt Text</label>
                    <input type="text" name="alt_text" value="<?= esc($item['alt_text'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <div>
                    <label class="ck-block ck-text-sm ck-text-gray-600 ck-mb-1">Folder</label>
                    <select name="folder_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                        <option value="">Root</option>
                        <?php foreach (($folders ?? []) as $folder): ?>
                            <option value="<?= $folder['id'] ?>" <?= ($item['folder_id'] ?? null) === $folder['id'] ? 'selected' : '' ?>><?= $folder['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            </form>
        </div>

        <?php if ($isImage): ?>
        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
            <h4 class="ck-font-bold ck-mb-4">Resize</h4>
            <form method="post" action="/admin/media/resize/<?= $item['id'] ?>" class="ck-grid ck-grid-cols-2 ck-gap-3">
                <?= csrf_field() ?>
                <div>
                    <label class="ck-block ck-text-xs ck-text-gray-600 ck-mb-1">Max Width</label>
                    <input type="number" name="width" min="1" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <div>
                    <label class="ck-block ck-text-xs ck-text-gray-600 ck-mb-1">Max Height</label>
                    <input type="number" name="height" min="1" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <button type="submit" class="ck-col-span-2 ck-px-4 ck-py-2 ck-bg-gray-500 ck-text-white ck-rounded">Resize</button>
            </form>
        </div>

        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
            <h4 class="ck-medium ck-mb-4">Rotate</h4>
            <form method="post" action="/admin/media/rotate/<?= $item['id'] ?>" class="ck-space-y-3">
                <?= csrf_field() ?>
                <select name="degrees" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="90">90&deg; clockwise</option>
                    <option value="180">180&deg;</option>
                    <option value="270">270&deg; clockwise</option>
                </select>
                <button type="submit" class="ck-w-full ck-px-4 ck-py-2 ck-bg-gray-500 ck-text-white ck-rounded">Rotate</button>
            </form>
        </div>

        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
            <h4 class="ck-medium ck-mb-4">Crop</h4>
            <button type="button" id="enable-crop" class="ck-w-full ck-px-4 ck-py-2 ck-bg-indigo-600 ck-text-white ck-rounded">Select crop area</button>
            <form id="crop-form" method="post" action="/admin/media/crop/<?= $item['id'] ?>" class="ck-hidden ck-space-y-3 ck-mt-3">
                <?= csrf_field() ?>
                <input type="hidden" name="x" id="crop-x">
                <input type="hidden" name="y" id="crop-y">
                <input type="hidden" name="width" id="crop-width">
                <input type="hidden" name="height" id="crop-height">
                <button type="submit" class="ck-w-full ck-px-4 ck-py-2 ck-bg-indigo-600 ck-text-white ck-rounded">Apply crop</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    (() => {
        const img = document.getElementById('preview-image');
        if (!img) return;

        let enabled = false;
        let startX = 0, startY = 0, rect = null, overlay = null;

        document.getElementById('enable-crop').addEventListener('click', () => {
            enabled = !enabled;
            document.getElementById('enable-crop').textContent = enabled ? 'Cancel crop' : 'Select crop area';
            document.getElementById('crop-form').classList.toggle('ck-hidden', !enabled);

            if (enabled && !overlay) {
                overlay = document.createElement('div');
                overlay.id = 'crop-overlay';
                overlay.style.cssText = 'position:absolute;border:2px dashed #4f46e5;background:rgba(0,0,0,0.25);display:none;pointer-events:none;z-index:50;';
                document.querySelector('.ck-bg-gray-100').style.position = 'relative';
                document.querySelector('.ck-bg-gray-100').appendChild(overlay);
            } else if (!enabled && overlay) {
                overlay.style.display = 'none';
            }
        });

        const previewBox = document.querySelector('.ck-bg-gray-100');

        previewBox.addEventListener('mousedown', (e) => {
            if (!enabled) return;
            const bounds = previewBox.getBoundingClientRect();
            startX = e.clientX - bounds.left;
            startY = e.clientY - bounds.top;
            crop = { x: startX, y: startY, w: 0, h: 0 };
        });

        previewBox.addEventListener('mousemove', (e) => {
            if (!crop) return;
            const bounds = previewBox.getBoundingClientRect();
            const curX = e.clientX - bounds.left;
            const curY = e.clientY - bounds.top;
            crop.w = curX - crop.x;
            crop.h = curY - crop.y;
            overlay.style.display = 'block';
            overlay.style.left = Math.min(crop.x, curX) + 'px';
            overlay.style.top = Math.min(crop.y, curY) + 'px';
            overlay.style.width = Math.abs(crop.w) + 'px';
            overlay.style.height = Math.abs(crop.h) + 'px';
        });

        window.addEventListener('mouseup', () => {
            if (!crop) return;
            if (Math.abs(crop.w) > 10 && Math.abs(crop.h) > 10) {
                const scaleX = img.naturalWidth / img.getBoundingClientRect().width;
                const scaleY = img.naturalHeight / img.getBoundingClientRect().height;
                document.getElementById('crop-x').value = Math.round(Math.min(crop.x, crop.x + crop.w) * scaleX);
                document.getElementById('crop-y').value = Math.round(Math.min(crop.y, crop.y + crop.h) * scaleY);
                document.getElementById('crop-width').value = Math.round(Math.abs(crop.w) * scaleX);
                document.getElementById('crop-height').value = Math.round(Math.abs(crop.h) * scaleY);
            }
            crop = null;
        });
    })();
</script>