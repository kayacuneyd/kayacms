<?php
$footerText  = $theme_config['footer_text'] ?? '';
$footerEmail = $theme_config['footer_email'] ?? '';
?>
<footer class="site">
    <div class="wrap footer-inner">
        <div class="footer-nav">
            <span>&copy; <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'KayaCMS') ?>. All rights reserved.</span>
            <a href="<?= base_url('/admin') ?>">Admin</a>
            <?php if ($footerEmail !== ''): ?>
                <a href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a>
            <?php endif; ?>
        </div>
        <?php if (! empty($footerText)): ?>
            <span><?= esc($footerText) ?></span>
        <?php endif; ?>
    </div>
</footer>
<?= $this->include('themes/landing/partials/cookie_consent'); ?>
<?= ($settings['footer_scripts'] ?? '') ?>
</body>
</html>