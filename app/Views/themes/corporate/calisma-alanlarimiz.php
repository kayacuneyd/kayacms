<?php
$cfg = $theme_config ?? [];
$practice = is_array($cfg['practice'] ?? null) ? $cfg['practice'] : [];
?>
<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <div class="section-head center">
        <h1>Çalışma Alanlarımız</h1>
    </div>

    <div class="blog-grid">
        <?php foreach ($practice as $p): ?>
            <div class="post-card">
                <div class="body" style="text-align:center;">
                    <?php if (! empty($p['icon'])): ?>
                        <img src="<?= esc($p['icon']) ?>" alt="" style="width:64px;height:64px;object-fit:contain;margin:0 auto 1.6rem;">
                    <?php endif; ?>
                    <h3><?= esc($p['title'] ?? '') ?></h3>
                    <p class="c-grey"><?= esc($p['desc'] ?? '') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($practice)): ?>
        <p class="empty">Çalışma alanları henüz eklenmedi. Admin → Temalar → Yapılandır üzerinden ekleyin.</p>
    <?php endif; ?>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>