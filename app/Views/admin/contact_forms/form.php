<?php $title = $title ?? 'Contact Form'; ?>
<?php
$item = $item ?? null;
$fields = [];
$settings = ['notify_email' => ''];
if ($item) {
    $fields   = is_string($item['fields'] ?? '') ? json_decode($item['fields'], true) : ($item['fields'] ?? []);
    $settings = is_string($item['settings'] ?? '') ? json_decode($item['settings'], true) : ($item['settings'] ?? []);
}
?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-mb-8">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold"><?= $item ? 'Edit Contact Form' : 'New Contact Form' ?></h3>
    </div>

    <form method="post" action="<?= $item ? '/admin/contact-forms/update/' . $item['id'] : '/admin/contact-forms/store' ?>" class="ck-p-6 ck-space-y-6" id="form-builder">
        <?= csrf_field() ?>

        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-6">
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Form Name</label>
                <input type="text" name="name" value="<?= esc($item['name'] ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Slug</label>
                <input type="text" name="slug" value="<?= esc($item['slug'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="auto-generated">
            </div>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Notification Email</label>
            <input type="email" name="settings[notify_email]" value="<?= esc($settings['notify_email'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="admin@example.com">
            <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Leave empty to use admin_email setting.</p>
        </div>

        <div class="ck-flex ck-items-center ck-gap-2">
            <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?> id="is_active" class="ck-w-4 ck-h-4">
            <label for="is_active" class="ck-text-sm ck-text-gray-700">Active</label>
        </div>

        <div>
            <div class="ck-flex ck-justify-between ck-items-center ck-mb-4">
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700">Form Fields</label>
                <button type="button" id="add-field" class="ck-px-3 ck-py-1 ck-bg-green-600 ck-text-white ck-rounded ck-text-sm hover:ck-bg-green-700">+ Add Field</button>
            </div>

            <div id="fields-container" class="ck-space-y-4"></div>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save Form</button>
            <a href="/admin/contact-forms" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>

<script>
    const existingFields = <?= json_encode($fields) ?>;
    let fieldIndex = 0;
    const container = document.getElementById('fields-container');

    function renderField(field = {}, index) {
        const label = field.label || '';
        const name = field.name || '';
        const type = field.type || 'text';
        const required = field.required ? 'checked' : '';
        const options = Array.isArray(field.options) ? field.options.join(', ') : '';
        const div = document.createElement('div');
        div.className = 'field-row ck-p-4 ck-border ck-rounded ck-bg-gray-50';
        div.innerHTML = `
            <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-4 ck-gap-4">
                <div>
                    <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Label</label>
                    <input type="text" name="fields[${index}][label]" value="${label.replace(/"/g, '&quot;')}" required class="ck-w-full ck-px-2 ck-py-1 ck-border ck-rounded">
                </div>
                <div>
                    <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Name</label>
                    <input type="text" name="fields[${index}][name]" value="${name.replace(/"/g, '&quot;')}" required class="ck-w-full ck-px-2 ck-py-1 ck-border ck-rounded">
                </div>
                <div>
                    <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Type</label>
                    <select name="fields[${index}][type]" class="ck-w-full ck-px-2 ck-py-1 ck-border ck-rounded">
                        <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="select" ${type === 'select' ? 'selected' : ''}>Select</option>
                        <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                    </select>
                </div>
                <div class="ck-flex ck-items-end ck-gap-4">
                    <label class="ck-flex ck-items-center ck-gap-1 ck-text-sm">
                        <input type="checkbox" name="fields[${index}][required]" value="1" ${required}> Required
                    </label>
                    <button type="button" class="remove-field ck-text-red-600 ck-text-sm hover:ck-text-red-800">Remove</button>
                </div>
            </div>
            <div class="ck-mt-2">
                <label class="ck-block ck-text-xs ck-font-medium ck-text-gray-700 ck-mb-1">Options (for select/checkbox, comma separated)</label>
                <input type="text" name="fields[${index}][options]" value="${options.replace(/"/g, '&quot;')}" class="ck-w-full ck-px-2 ck-py-1 ck-border ck-rounded" placeholder="option1, option2, option3">
            </div>
        `;
        container.appendChild(div);
    }

    existingFields.forEach((field) => renderField(field, fieldIndex++));

    document.getElementById('add-field').addEventListener('click', () => {
        renderField({}, fieldIndex++);
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-field')) {
            e.target.closest('.field-row').remove();
        }
    });
</script>
