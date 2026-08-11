<?php $title = $title ?? 'Edit Custom Fields';
$fieldTypes = $fieldTypes ?? [
    'text', 'textarea', 'number', 'email', 'url', 'select', 'checkbox', 'date', 'datetime', 'toggle'
];
?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <div>
            <h3 class="ck-text-xl ck-font-bold"><?= esc($title) ?>: <code><?= esc($contentType) ?></code></h3>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-1">Fields appear in the content form for this type. Custom field values persist in <code>custom_data</code>.</p>
        </div>
        <a href="/admin/content/schemas" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Back</a>
    </div>

    <form method="post" action="/admin/content/schemas/store/<?= esc($contentType) ?>" class="ck-p-6">
        <?= csrf_field() ?>
        <div id="fields-list" class="ck-space-y-4">
            <?php foreach ($schema as $index => $field): ?>
                <div class="field-row ck-border ck-border-gray-300 ck-rounded-lg ck-p-4">
                    <div class="ck-flex ck-gap-3 ck-mb-3">
                        <div class="ck-flex-1">
                            <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Name (snake_case)</label>
                            <input type="text" name="fields[<?= $index ?>][name]" value="<?= esc($field['name'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="e.g. author_name">
                        </div>
                        <div class="ck-flex-1">
                            <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Label</label>
                            <input type="text" name="fields[<?= $index ?>][label]" value="<?= esc($field['label'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="e.g. Author Name">
                        </div>
                        <div class="ck-w-40">
                            <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Type</label>
                            <select name="fields[<?= $index ?>][type]" class="field-type ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                                <?php foreach ($fieldTypes as $ft): ?>
                                    <option value="<?= $ft ?>" <?= ($field['type'] ?? 'text') === $ft ? 'selected' : '' ?>><?= ucfirst($ft) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ck-flex ck-items-end ck-pb-1">
                            <button type="button" class="remove-field ck-px-3 ck-py-2 ck-bg-red-100 ck-text-red-700 ck-rounded hover:ck-bg-red-200">Remove</button>
                        </div>
                    </div>
                    <div class="ck-flex ck-gap-3 ck-items-center">
                        <div class="ck-w-48">
                            <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Default</label>
                            <input type="text" name="fields[<?= $index ?>][default]" value="<?= esc(is_scalar($field['default'] ?? null) ? (string) $field['default'] : '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                        </div>
                        <label class="ck-inline-flex ck-items-center ck-gap-2 ck-text-sm ck-text-gray-700 ck-mt-5">
                            <input type="checkbox" name="fields[<?= $index ?>][required]" value="1" <?= !empty($field['required']) ? 'checked' : '' ?> class="ck-w-4 ck-h-4"> Required
                        </label>
                        <div class="options-wrap ck-flex-1" <?= ($field['type'] ?? '') === 'select' ? '' : 'style="display:none"' ?>>
                            <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Options (one per line)</label>
                            <textarea name="fields[<?= $index ?>][options]" rows="2" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="red&#10;blue&#10;green"><?= esc(implode("\n", array_map('strval', (array) ($field['options'] ?? [])))) ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <template id="field-row-template">
            <div class="field-row ck-border ck-border-gray-300 ck-rounded-lg ck-p-4">
                <div class="ck-flex ck-gap-3 ck-mb-3">
                    <div class="ck-flex-1">
                        <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Name (snake_case)</label>
                        <input type="text" name="fields[__INDEX__][name]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="e.g. author_name">
                    </div>
                    <div class="ck-flex-1">
                        <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Label</label>
                        <input type="text" name="fields[__INDEX__][label]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="e.g. Author Name">
                    </div>
                    <div class="ck-w-40">
                        <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Type</label>
                        <select name="fields[__INDEX__][type]" class="field-type ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                            <?php foreach ($fieldTypes as $ft): ?>
                                <option value="<?= $ft ?>"><?= ucfirst($ft) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ck-flex ck-items-end ck-pb-1">
                        <button type="button" class="remove-field ck-px-3 ck-py-2 ck-bg-red-100 ck-text-red-700 ck-rounded hover:ck-bg-red-200">Remove</button>
                    </div>
                </div>
                <div class="ck-flex ck-gap-3 ck-items-center">
                    <div class="ck-w-48">
                        <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Default</label>
                        <input type="text" name="fields[__INDEX__][default]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    </div>
                    <label class="ck-inline-flex ck-items-center ck-gap-2 ck-text-sm ck-text-gray-700 ck-mt-5">
                        <input type="checkbox" name="fields[__INDEX__][required]" value="1" class="ck-w-4 ck-h-4"> Required
                    </label>
                    <div class="options-wrap ck-flex-1" style="display:none">
                        <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-500 ck-mb-1">Options (one per line)</label>
                        <textarea name="fields[__INDEX__][options]" rows="2" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="red&#10;blue&#10;green"></textarea>
                    </div>
                </div>
            </div>
        </template>

        <div class="ck-flex ck-gap-3 ck-mt-4">
            <button type="button" id="add-field" class="ck-px-4 ck-py-2 ck-bg-gray-200 ck-text-gray-700 ck-rounded hover:ck-bg-gray-300">+ Add Field</button>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            <a href="/admin/content/schemas" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
<script>
    (function () {
        const list = document.getElementById('fields-list');
        const template = document.getElementById('field-row-template');
        let index = list.querySelectorAll('.field-row').length;

        document.getElementById('add-field').addEventListener('click', function () {
            const node = template.content.firstElementChild.cloneNode(true);
            node.innerHTML = node.innerHTML.replace(/__INDEX__/g, index++);
            list.appendChild(node);
            wireRow(node);
        });

        function wireRow(row) {
            row.querySelector('.remove-field').addEventListener('click', function () {
                row.remove();
            });
            const type = row.querySelector('.field-type');
            const optionsWrap = row.querySelector('.options-wrap');
            type.addEventListener('change', function () {
                optionsWrap.style.display = type.value === 'select' ? '' : 'none';
            });
        }

        list.querySelectorAll('.field-row').forEach(wireRow);
    })();
</script>