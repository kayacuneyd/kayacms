<?php $footerText = $theme_config['footer_text'] ?? ''; ?>
<footer class="site">
    <div class="wrap footer-inner">
        <span>&copy; <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'KayaCMS') ?>. All rights reserved.</span>
        <?php if (! empty($footerText)): ?>
            <span><?= esc($footerText) ?></span>
        <?php endif; ?>
        <a href="<?= base_url('/admin') ?>">Admin</a>
    </div>
</footer>
<?= $this->include('themes/default/partials/cookie_consent'); ?>
<?= ($settings['footer_scripts'] ?? '') ?>
</body>
</html>
