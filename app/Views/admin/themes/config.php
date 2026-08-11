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

                    <?php if ($field['type'] === 'toggle'): ?>
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