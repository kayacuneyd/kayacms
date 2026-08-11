<?= $this->include('themes/landing/partials/header'); ?>
<section class="block">
    <div class="wrap">
        <div class="sec-head">
            <h1 style="font-size:2rem;"><?= ! empty($query) ? esc('Search: ' . $query) : 'Search' ?></h1>
        </div>
        <div class="hero-actions" style="margin-bottom:40px;">
            <form action="<?= localized_url('/search') ?>" method="get" role="search" style="display:flex;gap:10px;width:100%;max-width:560px;">
                <input type="search" name="q" value="<?= esc($query ?? '') ?>" placeholder="Search articles..." aria-label="Search"
                       style="flex:1;padding:12px 16px;background:var(--surface);color:var(--ink);border:1px solid var(--border);border-radius:999px;font-size:1rem;">
                <button type="submit" class="btn btn-brand">Search</button>
            </form>
        </div>
        <?php if (! empty($articles)): ?>
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
        <?php else: ?>
            <p class="empty">No results found<?= ! empty($query) ? ' for "' . esc($query) . '"' : '' ?>.</p>
        <?php endif; ?>
    </div>
</section>
<?= $this->include('themes/landing/partials/footer'); ?>