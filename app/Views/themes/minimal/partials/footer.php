<?php $footerText = $theme_config['footer_text'] ?? ''; ?>
<footer class="site-footer">
    <div class="container">
        <span>&copy; <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'KayaCMS') ?></span>
        <?php if (! empty($footerText)): ?>
            <span><?= esc($footerText) ?></span>
        <?php endif; ?>
        <a href="<?= base_url('/admin') ?>">Admin</a>
    </div>
</footer>
<?= $this->include('themes/minimal/partials/cookie_consent'); ?>
<?= ($settings['footer_scripts'] ?? '') ?>
</body>
</html>
