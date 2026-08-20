<?php
$title = $title ?? 'Settings';
$settingsMap = $settingsMap ?? [];
$groups = $groups ?? [];
$mediaSettingKeys = [
    'site_logo',
    'site_footer_logo',
    'site_favicon',
    'site_mascot',
    'default_og_image',
    'publisher_logo',
];
?>
<div class="bp-admin-stack">
    <div class="bp-admin-actionbar">
        <div>
            <h2 class="ck-text-xl ck-font-bold ck-m-0">Site Settings</h2>
            <p class="bp-admin-help">Identity, email, SEO and storage controls for this site.</p>
        </div>
        <?php if ($canUpdate ?? false): ?>
            <button type="button" id="hostinger-preset-btn" class="bp-admin-secondary-button">Hostinger SMTP preset</button>
        <?php endif; ?>
    </div>

    <form method="post" action="/admin/settings/bulk-update" class="bp-admin-settings-tabs">
        <?= csrf_field() ?>

        <?php foreach ($groups as $groupLabel => $fields): ?>
            <fieldset class="bp-admin-fieldset">
                <legend><?= esc($groupLabel) ?></legend>
                <div class="bp-admin-form-grid">
                    <?php foreach ($fields as $field): ?>
                        <?php
                            $key = $field['key'];
                            $value = $settingsMap[$key] ?? '';
                            $inputType = $field['input'] ?? 'text';
                            $isTextarea = ($field['type'] ?? 'string') === 'textarea';
                            $placeholder = $field['placeholder'] ?? '';
                            $isSensitive = $field['sensitive'] ?? false;
                            $isMediaSetting = in_array($key, $mediaSettingKeys, true);
                            $previewUrl = $value !== ''
                                ? (preg_match('#^https?://#i', $value) ? $value : base_url($value))
                                : '';
                        ?>
                        <div class="bp-form-field <?= $isMediaSetting ? 'bp-admin-media-setting' : '' ?>">
                            <label for="setting-<?= esc($key) ?>"><?= esc($field['label']) ?></label>
                            <?php if ($isTextarea): ?>
                                <textarea id="setting-<?= esc($key) ?>" name="settings[<?= esc($key) ?>]" rows="4" <?= ($canUpdate ?? false) ? '' : 'readonly' ?> placeholder="<?= esc($placeholder) ?>"><?= esc($value) ?></textarea>
                            <?php else: ?>
                                <input
                                    id="setting-<?= esc($key) ?>"
                                    type="<?= esc($inputType) ?>"
                                    name="settings[<?= esc($key) ?>]"
                                    value="<?= $isSensitive ? '' : esc($value) ?>"
                                    placeholder="<?= esc($isSensitive && $value !== '' ? 'Leave blank to keep current value' : $placeholder) ?>"
                                    <?= ($canUpdate ?? false) ? '' : 'readonly' ?>
                                >
                            <?php endif; ?>
                            <?php if ($isMediaSetting): ?>
                                <div class="bp-admin-media-tools">
                                    <button type="button" class="bp-admin-secondary-button" data-setting-media-picker="<?= esc($key) ?>">Choose from library</button>
                                    <a class="bp-admin-secondary-button" href="<?= site_url('admin/media/upload') ?>">Upload</a>
                                </div>
                                <div class="bp-admin-media-preview <?= $previewUrl === '' ? 'is-empty' : '' ?>" data-setting-media-preview="<?= esc($key) ?>">
                                    <?php if ($previewUrl !== ''): ?>
                                        <img src="<?= esc($previewUrl) ?>" alt="">
                                    <?php else: ?>
                                        <span>No image selected</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endforeach; ?>

        <?php if ($canUpdate ?? false): ?>
            <div class="bp-admin-actionbar">
                <button type="submit" class="bp-admin-button">Save Settings</button>
            </div>
        <?php endif; ?>
    </form>

    <?php if ($canUpdate ?? false): ?>
        <form method="post" action="/admin/settings/test-email">
            <?= csrf_field() ?>
            <fieldset class="bp-admin-fieldset">
                <legend>SMTP Test</legend>
                <p class="bp-admin-help">Sends a test message using the saved SMTP settings. Leave recipient empty to use your admin email.</p>
                <div class="bp-admin-form-grid">
                    <div class="bp-form-field">
                        <label for="test-recipient">Test recipient</label>
                        <input id="test-recipient" type="email" name="test_recipient" placeholder="you@example.com">
                    </div>
                    <div class="bp-form-field">
                        <label>&nbsp;</label>
                        <button type="submit" class="bp-admin-secondary-button">Send Test Email</button>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php endif; ?>
</div>

<?php if ($canUpdate ?? false): ?>
<script>
document.getElementById('hostinger-preset-btn')?.addEventListener('click', function () {
    var values = {
        'smtp_host': 'smtp.hostinger.com',
        'smtp_port': '465',
        'smtp_crypto': 'ssl',
        'mail_protocol': 'smtp'
    };
    Object.keys(values).forEach(function (key) {
        var field = document.getElementById('setting-' + key);
        if (field) field.value = values[key];
    });
});

document.querySelectorAll('[data-setting-media-picker]').forEach(function (button) {
    button.addEventListener('click', function () {
        if (typeof window.kayaCmsOpenMediaPicker === 'function') {
            window.kayaCmsOpenMediaPicker(button.getAttribute('data-setting-media-picker'));
        }
    });
});

window.kayaCmsMediaSelect = function (media) {
    var target = window.kayaCmsMediaPickerTarget;
    if (!target || !media) return;
    var input = document.getElementById('setting-' + target);
    var preview = document.querySelector('[data-setting-media-preview="' + target + '"]');
    var value = media.path || media.url || '';
    var url = media.url || value;

    if (input) input.value = value;
    if (preview) {
        preview.classList.toggle('is-empty', !url);
        preview.innerHTML = url
            ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">'
            : '<span>No image selected</span>';
    }
};
</script>
<?php endif; ?>
