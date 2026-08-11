<?php
$cfg              = $theme_config ?? [];
$heroHeadline     = $cfg['hero_headline'] ?? 'Build something people love.';
$heroSub          = $cfg['hero_subheadline'] ?? '';
$heroBadge        = $cfg['hero_badge'] ?? '';
$heroCtaText      = $cfg['hero_cta_text'] ?? '';
$heroCtaUrl       = $cfg['hero_cta_url'] ?? '#features';
$featuresTitle    = $cfg['features_title'] ?? '';
$featuresIntro    = $cfg['features_intro'] ?? '';
$featuresRaw      = (string) ($cfg['features'] ?? '');
$showArticles     = ($cfg['show_articles'] ?? '1') === '1';
$articlesTitle    = $cfg['articles_title'] ?? 'Latest from the blog';
$ctaTitle         = $cfg['cta_title'] ?? '';
$ctaText          = $cfg['cta_text'] ?? '';
$ctaButton        = $cfg['cta_button_text'] ?? '';
$ctaUrl           = $cfg['cta_button_url'] ?? '/admin';
$stats            = is_array($cfg['stats'] ?? null) ? $cfg['stats'] : [];

$features = [];
foreach (preg_split('/\r?\n/', $featuresRaw) as $line) {
    $line = trim($line);
    if ($line === '') {
        continue;
    }
    $parts = array_map('trim', explode('|', $line));
    $features[] = [
        'icon' => $parts[0] ?? '',
        'title' => $parts[1] ?? '',
        'desc' => $parts[2] ?? '',
    ];
}
?>
<?= $this->include('themes/landing/partials/header'); ?>

<section class="hero">
    <div class="wrap">
        <?php if ($heroBadge !== ''): ?>
            <span class="badge"><?= esc($heroBadge) ?></span>
        <?php endif; ?>
        <h1><?= esc($heroHeadline) ?></h1>
        <?php if ($heroSub !== ''): ?>
            <p><?= esc($heroSub) ?></p>
        <?php endif; ?>
        <?php if ($heroCtaText !== ''): ?>
            <div class="hero-actions">
                <a class="btn btn-brand" href="<?= esc($heroCtaUrl) ?>"><?= esc($heroCtaText) ?></a>
                <?php if (! empty($articles)): ?>
                    <a class="btn btn-ghost" href="#articles"><?= esc($articlesTitle) ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (! empty($stats)): ?>
<section class="block" id="stats" style="padding:36px 0;">
    <div class="wrap">
        <div class="grid">
            <?php foreach ($stats as $stat): ?>
                <div class="card" style="text-align:center;padding:22px 18px;">
                    <div style="font-size:2.2rem;font-weight:800;color:var(--brand);margin-bottom:6px;"><?= esc($stat['value'] ?? '') ?></div>
                    <div style="color:var(--muted);font-size:.95rem;"><?= esc($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (! empty($features)): ?>
<section class="block" id="features">
    <div class="wrap">
        <div class="sec-head">
            <?php if ($featuresTitle !== ''): ?>
                <h2><?= esc($featuresTitle) ?></h2>
            <?php endif; ?>
            <?php if ($featuresIntro !== ''): ?>
                <p><?= esc($featuresIntro) ?></p>
            <?php endif; ?>
        </div>
        <div class="grid">
            <?php foreach ($features as $feature): ?>
                <div class="card">
                    <?php if ($feature['icon'] !== ''): ?>
                        <div class="icon"><?= esc($feature['icon']) ?></div>
                    <?php endif; ?>
                    <?php if ($feature['title'] !== ''): ?>
                        <h3><?= esc($feature['title']) ?></h3>
                    <?php endif; ?>
                    <?php if ($feature['desc'] !== ''): ?>
                        <p><?= esc($feature['desc']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($showArticles && ! empty($articles)): ?>
<section class="block" id="articles">
    <div class="wrap">
        <div class="sec-head">
            <h2><?= esc($articlesTitle) ?></h2>
        </div>
        <div class="blog-grid">
            <?php foreach ($articles as $item): ?>
                <article class="post-card">
                    <div class="thumb">
                        <?php if (! empty($item->featured_image)): ?>
                            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" loading="lazy">
                        <?php else: ?>
                            <?= esc(mb_strtoupper(mb_substr($item->title ?? 'K', 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="body">
                        <h3><a href="<?= localized_url('/content/' . $item->slug) ?>"><?= esc($item->title) ?></a></h3>
                        <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item->body ?? null, $item->excerpt ?? null)) ?></p>
                        <span class="meta"><?= esc(date('M j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($ctaTitle !== ''): ?>
<section class="cta-block">
    <div class="wrap">
        <h2><?= esc($ctaTitle) ?></h2>
        <?php if ($ctaText !== ''): ?>
            <p><?= esc($ctaText) ?></p>
        <?php endif; ?>
        <?php if ($ctaButton !== ''): ?>
            <a class="btn btn-brand" href="<?= esc($ctaUrl) ?>"><?= esc($ctaButton) ?></a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?= $this->include('themes/landing/partials/footer'); ?>