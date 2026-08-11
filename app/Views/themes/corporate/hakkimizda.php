<?php
$cfg = $theme_config ?? [];
$aboutTitle = $cfg['about_title'] ?? 'Hakkımızda';
$about      = is_array($cfg['about'] ?? null) ? $cfg['about'] : [];
?>
<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <article class="article">
        <h1><?= esc($aboutTitle) ?></h1>
        <div class="content">
            <?= $virtual_body ?? '' ?>
        </div>
    </article>

    <?php if (! empty($about)): ?>
        <div class="blog-grid" style="margin-top:4rem;">
            <?php foreach ($about as $value): ?>
                <div class="post-card">
                    <div class="body">
                        <h3><?= esc($value['title'] ?? '') ?></h3>
                        <p class="c-grey"><?= esc($value['desc'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>