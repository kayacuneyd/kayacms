<?php
$siteName        = $settings['site_name'] ?? 'K&Z Hukuk';
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
$accent          = $themeConfig['brand_color'] ?? '#de252a';
$footerPhone     = $themeConfig['footer_phone'] ?? '';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;500;600;700;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        :root { --accent: <?= esc($accent) ?>; --black: #000000; --ink: #1a1a1a; --grey: #4d4d4d; --light: #ededed; --faint: #fafafa; --line: #e5e5e5; --border-w: 20px; }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; font-family: "Libre Franklin", system-ui, -apple-system, Roboto, Arial, sans-serif; color: var(--ink); background: #fff; line-height: 1.65; font-size: 1.8rem; }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3 { font-family: "Playfair Display", Georgia, serif; font-weight: 600; letter-spacing: -.01em; }
        h1 { font-size: 5.2rem; line-height: 1.1; margin: 0 0 2rem; }
        h2 { font-size: 4rem; line-height: 1.15; margin: 0 0 2rem; }
        h3 { font-size: 2.8rem; line-height: 1.3; margin: 0 0 1.4rem; }
        p { margin: 0 0 2.4rem; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .txt-upper { text-transform: uppercase; letter-spacing: .12em; }
        .c-grey { color: var(--grey); }
        .c-white { color: #fff; }
        .c-accent { color: var(--accent); }
        .center { text-align: center; }
        /* Nav */
        .nav { position: fixed; top: 0; left: 0; width: 100%; z-index: 500; background: #fff; padding: 2.4rem 5rem; border-left: var(--border-w) solid #000; border-right: var(--border-w) solid #000; display: flex; justify-content: space-between; align-items: center; gap: 2rem; }
        .nav-logo { display: flex; align-items: center; }
        .nav-logo img { max-height: 56px; width: auto; }
        .nav-logo .logo-text { font-family: "Playfair Display", serif; font-size: 2.6rem; font-weight: 700; color: #000; }
        ul.menu { display: flex; list-style: none; margin: 0; padding: 0; gap: .4rem; align-items: center; }
        ul.menu a { display: block; padding: .9rem 1.6rem; color: #000; font-size: 1.7rem; font-weight: 500; border-bottom: 3px solid transparent; }
        ul.menu a:hover, ul.menu li.active > a { border-bottom-color: var(--accent); color: var(--accent); }
        ul.menu li { position: relative; }
        ul.menu ul.child { display: none; position: absolute; top: 100%; left: 0; background: #fff; border: 1px solid var(--line); min-width: 240px; padding: .8rem 0; box-shadow: 0 12px 30px rgba(0,0,0,.08); list-style: none; }
        ul.menu li.has-child:hover > ul.child { display: block; }
        ul.menu ul.child a { padding: .9rem 1.8rem; font-size: 1.6rem; }
        .nav-burger { display: none; cursor: pointer; }
        .nav-burger div { width: 28px; height: 3px; background: #000; margin: 5px 0; }
        /* Hero */
        .hero { position: relative; margin-top: 0; overflow: hidden; }
        .hero .swiper { width: 100%; height: 92vh; min-height: 560px; }
        .hero-slide { position: relative; background-size: cover; background-position: center; display: flex; align-items: flex-start; justify-content: flex-start; padding: 12rem 8rem; }
        .hero-slide .hero-tag { background: rgba(240, 248, 255, .82); padding: 1.8rem 2.6rem; max-width: 720px; }
        .hero-slide .hero-tag h1 { color: #1a1a1a; margin: 0; font-size: 6.4rem; line-height: 1.05; }
        .hero-pagination { display: flex; justify-content: center; padding: 4rem 0 5rem; background: #fff; }
        .hero-pagination .swiper-wrapper { display: flex; align-items: flex-start; }
        .hero-pagination .hp-item { text-align: center; max-width: 240px; cursor: pointer; transition: opacity .2s ease; }
        .hero-pagination .hp-item img { width: 56px; height: 56px; object-fit: contain; margin-bottom: 1rem; }
        .hero-pagination .hp-item h4 { margin: 0 0 .4rem; font-size: 1.8rem; font-weight: 600; }
        .hero-pagination .hp-item p { margin: 0; font-size: 1.4rem; color: var(--grey); line-height: 1.5; }
        .hero-pagination .hp-item:not(.swiper-slide-active) { opacity: .45; }
        /* Sections */
        section.block { padding: 7rem 0; }
        .section-head { margin-bottom: 4.4rem; }
        .section-head h2 { margin-bottom: 1.2rem; }
        .section-head p { color: var(--grey); max-width: 760px; }
        /* Intro */
        .intro { display: grid; grid-template-columns: 1fr auto; gap: 4rem; align-items: start; padding: 8rem 0 2rem; }
        .vertical-txt { writing-mode: vertical-rl; transform: rotate(180deg); font-family: "Playfair Display", serif; font-size: 9rem; font-weight: 800; color: var(--line); margin: 0; line-height: 1; }
        .intro-img { margin-top: 4rem; width: 100%; }
        .intro-img img { width: 100%; height: auto; display: block; }
        /* Practice areas */
        .practice { background: #fafafa; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .practice .card { background: #fff; border: 1px solid var(--line); padding: 3.2rem 2.6rem; text-align: center; height: 100%; transition: box-shadow .2s ease, transform .2s ease; }
        .practice .card:hover { box-shadow: 0 14px 40px rgba(0,0,0,.08); transform: translateY(-3px); }
        .practice .card img { width: 64px; height: 64px; object-fit: contain; margin-bottom: 1.6rem; }
        .practice .card h3 { font-size: 2.4rem; margin-bottom: 1.2rem; }
        .practice .card p { color: var(--grey); font-size: 1.5rem; line-height: 1.7; margin: 0; }
        /* Testimonials */
        .testimonials { background: #000; color: #fff; }
        .testimonials .section-head h2 { color: #fff; }
        .testimonial-card { text-align: center; max-width: 780px; margin: 0 auto; padding: 2rem 0; }
        .testimonial-card .mark { font-size: 6rem; color: var(--accent); font-family: "Playfair Display", serif; line-height: .6; margin-bottom: 2rem; }
        .testimonial-card p.quote { font-size: 2rem; line-height: 1.8; margin-bottom: 2.4rem; color: #ededed; }
        .testimonial-card h5 { font-size: 1.7rem; text-transform: uppercase; letter-spacing: .1rem; color: var(--accent); margin: 0; }
        /* CTA */
        .cta { background: #000; color: #fff; text-align: center; padding: 8rem 0; }
        .cta h3 { color: #fff; font-size: 3.6rem; font-weight: 400; max-width: 760px; margin: 0 auto 1.4rem; }
        .cta h4 { font-size: 2rem; font-weight: 400; color: #ededed; margin: 0 0 2.4rem; }
        .cta .buttons { display: flex; gap: 1.6rem; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 1.2rem; padding: 1.7rem 3.2rem; font-size: 1.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: .1rem; border: 2px solid #fff; color: #fff; transition: all .2s ease; }
        .btn:hover { background: #fff; color: #000; }
        .btn-accent { border-color: var(--accent); color: #fff; }
        .btn-accent:hover { background: var(--accent); border-color: var(--accent); }
        /* Blog / cards */
        .blog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2.4rem; }
        .post-card { border: 1px solid var(--line); background: #fff; display: flex; flex-direction: column; }
        .post-card .thumb { height: 200px; background: var(--line); overflow: hidden; }
        .post-card .thumb img { width: 100%; height: 100%; object-fit: cover; }
        .post-card .body { padding: 2.4rem; display: flex; flex-direction: column; flex: 1; }
        .post-card h3 { font-size: 2.2rem; }
        .post-card h3 a { color: var(--ink); }
        .post-card h3 a:hover { color: var(--accent); }
        .post-card .excerpt { color: var(--grey); font-size: 1.5rem; flex: 1; }
        .post-card .meta { font-size: 1.3rem; color: var(--grey); text-transform: uppercase; letter-spacing: .08rem; }
        /* Single / content */
        article.article { max-width: 860px; margin: 0 auto; }
        article.article h1 { font-size: 5rem; }
        article.article .content { font-size: 1.8rem; }
        article.article .content img { max-width: 100%; height: auto; }
        article.article .content h2 { margin-top: 3rem; }
        article.article .content blockquote { border-left: 4px solid var(--accent); margin-left: 0; padding-left: 2rem; color: var(--grey); }
        article.article .content pre { background: var(--faint); border: 1px solid var(--line); padding: 1.6rem; overflow: auto; }
        .empty { text-align: center; color: var(--grey); padding: 6rem 0; }
        .pager { display: flex; gap: 1.2rem; justify-content: center; padding: 3rem 0 6rem; }
        .pager a, .pager span { padding: 1rem 1.8rem; border: 1px solid var(--line); color: var(--ink); font-size: 1.5rem; }
        .pager a:hover { border-color: var(--accent); color: var(--accent); }
        /* Forms (contact) */
        .form-card { background: var(--faint); border: 1px solid var(--line); padding: 3.2rem; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; padding: 1.4rem 1.6rem; border: 1px solid var(--line); background: #fff; font: inherit; font-size: 1.6rem; margin-bottom: 1.8rem; }
        .form-card label { display: block; font-weight: 600; margin-bottom: .6rem; font-size: 1.5rem; }
        .flash-success { border: 1px solid #16a34a; background: #ecfdf5; color: #166534; padding: 1.6rem; margin-bottom: 2rem; }
        .flash-error { border: 1px solid #dc2626; background: #fef2f2; color: #991b1b; padding: 1.6rem; margin-bottom: 2rem; }
        /* Footer */
        footer.site { border-top: 1px solid #000; background: #fff; padding: 6rem 0 3rem; }
        .footer-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 3rem; }
        .footer-col h6 { font-size: 1.5rem; font-weight: 600; text-transform: uppercase; letter-spacing: .1rem; color: #000; margin: 0 0 1.6rem; }
        .footer-col p, .footer-col li { color: var(--grey); font-size: 1.5rem; line-height: 1.9; }
        .footer-col ul { list-style: none; margin: 0; padding: 0; }
        .footer-col a { color: var(--grey); }
        .footer-col a:hover { color: var(--accent); }
        .footer-bottom { margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 1.6rem; flex-wrap: wrap; color: var(--grey); font-size: 1.4rem; }
        .lang-switcher { display: flex; gap: .4rem; }
        .lang-switcher a { padding: .4rem .8rem; font-size: 1.3rem; color: var(--grey); }
        .lang-switcher a.active { background: #000; color: #fff; }
        @media (max-width: 992px) {
            body { font-size: 1.6rem; }
            h1 { font-size: 3.6rem; }
            h2 { font-size: 2.8rem; }
            .nav { padding: 1.6rem 2.4rem; border-width: 10px; }
            .nav-burger { display: block; }
            .nav-menu { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: #fff; padding: 6rem 3rem; z-index: 600; }
            .nav-menu.open { display: block; }
            ul.menu { flex-direction: column; align-items: stretch; gap: 1rem; }
            ul.menu ul.child { position: static; box-shadow: none; border: none; padding-left: 2rem; }
            .hero .swiper { height: 70vh; }
            .hero-slide { padding: 8rem 3rem; }
            .hero-slide .hero-tag h1 { font-size: 3.4rem; }
            .hero-pagination { flex-wrap: wrap; gap: 2rem; }
            .intro { grid-template-columns: 1fr; }
            .vertical-txt { display: none; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .footer-grid { grid-template-columns: 1fr; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
    <?= ($settings['header_scripts'] ?? '') ?>
</head>
<body>

<div class="nav">
    <div class="nav-logo">
        <?php $logo = $settings['site_logo'] ?? ''; ?>
        <?php if ($logo !== ''): ?>
            <img src="<?= esc(base_url($logo)) ?>" alt="<?= esc($siteName) ?>">
        <?php else: ?>
            <span class="logo-text"><?= esc($siteName) ?></span>
        <?php endif; ?>
    </div>
    <div class="nav-menu" id="navMenu">
        <nav>
            <ul class="menu">
                <?php if (! empty($navItems)): ?>
                    <?php foreach ($navItems as $mitem): ?>
                        <li class="<?= !empty($mitem['children']) ? 'has-child' : '' ?> <?= (($virtual_slug ?? '') !== '' && ($virtual_slug ?? '') === ($mitem['slug'] ?? '')) ? 'active' : '' ?>">
                            <a href="<?= esc($mitem['url'] ?? '#') ?>" <?= !empty($mitem['target']) ? 'target="'.$mitem['target'].'" rel="noopener"' : '' ?>><?= esc($mitem['title']) ?></a>
                            <?php if (! empty($mitem['children'])): ?>
                                <ul class="child">
                                    <?php foreach ($mitem['children'] as $child): ?>
                                        <li><a href="<?= esc($child['url'] ?? '#') ?>"><?= esc($child['title']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a href="<?= localized_url('/') ?>">Anasayfa</a></li>
                <?php endif; ?>
                <?php if (count($activeLocales) > 1): ?>
                    <li>
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
                                <a href="<?= esc($targetPath) ?>" class="<?= $loc === $locale ? 'active' : '' ?>"><?= strtoupper($loc) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <div class="nav-burger" id="navBurger">
        <div></div><div></div><div></div>
    </div>
</div>

<div class="nav-spacer" style="height:0;"></div>

<?php if (! empty($showFallbackNotice ?? false)): ?>
    <div style="background:#fffbeb;border:1px solid #f59e0b;color:#b45309;padding:12px 24px;text-align:center;font-size:1.5rem;">
        This content is not available in <?= strtoupper($locale) ?>. Showing <?= strtoupper($defaultLocale) ?> version.
    </div>
<?php endif; ?>
