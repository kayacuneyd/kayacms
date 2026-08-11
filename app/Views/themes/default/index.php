<?= $this->include('themes/default/partials/header'); ?>
<?php $showSearch = ($theme_config['show_search'] ?? '1') === '1'; ?>
<div class="wrap hero">
    <h1><?= esc($page_heading ?? ($settings['site_name'] ?? 'Welcome')) ?></h1>
    <p><?= esc($page_subheading ?? ($settings['site_description'] ?? '')) ?></p>

    <?php if ($showSearch): ?>
    <form class="search-bar" action="<?= localized_url('/search') ?>" method="get" role="search">
        <input type="search" name="q" value="<?= esc($query ?? '') ?>" placeholder="Search articles..." aria-label="Search">
        <button type="submit">Search</button>
    </form>
    <?php endif; ?>
</div>

<?php if (! empty($featured) && ($theme_config['show_featured'] ?? '1') === '1'): ?>
<div class="wrap">
    <h2>Featured Content</h2>
    <div class="grid">
        <?php foreach ($featured as $item): ?>
            <div class="card">
                <div class="thumb">
                    <?php if (! empty($item->featured_image)): ?>
                        <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <?= esc(mb_strtoupper(mb_substr($item->title ?? 'F', 0, 1))) ?>
                    <?php endif; ?>
                </div>
                <div class="body">
                    <h3><a href="<?= localized_url('/content/' . $item->slug) ?>"><?= esc($item->title) ?></a></h3>
                    <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item->body ?? null, $item->excerpt ?? null)) ?></p>
                    <span class="meta"><?= esc(date('M j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="wrap">
    <div class="grid">
        <?php if (! empty($articles)): ?>
            <?php foreach ($articles as $item): ?>
                <div class="card">
                    <div class="thumb">
                        <?php if (! empty($item->featured_image)): ?>
                            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <?= esc(mb_strtoupper(mb_substr($item->title ?? 'C', 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="body">
                        <h3><a href="<?= localized_url('/content/' . $item->slug) ?>"><?= esc($item->title) ?></a></h3>
                        <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item->body ?? null, $item->excerpt ?? null)) ?></p>
                        <span class="meta"><?= esc(date('M j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty"><?= !empty($query) ? 'No results found for "' . esc($query) . '".' : 'No articles published yet.' ?></div>
        <?php endif; ?>
    </div>

    <?php if (! empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="pager">
            <?php if (($pagination['current_page'] ?? 1) > 1): ?>
                <a class="btn" href="<?= esc($pagination['base_url']) ?>?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">← Newer</a>
            <?php endif; ?>
            <span class="meta" style="align-self:center">Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?></span>
            <?php if (($pagination['current_page'] ?? 1) < ($pagination['total_pages'] ?? 1)): ?>
                <a class="btn" href="<?= esc($pagination['base_url']) ?>?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Older →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->include('themes/default/partials/footer'); ?>
