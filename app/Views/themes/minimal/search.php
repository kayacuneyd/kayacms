<?= $this->include('themes/minimal/partials/header'); ?>
<div class="container page-heading">
    <h1><?= !empty($query) ? 'Search: ' . esc($query) : 'Search' ?></h1>
</div>

<div class="container">
    <form class="search-bar" action="<?= localized_url('/search') ?>" method="get" role="search">
        <input type="search" name="q" value="<?= esc($query ?? '') ?>" placeholder="Search..." aria-label="Search">
        <button type="submit">Search</button>
    </form>
</div>

<div class="container">
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
        <div class="empty"><?= !empty($query) ? 'No results found for "' . esc($query) . '".' : 'Enter a search term above.' ?></div>
    <?php endif; ?>
</div>
<?= $this->include('themes/minimal/partials/footer'); ?>