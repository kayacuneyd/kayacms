<?php
$siteName        = $settings['site_name'] ?? 'KayaCMS';
$siteDescription = $settings['site_description'] ?? '';
$pageTitle       = $page_title ?? ($title ?? $siteName);
$navItems        = $menus['primary'] ?? [];
$canonicalUrl    = $canonical_url ?? base_url(current_url());
$ogType          = $og_type ?? 'website';
$metaDescription = $meta_description ?? $siteDescription;
$locale          = $locale ?? 'tr';
$defaultLocale   = $defaultLocale ?? 'tr';
$activeLocales   = $activeLocales ?? [$locale];
$localizedLinks  = $localizedLinks ?? [];
$themeConfig     = $theme_config ?? [];
$brandColor      = $themeConfig['brand_color'] ?? '#6366f1';

?>
<!DOCTYPE html>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? $pageTitle) ?></title>
    <meta name="description" content="<?= esc($metaDescription) ?>">
    <link rel="canonical" href="<?= esc($canonicalUrl) ?>">
    <?php foreach ($activeLocales as $loc): ?>
        <?php if (isset($localizedLinks[$loc])): ?>
            <link rel="alternate" hreflang="<?= esc($loc) ?>" href="<?= esc($localizedLinks[$loc]) ?>">
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (isset($localizedLinks[$defaultLocale])): ?>
        <link rel="alternate" hreflang="x-default" href="<?= esc($localizedLinks[$defaultLocale]) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= esc($pageTitle) ?>">
    <meta property="og:description" content="<?= esc($metaDescription) ?>">
    <meta property="og:type" content="<?= esc($ogType) ?>">
    <meta property="og:url" content="<?= esc($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= esc($siteName) ?>">
    <?php if (! empty($item->featured_image ?? '')): ?>
        <meta property="og:image" content="<?= esc(base_url($item->featured_image)) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($pageTitle) ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription) ?>">
    <?php if (! empty($item->featured_image ?? '')): ?>
        <meta name="twitter:image" content="<?= esc(base_url($item->featured_image)) ?>">
    <?php endif; ?>
    <link rel="alternate" type="application/rss+xml" title="<?= esc($siteName) ?> RSS Feed" href="<?= base_url('/' . ($locale === $defaultLocale ? '' : $locale . '/') . 'feed.xml') ?>">
    <style>
        :root { --brand: <?= esc($brandColor) ?>; --bg: #0b1020; --surface: #111733; --surface-2: #181f3d; --ink: #eef1f8; --muted: #9aa4c0; --border: #242c4e; --brand-glow: rgba(99,102,241,.35); }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; font-family: "Segoe UI", system-ui, -apple-system, Roboto, Helvetica, Arial, sans-serif; color: var(--ink); background: var(--bg); line-height: 1.65; }
        a { color: var(--brand); text-decoration: none; }
        a:hover { text-decoration: none; filter: brightness(1.15); }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
        /* Nav */
        .site-nav { position: sticky; top: 0; z-index: 50; background: rgba(11,16,32,.82); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; gap: 16px; }
        .brand { font-size: 1.25rem; font-weight: 800; color: var(--ink); letter-spacing: -.01em; }
        .brand .dot { color: var(--brand); }
        .nav-right { display: flex; align-items: center; gap: 18px; }
        ul.menu { display: flex; list-style: none; margin: 0; padding: 0; gap: 4px; flex-wrap: wrap; }
        ul.menu a { display: block; padding: 8px 12px; border-radius: 999px; color: var(--muted); font-size: .93rem; }
        ul.menu a:hover { color: var(--ink); background: var(--surface-2); }
        .nav-cta { background: var(--brand); color: #fff !important; padding: 9px 18px; border-radius: 999px; font-weight: 600; }
        .nav-cta:hover { filter: brightness(1.1); }
        /* Hero */
        .hero { position: relative; overflow: hidden; padding: 96px 0 84px; text-align: center; }
        .hero::before { content: ""; position: absolute; inset: -40% -20% auto; height: 90%; background: radial-gradient(closest-side, var(--brand-glow), transparent 70%); z-index: -1; }
        .badge { display: inline-block; padding: 6px 14px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface); color: var(--muted); font-size: .82rem; margin-bottom: 22px; }
        .hero h1 { margin: 0 auto 16px; max-width: 820px; font-size: 3rem; line-height: 1.12; letter-spacing: -.03em; }
        .hero p { margin: 0 auto 32px; max-width: 640px; color: var(--muted); font-size: 1.12rem; }
        .hero-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 13px 26px; border-radius: 999px; font-weight: 600; font-size: .95rem; }
        .btn-brand { background: var(--brand); color: #fff !important; }
        .btn-ghost { border: 1px solid var(--border); color: var(--ink) !important; }
        .btn-ghost:hover { border-color: var(--brand); color: var(--brand) !important; }
        /* Sections */
        section.block { padding: 64px 0; border-top: 1px solid var(--border); }
        .sec-head { text-align: center; max-width: 660px; margin: 0 auto 44px; }
        .sec-head h2 { margin: 0 0 12px; font-size: 2rem; letter-spacing: -.02em; }
        .sec-head p { margin: 0; color: var(--muted); }
        /* Features grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 26px; display: flex; flex-direction: column; transition: transform .15s ease, border-color .15s ease; }
        .card:hover { transform: translateY(-3px); border-color: var(--brand); }
        .card .icon { font-size: 1.6rem; margin-bottom: 12px; }
        .card h3 { margin: 0 0 8px; font-size: 1.1rem; }
        .card p { margin: 0; color: var(--muted); font-size: .95rem; flex: 1; }
        /* Blog cards */
        .blog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .post-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; }
        .post-card .thumb { height: 150px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--brand); font-size: 2rem; overflow: hidden; }
        .post-card .thumb img { width: 100%; height: 100%; object-fit: cover; }
        .post-card .body { padding: 20px 24px 24px; display: flex; flex-direction: column; flex: 1; }
        .post-card h3 { margin: 0 0 8px; font-size: 1.08rem; }
        .post-card h3 a { color: var(--ink); }
        .post-card p.excerpt { color: var(--muted); margin: 0 0 16px; font-size: .93rem; flex: 1; }
        .meta { font-size: .82rem; color: var(--muted); }
        .cta-block { text-align: center; padding: 80px 0; }
        .cta-block h2 { font-size: 2.2rem; margin: 0 auto 12px; max-width: 640px; letter-spacing: -.02em; }
        .cta-block p { color: var(--muted); margin: 0 auto 28px; max-width: 520px; }
        /* Single / content pages */
        article.article { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 36px; margin: 32px 0 48px; }
        article.article h1 { margin-top: 0; }
        article.article .content { color: var(--ink); }
        article.article .content h2 { margin-top: 28px; }
        article.article .content img { max-width: 100%; border-radius: 10px; }
        article.article .content blockquote { border-left: 3px solid var(--brand); margin-left: 0; padding-left: 18px; color: var(--muted); }
        article.article .content pre { background: var(--surface-2); padding: 14px; border-radius: 10px; overflow: auto; }
        .empty { text-align: center; color: var(--muted); padding: 60px 0; }
        .pager { display: flex; gap: 12px; justify-content: center; padding: 20px 0 60px; align-items: center; }
        /* Footer */
        footer.site { border-top: 1px solid var(--border); background: var(--surface); margin-top: 40px; }
        .footer-inner { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 24px 0; color: var(--muted); font-size: .9rem; }
        .footer-inner a { color: var(--muted); }
        .footer-inner a:hover { color: var(--ink); }
        .footer-nav { display: flex; gap: 16px; flex-wrap: wrap; }
        .footer-links a { margin-left: 14px; }
        .lang-switcher { display: flex; gap: 4px; align-items: center; }
        .lang-switcher a { padding: 4px 8px; border-radius: 6px; font-size: .8rem; color: var(--muted); }
        .lang-switcher a.active { background: var(--brand); color: #fff; }
        @media (max-width: 680px) {
            .hero h1 { font-size: 2.2rem; }
            .nav-inner { flex-direction: column; align-items: flex-start; }
            .nav-right { width: 100%; justify-content: space-between; }
        }
    </style>
    <?= ($settings['header_scripts'] ?? '') ?>
</head>
<body>
<header class="site-nav">
    <div class="wrap nav-inner">
        <a class="brand" href="<?= localized_url('/') ?>"><?= esc($siteName) ?><span class="dot">.</span></a>
        <div class="nav-right">
            <nav>
                <ul class="menu">
                    <?php if (! empty($navItems)): ?>
                        <?php foreach ($navItems as $mitem): ?>
                            <li>
                                <a href="<?= esc($mitem['url'] ?? '#') ?>" <?= !empty($mitem['target']) ? 'target="'.$mitem['target'].'" rel="noopener"' : '' ?>><?= esc($mitem['title']) ?></a>
                                <?php if (! empty($mitem['children'])): ?>
                                    <ul>
                                        <?php foreach ($mitem['children'] as $child): ?>
                                            <li><a href="<?= esc($child['url'] ?? '#') ?>"><?= esc($child['title']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?= localized_url('/') ?>">Home</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php if (count($activeLocales) > 1): ?>
                <div class="lang-switcher">
                    <?php foreach ($activeLocales as $loc): ?>
                        <?php
                        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                        $prefix = '/' . $loc;
                        $targetPath = $currentPath;

                        foreach ($activeLocales as $l) {
                            if (strpos($targetPath, '/' . $l . '/') === 0) {
                                $targetPath = substr($targetPath, strlen('/' . $l));
                                break;
                            }
                            if ($targetPath === '/' . $l) {
                                $targetPath = '/';
                                break;
                            }
                        }

                        if ($loc !== $defaultLocale) {
                            $targetPath = $prefix . ($targetPath === '/' ? '' : $targetPath);
                        }
                        ?>
                        <a href="<?= esc($targetPath) ?>" class="<?= $loc === $locale ? 'active' : '' ?>" title="<?= strtoupper($loc) ?>">
                            <?= strtoupper($loc) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php $heroCta = $themeConfig['hero_cta_text'] ?? ''; if ($heroCta !== ''): ?>
                <a class="nav-cta" href="<?= esc($themeConfig['hero_cta_url'] ?? '/admin') ?>"><?= esc($heroCta) ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (! empty($showFallbackNotice ?? false)): ?>
    <div style="background:#fffbeb;border:1px solid #f59e0b;color:#fbbf24;padding:12px 24px;text-align:center;font-size:.9rem;">
        This content is not available in <?= strtoupper($locale) ?>. Showing <?= strtoupper($defaultLocale) ?> version.
    </div>
<?php endif; ?>