<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <div class="section-head">
        <h1><?= esc($term['name'] ?? ($page_title ?? 'Kategori')) ?></h1>
        <?php if (! empty($term['description'] ?? '')): ?>
            <p><?= esc($term['description']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (! empty($articles)): ?>
        <div class="blog-grid">
            <?php foreach ($articles as $item): ?>
                <article class="post-card">
                    <div class="thumb">
                        <?php if (! empty($item['featured_image'] ?? null)): ?>
                            <img src="<?= esc($item['featured_image']) ?>" alt="<?= esc($item['title'] ?? '') ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="body">
                        <span class="meta"><?= esc(date('j F Y', strtotime($item['published_at'] ?? $item['created_at'] ?? 'now'))) ?></span>
                        <h3><a href="<?= localized_url('/content/' . ($item['slug'] ?? '')) ?>"><?= esc($item['title'] ?? '') ?></a></h3>
                        <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item['body'] ?? null, $item['excerpt'] ?? null)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty">Bu kategoride henüz içerik yok.</p>
    <?php endif; ?>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>