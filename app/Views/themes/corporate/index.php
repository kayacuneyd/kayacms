<?php
$cfg            = $theme_config ?? [];
$hero           = is_array($cfg['hero'] ?? null) ? $cfg['hero'] : [];
$introTitle     = $cfg['intro_title'] ?? '';
$introText      = (string) ($cfg['intro_text'] ?? '');
$introImage     = $cfg['intro_image'] ?? '';
$verticalText   = $cfg['vertical_text'] ?? 'K&Z';
$practiceTitle  = $cfg['practice_title'] ?? '';
$practice       = is_array($cfg['practice'] ?? null) ? $cfg['practice'] : [];
$refsTitle      = $cfg['references_title'] ?? '';
$references     = is_array($cfg['references'] ?? null) ? $cfg['references'] : [];
$ctaKicker      = $cfg['cta_kicker'] ?? '';
$ctaTitle       = (string) ($cfg['cta_title'] ?? '');
$ctaPhone       = $cfg['cta_phone'] ?? '';
$ctaPhoneUrl    = $cfg['cta_phone_url'] ?? 'tel:+15551234567';
$ctaBtnText     = $cfg['cta_btn_text'] ?? '';
$ctaBtnUrl      = $cfg['cta_btn_url'] ?? '/iletisim';
$showBlog       = ($cfg['show_blog'] ?? '1') === '1';
$blogArticles   = $articles ?? [];
?>
<?= $this->include('themes/corporate/partials/header'); ?>

<?php if (! empty($hero)): ?>
<section class="hero">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($hero as $slide): ?>
                <div class="swiper-slide hero-slide" style="background-image:url('<?= esc($slide['image'] ?? '') ?>');">
                    <div class="hero-tag">
                        <h1><?= esc($slide['headline'] ?? '') ?></h1>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hero-pagination swiper">
        <div class="swiper-wrapper">
            <?php foreach ($hero as $i => $slide): ?>
                <div class="swiper-slide hp-item">
                    <?php if (! empty($slide['icon'])): ?>
                        <img src="<?= esc($slide['icon']) ?>" alt="">
                    <?php endif; ?>
                    <h4><?= esc($slide['name'] ?? '') ?></h4>
                    <p><?= esc($slide['desc'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($introTitle !== '' || $introText !== ''): ?>
<section class="block" style="padding-top:2rem;">
    <div class="wrap intro">
        <div class="intro-content">
            <?php if ($introTitle !== ''): ?>
                <h2><?= esc($introTitle) ?></h2>
            <?php endif; ?>
            <?php if ($introText !== ''): ?>
                <?php foreach (preg_split('/\r?\n/', $introText) as $paragraph): ?>
                    <?php if (trim($paragraph) !== ''): ?>
                        <p class="c-grey"><?= esc($paragraph) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <h1 class="vertical-txt"><?= esc($verticalText) ?></h1>
    </div>
    <?php if ($introImage !== ''): ?>
        <div class="wrap intro-img">
            <img src="<?= esc($introImage) ?>" alt="<?= esc($introTitle) ?>">
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (! empty($practice)): ?>
<section class="block practice">
    <div class="wrap">
        <div class="section-head center" style="margin:0 auto 4.4rem;">
            <?php if ($practiceTitle !== ''): ?>
                <h2><?= esc($practiceTitle) ?></h2>
            <?php endif; ?>
        </div>
    </div>
    <div class="swiper practice-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($practice as $p): ?>
                <div class="swiper-slide" style="height:auto;">
                    <div class="card">
                        <?php if (! empty($p['icon'])): ?>
                            <img src="<?= esc($p['icon']) ?>" alt="">
                        <?php endif; ?>
                        <h3><?= esc($p['title'] ?? '') ?></h3>
                        <p><?= esc($p['desc'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination bullet-pagination"></div>
    </div>
</section>
<?php endif; ?>

<?php if (! empty($references)): ?>
<section class="block testimonials">
    <div class="wrap">
        <div class="section-head center">
            <h2><?= esc($refsTitle) ?></h2>
        </div>
    </div>
    <div class="swiper refs-swiper" style="max-width:860px;margin:0 auto;padding:0 24px;">
        <div class="swiper-wrapper">
            <?php foreach ($references as $ref): ?>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="mark">&ldquo;</div>
                        <p class="quote"><?= esc($ref['quote'] ?? '') ?></p>
                        <h5><?= esc($ref['name'] ?? '') ?></h5>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination bullet-pagination"></div>
    </div>
</section>
<?php endif; ?>

<?php if ($showBlog && ! empty($blogArticles)): ?>
<section class="block">
    <div class="wrap">
        <div class="section-head center" style="margin:0 auto 4.4rem;">
            <h2>Blog</h2>
        </div>
        <div class="blog-grid">
            <?php foreach ($blogArticles as $item): ?>
                <article class="post-card">
                    <div class="thumb">
                        <?php if (! empty($item->featured_image)): ?>
                            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="body">
                        <span class="meta"><?= esc(date('j F Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></span>
                        <h3><a href="<?= localized_url('/content/' . $item->slug) ?>"><?= esc($item->title) ?></a></h3>
                        <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item->body ?? null, $item->excerpt ?? null)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($ctaTitle !== '' || $ctaKicker !== ''): ?>
<section class="cta">
    <div class="wrap">
        <?php if ($ctaKicker !== ''): ?>
            <h4><?= esc($ctaKicker) ?></h4>
        <?php endif; ?>
        <?php if ($ctaTitle !== ''): ?>
            <h3><?= esc($ctaTitle) ?></h3>
        <?php endif; ?>
        <div class="buttons">
            <?php if ($ctaPhone !== ''): ?>
                <a href="<?= esc($ctaPhoneUrl) ?>" class="btn btn-accent"><?= esc($ctaPhone) ?></a>
            <?php endif; ?>
            <?php if ($ctaBtnText !== ''): ?>
                <a href="<?= esc($ctaBtnUrl) ?>" class="btn"><?= esc($ctaBtnText) ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var hero = new Swiper('.hero-swiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false }
    });

    var heroPagination = new Swiper('.hero-pagination', {
        slidesPerView: 'auto',
        spaceBetween: 40,
        slideToClickedSlide: false
    });

    heroPagination.slides.forEach(function (el, i) {
        el.addEventListener('click', function () { hero.slideToLoop(i); });
    });

    new Swiper('.practice-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: { el: '.practice-swiper .bullet-pagination', clickable: true },
        breakpoints: { 700: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
    });

    new Swiper('.refs-swiper', {
        slidesPerView: 1,
        loop: true,
        autoplay: { delay: 6000, disableOnInteraction: false },
        pagination: { el: '.refs-swiper .bullet-pagination', clickable: true }
    });
});
</script>

<?= $this->include('themes/corporate/partials/footer'); ?>
