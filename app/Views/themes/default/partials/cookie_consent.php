<?php
$consentEnabled = ! empty($settings['cookie_consent_enabled'] ?? true);
$policyUrl      = trim((string) ($settings['privacy_policy_url'] ?? ''));
?>
<?php if ($consentEnabled): ?>
<style>
    #ck-consent {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        background: #1f2937;
        color: #f9fafb;
        padding: 16px 20px;
        z-index: 999;
        box-shadow: 0 -6px 20px rgba(0, 0, 0, .25);
        font-size: .95rem;
    }
    #ck-consent .wrap { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    #ck-consent p { margin: 0; flex: 1 1 420px; }
    #ck-consent .ck-c-actions { display: flex; gap: 8px; }
    #ck-consent button { border: 0; border-radius: 6px; padding: 8px 16px; cursor: pointer; font-weight: 600; }
    #ck-accept { background: #2563eb; color: #fff; }
    #ck-decline { background: #374151; color: #e5e7eb; }
</style>
<div id="ck-consent" role="dialog" aria-label="Cookie consent" hidden>
    <div class="wrap">
        <p>
            We use cookies to provide and improve our services.
            <?php if ($policyUrl !== ''): ?>
                <a href="<?= esc($policyUrl) ?>" style="color:#93c5fd" target="_blank" rel="noopener">Privacy Policy</a>
            <?php endif; ?>
        </p>
        <div class="ck-c-actions">
            <button type="button" id="ck-decline">Decline</button>
            <button type="button" id="ck-accept">Accept</button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = 'ck_consent';
    if (document.cookie.indexOf(KEY + '=') !== -1) return;

    var banner = document.getElementById('ck-consent');
    if (!banner) return;
    banner.hidden = false;

    function persist(value) {
        var d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = KEY + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
        banner.hidden = true;
    }

    document.getElementById('ck-accept').addEventListener('click', function () { persist('accepted'); });
    document.getElementById('ck-decline').addEventListener('click', function () { persist('declined'); });
})();
</script>
<?php endif; ?>