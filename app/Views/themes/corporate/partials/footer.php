<?php
$cfg          = $theme_config ?? [];
$footerPhone  = $cfg['footer_phone'] ?? '';
$footerFax    = $cfg['footer_fax'] ?? '';
$footerAddr   = $cfg['footer_address'] ?? '';
$footerEmail  = $cfg['footer_email'] ?? '';
$siteName     = $settings['site_name'] ?? 'K&Z Hukuk';
$importantNav = $menus['footer'] ?? [];
?>
<footer class="site">
    <div class="wrap">
        <div class="footer-grid">
            <div class="footer-col">
                <h6>İletişim</h6>
                <ul class="number-list">
                    <?php if ($footerPhone !== ''): ?>
                        <li>P <a href="tel:<?= esc($footerPhone) ?>"><?= esc($footerPhone) ?></a></li>
                    <?php endif; ?>
                    <?php if ($footerFax !== ''): ?>
                        <li>F <a href="tel:<?= esc($footerFax) ?>"><?= esc($footerFax) ?></a></li>
                    <?php endif; ?>
                    <?php if ($footerEmail !== ''): ?>
                        <li>E <a href="mailto:<?= esc($footerEmail) ?>"><?= esc($footerEmail) ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h6>Adres</h6>
                <p><?= nl2br(esc($footerAddr)) ?></p>
            </div>

            <div class="footer-col">
                <h6>Önemli Linkler</h6>
                <ul>
                    <li><a href="<?= localized_url('/') ?>">Anasayfa</a></li>
                    <?php if (! empty($importantNav)): ?>
                        <?php foreach ($importantNav as $fitem): ?>
                            <li><a href="<?= esc($fitem['url'] ?? '#') ?>"><?= esc($fitem['title']) ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h6>Sosyal Medya</h6>
                <ul>
                    <li><a href="https://x.com/" target="_blank" rel="noopener">Twitter</a></li>
                    <li><a href="https://facebook.com/" target="_blank" rel="noopener">Facebook</a></li>
                    <li><a href="https://linkedin.com/" target="_blank" rel="noopener">LinkedIn</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> | <?= esc($siteName) ?>. Tüm hakları saklıdır.</span>
            <span>Web tasarım ve geliştirme: <a href="https://kayacuneyt.com" target="_blank" rel="noopener">Cüneyt Kaya Web Design</a></span>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var burger = document.getElementById('navBurger');
    var menu   = document.getElementById('navMenu');
    if (burger && menu) {
        burger.addEventListener('click', function () { menu.classList.toggle('open'); });
        menu.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { menu.classList.remove('open'); });
        });
    }
});
</script>
<?= $this->include('themes/corporate/partials/cookie_consent'); ?>
<?= ($settings['footer_scripts'] ?? '') ?>
</body>
</html>
