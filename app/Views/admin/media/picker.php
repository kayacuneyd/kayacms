<?php
$title = $title ?? 'Media Picker';
$isModal = $isModal ?? false;
$currentFolder = $currentFolder ?? null;
?>
<div class="bp-media-picker-panel" data-media-picker-panel>
    <div class="bp-media-picker-head">
        <div>
            <h3 class="ck-text-xl ck-font-bold ck-m-0">Media Library</h3>
            <p class="bp-admin-help">Choose an existing file or upload a new one without leaving the editor.</p>
        </div>
        <?php if ($isModal): ?>
            <button type="button" class="bp-admin-icon-button" aria-label="Close media library" data-media-picker-close><i data-lucide="x" aria-hidden="true"></i></button>
        <?php endif; ?>
    </div>

    <?php if ($canUpload ?? false): ?>
        <form method="post" action="<?= site_url('admin/media/store') ?>" enctype="multipart/form-data" class="bp-media-picker-upload" data-media-upload-form>
            <?= csrf_field() ?>
            <div class="bp-media-picker-drop" data-media-upload-drop>
                <input type="file" name="files[]" multiple data-media-upload-input>
                <div>
                    <strong>Upload media</strong>
                    <span>Drag files here or click to browse.</span>
                </div>
            </div>
            <div class="bp-media-picker-upload-meta">
                <input type="text" name="alt_text" placeholder="Alt text (optional)" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <select name="folder_id" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">Root</option>
                    <?php foreach (($folders ?? []) as $folder): ?>
                        <option value="<?= $folder['id'] ?>"><?= esc($folder['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bp-admin-button">Upload</button>
            </div>
            <div class="bp-media-picker-upload-status" data-media-upload-status></div>
        </form>
    <?php endif; ?>

    <form method="get" action="<?= site_url('admin/media/picker') ?>" class="bp-media-picker-filter" data-media-picker-filter>
        <input type="hidden" name="modal" value="<?= $isModal ? '1' : '0' ?>">
        <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Search media..." class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        <select name="folder" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <option value="">All folders</option>
            <?php foreach (($folders ?? []) as $folder): ?>
                <option value="<?= $folder['id'] ?>" <?= (string) ($currentFolder ?? '') === (string) $folder['id'] ? 'selected' : '' ?>><?= esc($folder['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bp-admin-secondary-button">Search</button>
    </form>

    <?php if (! empty($items)): ?>
        <div class="bp-media-picker-grid" data-media-picker-grid>
            <?php foreach (($items ?? []) as $item): ?>
                <?php
                    $pickerPath = $item['display_path'] ?? $item['path'] ?? $item['url'];
                    $isImage = $item['is_image'] ?? false;
                    $thumb = ! empty($item['thumbnail_path']) ? base_url($item['thumbnail_path']) : ($item['url'] ?? '');
                ?>
                <button
                    type="button"
                    class="bp-media-picker-item"
                    data-media-select
                    data-media-type="<?= $isImage ? 'image' : 'file' ?>"
                    data-media-url="<?= esc($item['url'] ?? '') ?>"
                    data-media-path="<?= esc($pickerPath) ?>"
                    data-media-alt="<?= esc($item['alt_text'] ?? '') ?>"
                >
                    <span class="bp-media-picker-thumb">
                        <?php if ($isImage): ?>
                            <img src="<?= esc($thumb) ?>" alt="<?= esc($item['alt_text'] ?? '') ?>">
                        <?php else: ?>
                            <span class="bp-admin-file-thumb">
                                <i data-lucide="<?= str_contains((string) ($item['mime_type'] ?? ''), 'audio') ? 'file-audio' : (str_contains((string) ($item['mime_type'] ?? ''), 'pdf') ? 'file-text' : 'file') ?>" aria-hidden="true"></i>
                                <strong><?= esc(strtoupper(pathinfo((string) ($item['filename'] ?? ''), PATHINFO_EXTENSION) ?: 'FILE')) ?></strong>
                                <small><?= esc($item['mime_type'] ?? '') ?></small>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="bp-media-picker-name"><?= esc($item['original_name'] ?? $item['filename'] ?? 'Media') ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="bp-empty-state">No media found. Upload a file above to add one.</p>
    <?php endif; ?>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="bp-media-picker-pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="<?= site_url('admin/media/picker?' . http_build_query(['modal' => $isModal ? '1' : '0', 'search' => $search ?? '', 'folder' => $currentFolder ?? '', 'page' => $p])) ?>" data-media-picker-page class="<?= $p === ($page ?? 1) ? 'is-active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
