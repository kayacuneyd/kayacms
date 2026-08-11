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
$containerWidth  = ($theme_config['container_width'] ?? 'narrow') === 'full' ? 'full' : 'narrow';
$themeConfig     = $theme_config ?? [];
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
    <link rel="alternate" type="application/rss+xml" title="<?= esc($siteName) ?> RSS Feed" href="<?= base_url('/' . ($locale === $defaultLocale ? '' : $locale . '/') . 'feed.xml') ?>">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, "Times New Roman", serif; color: #111; background: #fff; line-height: 1.7; }
        a { color: inherit; text-decoration: underline; text-underline-offset: 3px; }
        .container { max-width: <?= $containerWidth === 'full' ? '1200px' : '680px' ?>; margin: 0 auto; padding: 0 20px; }
        .site-header { border-bottom: 1px solid #e5e5e5; padding: 28px 0; }
        .site-header .container { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .brand { font-size: 1.35rem; font-weight: 700; text-decoration: none; letter-spacing: .5px; }
        .brand span { color: #777; font-weight: 400; }
        ul.menu { display: flex; list-style: none; margin: 0; padding: 0; gap: 20px; }
        ul.menu a { text-decoration: none; color: #333; }
        ul.menu a:hover { text-decoration: underline; }
        .lang-switcher { display: flex; gap: 8px; font-size: .85rem; color: #999; }
        .lang-switcher a { text-decoration: none; }
        .lang-switcher a.active { color: #111; font-weight: 700; }
        .breadcrumbs { font-size: .85rem; color: #888; padding: 16px 0; }
        .breadcrumbs ol { list-style: none; margin: 0; padding: 0; display: flex; gap: 8px; flex-wrap: wrap; }
        .breadcrumbs li + li::before { content: '/'; color: #ccc; margin-right: 8px; }
        .fallback-notice { background: #fffbea; border-bottom: 1px solid #f0e6c8; color: #7a5c00; padding: 10px 0; font-size: .9rem; text-align: center; }
        .page-heading { padding: 48px 0 8px; }
        .page-heading h1 { margin: 0; font-size: 2rem; }
        .page-heading p { color: #666; margin: 8px 0 0; }
        .search-bar { display: flex; gap: 8px; max-width: 480px; margin: 24px 0 40px; }
        .search-bar input { flex: 1; padding: 10px 14px; border: 1px solid #ccc; border-radius: 0; font: inherit; }
        .search-bar button { padding: 10px 18px; border: 1px solid #111; background: #111; color: #fff; cursor: pointer; font: inherit; }
        .post-list { list-style: none; margin: 0; padding: 0 0 60px; }
        .post-list li { padding: 28px 0; border-bottom: 1px solid #eee; }
        .post-list li:first-child { border-top: 1px solid #eee; }
        .post-list h2 { margin: 0 0 6px; font-size: 1.35rem; }
        .post-list h2 a { text-decoration: none; color: #111; }
        .post-list h2 a:hover { text-decoration: underline; }
        .meta { color: #888; font-size: .85rem; }
        .excerpt { color: #444; margin: 8px 0 0; }
        article.post { padding: 40px 0 80px; }
        article.post h1 { font-size: 2.2rem; line-height: 1.25; margin: 0 0 8px; }
        article.post img.featured { width: 100%; height: auto; margin: 24px 0; }
        article.post .content { font-size: 1.05rem; }
        article.post .content h2 { margin-top: 40px; }
        .comments { border-top: 1px solid #eee; padding-top: 32px; margin-top: 48px; }
        .comment-form input, .comment-form textarea { width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 0; font: inherit; margin: 4px 0 12px; }
        .comment-form button { padding: 10px 18px; border: 1px solid #111; background: #111; color: #fff; cursor: pointer; font: inherit; }
        .pager { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 60px; }
        .empty { color: #888; padding: 40px 0 60px; }
        .site-footer { border-top: 1px solid #e5e5e5; padding: 28px 0; color: #888; font-size: .9rem; }
        .site-footer .container { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        @media (max-width: 640px) { .site-header .container { flex-direction: column; align-items: flex-start; } .search-bar { flex-direction: column; } }
    </style>
    <?= ($settings['header_scripts'] ?? '') ?>
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="brand" href="<?= localized_url('/') ?>"><?= esc($siteName) ?></a>
        <div>
            <nav>
                <ul class="menu">
                    <li><a href="<?= localized_url('/') ?>">Home</a></li>
                    <?php if (! empty($navItems)): ?>
                        <?php foreach ($navItems as $mitem): ?>
                            <li>
                                <a href="<?= esc($mitem['url'] ?? '#') ?>" <?= !empty($mitem['target']) ? 'target="'.$mitem['target'].'" rel="noopener"' : '' ?>><?= esc($mitem['title']) ?></a>
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
                            $targetPath = '/' . $loc . ($targetPath === '/' ? '' : $targetPath);
                        }
                        ?>
                        <a href="<?= esc($targetPath) ?>" class="<?= $loc === $locale ? 'active' : '' ?>"><?= strtoupper($loc) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (! empty($showFallbackNotice ?? false)): ?>
    <div class="fallback-notice">
        <div class="container">This content is not available in <?= strtoupper($locale) ?>. Showing <?= strtoupper($defaultLocale) ?> version.</div>
    </div>
<?php endif; ?>

<?php if (! empty($breadcrumbs) && is_array($breadcrumbs)): ?>
    <div class="breadcrumbs">
        <div class="container">
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
