<?php
$cfg = $theme_config ?? [];
$teamTitle = $cfg['team_title'] ?? 'Takımımız';
$team      = is_array($cfg['team'] ?? null) ? $cfg['team'] : [];
?>
<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <div class="section-head center">
        <h1><?= esc($teamTitle) ?></h1>
    </div>

    <div class="blog-grid">
        <?php foreach ($team as $member): ?>
            <div class="post-card">
                <?php if (! empty($member['photo'])): ?>
                    <div class="thumb" style="height:auto;aspect-ratio:4/3;">
                        <img src="<?= esc($member['photo']) ?>" alt="<?= esc($member['name'] ?? '') ?>" loading="lazy">
                    </div>
                <?php endif; ?>
                <div class="body" style="text-align:center;">
                    <h3><?= esc($member['name'] ?? '') ?></h3>
                    <?php if (! empty($member['email'])): ?>
                        <p class="c-grey"><a href="mailto:<?= esc($member['email']) ?>"><?= esc($member['email']) ?></a></p>
                    <?php endif; ?>
                    <?php if (! empty($member['linkedin'])): ?>
                        <p><a href="<?= esc($member['linkedin']) ?>" target="_blank" rel="noopener" style="color:var(--accent);">LinkedIn</a></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($team)): ?>
        <p class="empty">Takım üyeleri henüz eklenmedi. Admin → Temalar → Yapılandır üzerinden ekleyin.</p>
    <?php endif; ?>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>