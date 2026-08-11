<?php $title = $title ?? 'Theme Configuration'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Configure <?= esc($item['name'] ?? '') ?> (<?= esc($item['slug'] ?? '') ?>)</h3>
        <a href="/admin/themes" class="ck-px-4 ck-py-2 ck-border ck-rounded ck-text-gray-600">← Back</a>
    </div>

    <?php if (! $hasSchema): ?>
        <p class="ck-text-gray-500 ck-py-8 ck-text-center">
            This theme does not declare a configuration schema.
            Add an optional <code>config.php</code> in
            <code>app/Views/themes/<?= esc($item['slug'] ?? '') ?>/</code> returning
            field definitions (key, label, type, default) to make it configurable.
        </p>
    <?php else: ?>
        <form method="post" action="/admin/themes/config/<?= $item['id'] ?>">
            <?= csrf_field() ?>

            <?php foreach ($fields as $field): ?>
                <?php $current = $values[$field['key']] ?? $field['default']; ?>
                <div class="ck-mb-4">
                    <label class="ck-block ck-mb-1 ck-text-sm ck-font-medium"><?= esc($field['label']) ?></label>

                    <?php if ($field['type'] === 'repeater'): ?>
                        <?php $repeater = is_array($current) ? $current : []; ?>
                        <div class="ck-border ck-rounded ck-p-4"
                             data-repeater
                             data-key="config[<?= esc($field['key']) ?>]"
                             data-fields='<?= esc(json_encode(array_map(static fn ($f) => ['name' => $f['name'], 'label' => $f['label'], 'type' => $f['type'], 'options' => $f['options']], $field['fields']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
                            <div data-rows>
                                <?php foreach ($repeater as $ri => $row): ?>
                                    <?= view('admin/themes/_repeater_row', ['field' => $field, 'row' => $row, 'ri' => $ri]) ?>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" data-add-row class="ck-mt-2 ck-px-3 ck-py-1 ck-bg-gray-600 ck-text-white ck-rounded">+ Add Row</button>
                        </div>
                    <?php elseif ($field['type'] === 'toggle'): ?>
                        <select name="config[<?= esc($field['key']) ?>]" class="ck-px-3 ck-py-2 ck-border ck-rounded">
                            <option value="1" <?= $current === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= $current !== '1' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    <?php elseif ($field['type'] === 'textarea'): ?>
                        <textarea name="config[<?= esc($field['key']) ?>]" rows="4" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded"><?= esc($current) ?></textarea>
                    <?php elseif ($field['type'] === 'select'): ?>
                        <select name="config[<?= esc($field['key']) ?>]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                            <?php foreach ($field['options'] as $opt): ?>
                                <option value="<?= esc($opt) ?>" <?= $current === $opt ? 'selected' : '' ?>><?= esc($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="config[<?= esc($field['key']) ?>]" value="<?= esc($current) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save Configuration</button>
        </form>
    <?php endif; ?>
</div>

<?php if (($fields ?? []) !== []): ?>
<script>
(function () {
    var templateCache = {};

    document.querySelectorAll('[data-repeater]').forEach(function (repeater) {
        var key   = repeater.getAttribute('data-key');
        var rows  = repeater.querySelector('[data-rows]');
        var proto = JSON.parse(repeater.getAttribute('data-fields') || '[]');
        templateCache[key] = proto;

        var addBtn = repeater.querySelector('[data-add-row]');

        function rowHtml(fields, index) {
            var base = key + '[' + index + ']';
            var html = '';
            fields.forEach(function (sub) {
                var name = base + '[' + sub.name + ']';
                html += '<div class="ck-mb-2">';
                html += '<label class="ck-block ck-mb-1 ck-text-xs ck-font-medium">' + escapeHtml(sub.label) + '</label>';
                if (sub.type === 'select') {
                    html += '<select name="' + name + '" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">';
                    (sub.options || []).forEach(function (opt) {
                        html += '<option value="' + escapeHtml(opt) + '">' + escapeHtml(opt) + '</option>';
                    });
                    html += '</select>';
                } else if (sub.type === 'textarea') {
                    html += '<textarea name="' + name + '" rows="3" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded"></textarea>';
                } else if (sub.type === 'checkbox' || sub.type === 'toggle') {
                    html += '<select name="' + name + '" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">';
                    html += '<option value="1">Enabled</option><option value="0">Disabled</option>';
                    html += '</select>';
                } else {
                    html += '<input type="text" name="' + name + '" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">';
                }
                html += '</div>';
            });
            return html;
        }

        function renumber() {
            rows.querySelectorAll('[data-row]').forEach(function (row, i) {
                row.querySelectorAll('[name]').forEach(function (input) {
                    input.name = input.name.replace(/\[\d+\]/, '[' + i + ']');
                });
                var label = row.querySelector('span.ck-text-xs');
                if (label) {
                    label.textContent = 'Row ' + (i + 1);
                }
            });
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var index = rows.querySelectorAll('[data-row]').length;
                var wrapper = document.createElement('div');
                wrapper.setAttribute('data-row', '');
                wrapper.className = 'ck-border ck-rounded ck-p-3 ck-mb-2 ck-bg-gray-50';
                wrapper.innerHTML = rowHtml(proto, index) +
                    '<button type="button" data-remove-row class="ck-px-2 ck-py-1 ck-text-xs ck-bg-red-600 ck-text-white ck-rounded">Remove</button>';
                rows.appendChild(wrapper);
            });
        }

        rows.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-row]');
            if (btn) {
                btn.closest('[data-row]').remove();
                renumber();
            }
        });
    });

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
})();
</script>
<?php endif; ?>