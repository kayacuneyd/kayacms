<?= $this->include('themes/default/partials/header'); ?>
<div class="wrap hero">
    <h1><?= !empty($query) ? 'Search: ' . esc($query) : 'Search' ?></h1>
    <form class="search-bar" action="<?= localized_url('/search') ?>" method="get" role="search">
        <input type="search" name="q" value="<?= esc($query ?? '') ?>" placeholder="Search articles..." aria-label="Search">
        <button type="submit">Search</button>
    </form>
</div>

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
            <div class="empty"><?= !empty($query) ? 'No results found for "' . esc($query) . '".' : 'Enter a search term above.' ?></div>
        <?php endif; ?>
    </div>
</div>
<?= $this->include('themes/default/partials/footer'); ?>
