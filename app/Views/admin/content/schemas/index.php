<?php $title = $title ?? 'Custom Fields'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold">Custom Fields</h3>
        <p class="ck-text-sm ck-text-gray-500 ck-mt-1">
            Define per-content-type field schemas. Fields added here render in the content form and are stored as JSON in <code>content.custom_data</code>.
        </p>
    </div>

    <table class="ck-w-full">
        <thead class="ck-bg-gray-50">
            <tr>
                <th class="ck-px-6 ck-py-3 ck-text-left">Content Type</th>
                <th class="ck-px-6 ck-py-3 ck-text-left">Fields</th>
                <th class="ck-px-6 ck-py-3 ck-text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($schemas)): ?>
                <tr>
                    <td colspan="3" class="ck-px-6 ck-py-8 ck-text-center ck-text-gray-500">No custom fields configured yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($schemas as $schema): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-3 ck-font-medium"><?= esc($schema['content_type']) ?></td>
                        <td class="ck-px-6 ck-py-3"><?= count($schema['fields']) ?> field(s)</td>
                        <td class="ck-px-6 ck-py-3 ck-text-right ck-space-x-2">
                            <a href="/admin/content/schemas/edit/<?= esc($schema['content_type']) ?>" class="ck-px-3 ck-py-1 ck-bg-gray-200 ck-text-gray-700 ck-rounded hover:ck-bg-gray-300">Edit</a>
                            <form method="post" action="/admin/content/schemas/delete/<?= esc($schema['content_type']) ?>" class="ck-inline" onsubmit="return confirm('Remove custom fields for this content type?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-px-3 ck-py-1 ck-bg-red-100 ck-text-red-700 ck-rounded hover:ck-bg-red-200">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-mt-6 ck-p-6">
    <h4 class="ck-font-semibold ck-mb-2">Configure a content type</h4>
    <div class="ck-flex ck-gap-2">
        <select id="new-type" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <?php foreach ($types ?? ['article', 'page'] as $type): ?>
                <option value="<?= esc($type) ?>"><?= esc($type) ?></option>
            <?php endforeach; ?>
        </select>
        <a href="#" id="new-type-link" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">+ Configure</a>
    </div>
</div>
<script>
    const typeSelect = document.getElementById('new-type');
    const typeLink = document.getElementById('new-type-link');
    function syncTypeLink() {
        typeLink.href = '/admin/content/schemas/edit/' + encodeURIComponent(typeSelect.value);
    }
    typeSelect.addEventListener('change', syncTypeLink);
    syncTypeLink();
</script>