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
$brandColor      = $themeConfig['brand_color'] ?? '#2563eb';
$showSearch      = ($themeConfig['show_search'] ?? '1') === '1';
$headerLayout    = $themeConfig['header_layout'] ?? 'boxed';

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
        :root { --brand: <?= esc($brandColor) ?>; --brand-dark: #1d4ed8; --ink: #1f2937; --muted: #6b7280; --bg: #f9fafb; --card: #ffffff; --border: #e5e7eb; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: var(--ink); background: var(--bg); line-height: 1.6; }
        a { color: var(--brand); text-decoration: none; }
        a:hover { color: var(--brand-dark); }
        .wrap { max-width: 1080px; margin: 0 auto; padding: 0 20px; }
        header.site { background: var(--card); border-bottom: 1px solid var(--border); }
        header.site.header-full .wrap.nav { max-width: none; }
        .nav { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; flex-wrap: wrap; gap: 12px; }
        .brand { font-size: 1.4rem; font-weight: 800; color: var(--ink); }
        .brand span { color: var(--brand); }
        .nav-right { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        ul.menu { display: flex; list-style: none; margin: 0; padding: 0; gap: 6px; flex-wrap: wrap; }
        ul.menu > li { position: relative; }
        ul.menu a { display: block; padding: 8px 12px; border-radius: 6px; color: var(--ink); }
        ul.menu > li > a:hover { background: var(--bg); color: var(--brand); }
        ul.menu ul { display: none; position: absolute; top: 100%; left: 0; background: var(--card); border: 1px solid var(--border); border-radius: 6px; list-style: none; margin: 0; padding: 6px; min-width: 180px; box-shadow: 0 10px 20px rgba(0,0,0,.08); z-index: 10; }
        ul.menu li:hover > ul { display: block; }
        .lang-switcher { display: flex; gap: 4px; align-items: center; }
        .lang-switcher a { padding: 4px 8px; border-radius: 4px; font-size: .85rem; color: var(--muted); }
        .lang-switcher a.active { background: var(--brand); color: #fff; }
        .lang-switcher a:hover:not(.active) { background: var(--bg); }
        .breadcrumbs { padding: 14px 0; font-size: .85rem; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); }
        .breadcrumbs ol { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; }
        .breadcrumbs li { display: flex; align-items: center; gap: 6px; }
        .breadcrumbs li:not(:last-child)::after { content: '/'; color: var(--border); }
        .fallback-notice { background: #fffbeb; border: 1px solid #f59e0b; color: #92400e; padding: 12px 20px; text-align: center; font-size: .9rem; }
        .hero { padding: 60px 0 40px; text-align: center; }
        .hero h1 { margin: 0 0 12px; font-size: 2.2rem; }
        .hero p { margin: 0; color: var(--muted); font-size: 1.1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; padding-bottom: 60px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 24px; display: flex; flex-direction: column; }
        .card h3 { margin: 0 0 8px; font-size: 1.15rem; }
        .card h3 a { color: var(--ink); }
        .card p.excerpt { color: var(--muted); margin: 0 0 14px; flex: 1; }
        .meta { font-size: .82rem; color: var(--muted); }
        article.single { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; margin: 32px 0 60px; }
        article.single h1 { margin-top: 0; }
        article.single .content { font-size: 1.05rem; }
        article.single .content h2 { margin-top: 28px; }
        footer.site { border-top: 1px solid var(--border); background: var(--card); margin-top: 20px; }
        .footer-inner { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; color: var(--muted); font-size: .9rem; flex-wrap: wrap; gap: 10px; }
        .btn { display:inline-block; background: var(--brand); color:#fff; padding:10px 16px; border-radius:8px; }
        .btn:hover { background: var(--brand-dark); color: #fff; }
        .pager { display:flex; gap:12px; justify-content:center; padding-bottom:60px; }
        .empty { text-align:center; color:var(--muted); padding:60px 0; }
        .search-bar { max-width: 560px; margin: 0 auto 40px; display: flex; gap: 8px; }
        .search-bar input { flex: 1; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; }
        .search-bar button { padding: 12px 20px; border: 0; background: var(--brand); color: #fff; border-radius: 8px; cursor: pointer; }
        @media (max-width: 640px){ .nav{flex-direction:column; align-items:flex-start;} .nav-right { width: 100%; justify-content: space-between; } .search-bar { flex-direction: column; } }
    </style>
    <?= ($settings['header_scripts'] ?? '') ?>
</head>
<body>
<header class="site <?= $headerLayout === 'full' ? 'header-full' : '' ?>">
    <div class="wrap nav">
        <a class="brand" href="<?= localized_url('/') ?>"><?= esc($siteName) ?> <span>CMS</span></a>
        <div class="nav-right">
            <nav>
                <ul class="menu">
                    <li><a href="<?= localized_url('/') ?>">Home</a></li>
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

                        // Strip existing locale prefix
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

                        // Add new prefix if not default locale
                        if ($loc !== $defaultLocale) {
                            $targetPath = $prefix . ($targetPath === '/' ? '' : $targetPath);
                        }
                        ?>
                        <a href="<?= esc($targetPath) ?>" class="<?= $loc === $locale ? 'active' : '' ?>" title="<?= strtoupper($loc) ?>">
                            <?= locale_flag($loc) ?> <?= strtoupper($loc) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (! empty($showFallbackNotice ?? false)): ?>
    <div class="fallback-notice">
        <div class="wrap">This content is not available in <?= strtoupper($locale) ?>. Showing <?= strtoupper($defaultLocale) ?> version.</div>
    </div>
<?php endif; ?>

<?php if (! empty($breadcrumbs) && is_array($breadcrumbs)): ?>
    <div class="breadcrumbs">
        <div class="wrap">
            <nav aria-label="breadcrumb">
                <ol>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <li>
                            <?php if (! empty($crumb['url'])): ?>
                                <a href="<?= esc($crumb['url']) ?>"><?= esc($crumb['label']) ?></a>
                            <?php else: ?>
                                <span><?= esc($crumb['label']) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
    </div>
<?php endif; ?>
