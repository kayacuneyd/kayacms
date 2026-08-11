<?= $this->include('themes/minimal/partials/header'); ?>
<div class="container page-heading">
    <h1><?= esc($page_heading ?? ($settings['site_name'] ?? 'Welcome')) ?></h1>
    <p><?= esc($page_subheading ?? ($settings['site_description'] ?? '')) ?></p>
</div>

<?php if (($theme_config['show_search'] ?? '1') === '1'): ?>
<div class="container">
    <form class="search-bar" action="<?= localized_url('/search') ?>" method="get" role="search">
        <input type="search" name="q" value="<?= esc($query ?? '') ?>" placeholder="Search..." aria-label="Search">
        <button type="submit">Search</button>
    </form>
</div>
<?php endif; ?>

<div class="container">
    <?php if (! empty($featured)): ?>
        <p class="meta" style="margin-bottom:0;">Featured</p>
        <ul class="post-list" style="padding-bottom:20px;">
            <?php foreach ($featured as $item): ?>
                <li>
                    <h2><a href="<?= localized_url('/content/' . $item->slug) ?>"><?= esc($item->title) ?></a></h2>
                    <?php if (($theme_config['show_author'] ?? '1') === '1' && ! empty($item->author_id)): ?>
                        <p class="meta"><?= esc(date('M j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></p>
                    <?php else: ?>
                        <p class="meta"><?= esc(date('M j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></p>
                    <?php endif; ?>
                    <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item->body ?? null, $item->excerpt ?? null)) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (! empty($articles)): ?>
        <ul class="post-list">
            <?php foreach ($articles as $item): ?>
                <li>
                    <h2><a href="<?= localized_url('/content/' . $item->slug) ?>"><?= esc($item->title) ?></a></h2>
                    <p class="meta"><?= esc(date('M j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></p>
                    <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item->body ?? null, $item->excerpt ?? null)) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="empty"><?= !empty($query) ? 'No results found for "' . esc($query) . '".' : 'No articles published yet.' ?></div>
    <?php endif; ?>

    <?php if (! empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="pager">
            <?php if (($pagination['current_page'] ?? 1) > 1): ?>
                <a href="<?= esc($pagination['base_url']) ?>?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>">← Newer</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <span class="meta">Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?></span>
            <?php if (($pagination['current_page'] ?? 1) < ($pagination['total_pages'] ?? 1)): ?>
                <a href="<?= esc($pagination['base_url']) ?>?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>">Older →</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->include('themes/minimal/partials/footer'); ?>
