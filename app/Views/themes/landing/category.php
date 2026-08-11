<?= $this->include('themes/landing/partials/header'); ?>
<section class="block">
    <div class="wrap">
        <div class="sec-head">
            <h1 style="font-size:2rem;"><?= esc($term['name'] ?? ($page_title ?? 'Category')) ?></h1>
            <?php if (! empty($term['description'] ?? '')): ?>
                <p><?= esc($term['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php if (! empty($articles)): ?>
            <div class="blog-grid">
                <?php foreach ($articles as $item): ?>
                    <article class="post-card">
                        <div class="thumb">
                            <?php if (! empty($item['featured_image'])): ?>
                                <img src="<?= esc($item['featured_image']) ?>" alt="<?= esc($item['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <?= esc(mb_strtoupper(mb_substr($item['title'] ?? 'K', 0, 1))) ?>
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <h3><a href="<?= localized_url('/content/' . $item['slug']) ?>"><?= esc($item['title']) ?></a></h3>
                            <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item['body'] ?? null, $item['excerpt'] ?? null)) ?></p>
                            <span class="meta"><?= esc(date('M j, Y', strtotime($item['published_at'] ?? $item['created_at'] ?? 'now'))) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">No content in this category yet.</p>
        <?php endif; ?>
    </div>
</section>
<?= $this->include('themes/landing/partials/footer'); ?>