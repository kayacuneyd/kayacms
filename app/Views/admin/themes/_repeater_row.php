<?php
$row = $row ?? [];
$ri  = $ri ?? 0;
?>
<div data-row class="ck-border ck-rounded ck-p-3 ck-mb-2 ck-bg-gray-50">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-2">
        <span class="ck-text-xs ck-text-gray-500">Row <?= $ri + 1 ?></span>
        <button type="button" data-remove-row class="ck-px-2 ck-py-1 ck-text-xs ck-bg-red-600 ck-text-white ck-rounded">Remove</button>
    </div>

    <?php foreach ($field['fields'] as $sub): ?>
        <?php $value = $row[$sub['name']] ?? ''; ?>
        <div class="ck-mb-2">
            <label class="ck-block ck-mb-1 ck-text-xs ck-font-medium"><?= esc($sub['label']) ?></label>

            <?php if ($sub['type'] === 'select'): ?>
                <select name="config[<?= esc($field['key']) ?>][<?= $ri ?>][<?= esc($sub['name']) ?>]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                    <?php foreach ($sub['options'] as $opt): ?>
                        <option value="<?= esc($opt) ?>" <?= (string) $value === (string) $opt ? 'selected' : '' ?>><?= esc($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($sub['type'] === 'textarea'): ?>
                <textarea name="config[<?= esc($field['key']) ?>][<?= $ri ?>][<?= esc($sub['name']) ?>]" rows="3" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded"><?= esc($value) ?></textarea>
            <?php elseif ($sub['type'] === 'checkbox' || $sub['type'] === 'toggle'): ?>
                <select name="config[<?= esc($field['key']) ?>][<?= $ri ?>][<?= esc($sub['name']) ?>]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                    <option value="1" <?= $value === '1' || $value === 1 ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= $value !== '1' && $value !== 1 ? 'selected' : '' ?>>Disabled</option>
                </select>
            <?php else: ?>
                <input type="text" name="config[<?= esc($field['key']) ?>][<?= $ri ?>][<?= esc($sub['name']) ?>]" value="<?= esc($value) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
