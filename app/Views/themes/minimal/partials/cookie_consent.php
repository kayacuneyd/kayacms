<?php
$consentEnabled = ! empty($settings['cookie_consent_enabled'] ?? true);
$policyUrl      = trim((string) ($settings['privacy_policy_url'] ?? ''));
?>
<?php if ($consentEnabled): ?>
<style>
    #mk-consent {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        background: #111;
        color: #f5f5f5;
        padding: 16px 20px;
        z-index: 999;
        font-family: Georgia, "Times New Roman", serif;
        font-size: .9rem;
    }
    #mk-consent .container { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    #mk-consent p { margin: 0; flex: 1 1 400px; }
    #mk-consent button { border: 1px solid #f5f5f5; background: transparent; color: #f5f5f5; padding: 8px 16px; cursor: pointer; }
</style>
<div id="mk-consent" role="dialog" aria-label="Cookie consent" hidden>
    <div class="container">
        <p>
            We use cookies to provide and improve our services.
            <?php if ($policyUrl !== ''): ?>
                <a href="<?= esc($policyUrl) ?>" target="_blank" rel="noopener" style="color:#bbb">Privacy Policy</a>
            <?php endif; ?>
        </p>
        <div>
            <button type="button" id="mk-decline">Decline</button>
            <button type="button" id="mk-accept">Accept</button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = 'ck_consent';
    if (document.cookie.indexOf(KEY + '=') !== -1) return;

    var banner = document.getElementById('mk-consent');
    if (!banner) return;
    banner.hidden = false;

    function persist(value) {
        var d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = KEY + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
        banner.hidden = true;
    }

    document.getElementById('mk-accept').addEventListener('click', function () { persist('accepted'); });
    document.getElementById('mk-decline').addEventListener('click', function () { persist('declined'); });
})();
</script>
<?php endif; ?>
