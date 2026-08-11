<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <div class="section-head">
        <h1><?= ! empty($query) ? esc('Arama: ' . $query) : 'Arama' ?></h1>
        <form action="<?= localized_url('/search') ?>" method="get" role="search" style="display:flex;gap:1rem;max-width:560px;margin-top:2rem;">
            <input type="search" name="q" value="<?= esc($query ?? '') ?>" placeholder="Makalelerde ara..." aria-label="Search" style="flex:1;padding:1.2rem 1.6rem;border:1px solid var(--line);font:inherit;font-size:1.6rem;">
            <button type="submit" class="btn btn-accent">Ara</button>
        </form>
    </div>

    <?php if (! empty($articles)): ?>
        <div class="blog-grid">
            <?php foreach ($articles as $item): ?>
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
    <?php else: ?>
        <p class="empty">Sonuç bulunamadı<?= ! empty($query) ? ' "' . esc($query) . '"' : '' ?>.</p>
    <?php endif; ?>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>