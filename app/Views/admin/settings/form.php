<?php $title = $title ?? 'Settings'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold">Edit Setting</h3>
    </div>

    <form method="post" action="/admin/settings/update/<?= esc($item['key'] ?? '') ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>
        

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Value</label>
            <?php if (($item['type'] ?? '') === 'textarea'): ?>
                <textarea name="value" rows="8" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-font-mono"><?= esc($item['value'] ?? '') ?></textarea>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Raw HTML/JS is inserted verbatim into the theme layout before <code></head></code> (header scripts) or before <code></body></code> (footer scripts). Do not include the surrounding tags yourself.</p>
            <?php else: ?>
                <input type="text" name="value" value="<?= esc($item['value'] ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <?php endif; ?>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            <a href="/admin/settings" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
