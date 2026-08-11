<?php $title = $title ?? 'Settings'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <h3 class="ck-text-xl ck-font-bold ck-mb-6">Site Settings</h3>

    <form method="post" action="/admin/settings/bulk-update" class="ck-space-y-6">
        <?= csrf_field() ?>

        <?php foreach ($items as $item): ?>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2"><?= esc(ucwords(str_replace('_', ' ', $item['key']))) ?></label>
                <input
                    type="text"
                    name="settings[<?= esc($item['key']) ?>]"
                    value="<?= esc($item['value']) ?>"
                    class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md"
                    <?= ($canUpdate ?? false) ? '' : 'readonly' ?>
                >
            </div>
        <?php endforeach; ?>

        <?php if ($canUpdate ?? false): ?>
            <div class="ck-flex ck-gap-3">
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save Settings</button>
                <button type="button" id="test-email-btn" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-text-gray-700 ck-rounded hover:ck-bg-gray-50">Send Test Email</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php if ($canUpdate ?? false): ?>
<form id="test-email-form" method="post" action="/admin/settings/test-email" class="ck-hidden">
    <?= csrf_field() ?>
    <button type="submit"></button>
</form>
<script>
    document.getElementById('test-email-btn').addEventListener('click', () => {
        document.getElementById('test-email-form').submit();
    });
</script>
<?php endif; ?>
