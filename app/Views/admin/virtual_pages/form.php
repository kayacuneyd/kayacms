<?php
$title = $title ?? (empty($item) ? 'New Virtual Page' : 'Edit Virtual Page');
$item = $item ?? null;
$slug = old('slug', $item['slug'] ?? '');
$titleVal = old('title', $item['title'] ?? '');
$handler = old('handler', $item['handler'] ?? 'template');
$status = old('status', $item['status'] ?? 'active');
$payload = (empty($item['payload'])) ? [] : (json_decode($item['payload'], true) ?: []);
?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold"><?= $title ?></h3>
        <a href="/admin/virtual-pages" class="ck-px-4 ck-py-2 ck-border ck-rounded ck-text-gray-600">← Back</a>
    </div>

    <form method="post" action="/admin/virtual-pages/<?= $item ? 'update/' . $item['id'] : 'store' ?>">
        <?= csrf_field() ?>

        <div class="ck-grid ck-grid-cols-2 ck-gap-4 ck-mb-4">
            <div>
                <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Slug</label>
                <input type="text" name="slug" value="<?= esc($slug) ?>" required placeholder="features" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                <small class="ck-text-gray-500">Served at /features (letters, numbers, dashes, underscores).</small>
            </div>
            <div>
                <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Status</label>
                <select name="status" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="ck-mb-4">
            <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Title</label>
            <input type="text" name="title" value="<?= esc($titleVal) ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
        </div>

        <div class="ck-mb-4">
            <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Handler</label>
            <select name="handler" id="vp-handler" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                <option value="template" <?= $handler === 'template' ? 'selected' : '' ?>>Template (theme view)</option>
                <option value="markdown" <?= $handler === 'markdown' ? 'selected' : '' ?>>Markdown (body)</option>
                <option value="redirect" <?= $handler === 'redirect' ? 'selected' : '' ?>>Redirect</option>
            </select>
        </div>

        <div class="ck-mb-4" id="vp-tmpl" style="<?= $handler === 'template' ? '' : 'display:none' ?>">
            <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Theme View</label>
            <input type="text" name="payload_view" value="<?= esc(htmlspecialchars((string) ($payload['view'] ?? ''), ENT_QUOTES)) ?>" placeholder="themes/default/page (optional sub-view)" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
            <small class="ck-text-gray-500">Leave blank to use the default virtual page layout.</small>
        </div>

        <div class="ck-mb-4" id="vp-md" style="<?= $handler === 'markdown' ? '' : 'display:none' ?>">
            <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Markdown Body</label>
            <textarea name="payload_body" rows="10" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded"><?= esc((string) ($payload['body'] ?? '')) ?></textarea>
            <small class="ck-text-gray-500">Supports headings, paragraphs, bold/italic, lists, code fences and links.</small>
        </div>

        <div class="ck-mb-4" id="vp-red" style="<?= $handler === 'redirect' ? '' : 'display:none' ?>">
            <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium">Redirect URL</label>
            <input type="text" name="payload_url" value="<?= esc(htmlspecialchars((string) ($payload['url'] ?? ''), ENT_QUOTES)) ?>" placeholder="/page/about" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
        </div>

        <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">
            <?= $item ? 'Update' : 'Create' ?> Virtual Page
        </button>
    </form>
</div>

<script>
(function () {
    var select = document.getElementById('vp-handler');
    function toggle() {
        var v = select.value;
        document.getElementById('vp-tmpl').style.display = v === 'template' ? '' : 'none';
        document.getElementById('vp-md').style.display   = v === 'markdown' ? '' : 'none';
        document.getElementById('vp-red').style.display  = v === 'redirect' ? '' : 'none';
    }
    select.addEventListener('change', toggle);
})();
</script>