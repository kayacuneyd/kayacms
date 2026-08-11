<?= $this->include('themes/minimal/partials/header'); ?>
<div class="container page-heading">
    <h1><?= esc($term['name'] ?? 'Category') ?></h1>
    <?php if (! empty($term['description'])): ?>
        <p><?= esc($term['description']) ?></p>
    <?php endif; ?>
</div>

<div class="container">
    <?php if (! empty($articles)): ?>
        <ul class="post-list">
            <?php foreach ($articles as $item): ?>
                <li>
                    <h2><a href="<?= localized_url('/content/' . ($item['slug'] ?? '')) ?>"><?= esc($item['title'] ?? '') ?></a></h2>
                    <p class="meta"><?= esc(date('M j, Y', strtotime($item['published_at'] ?? $item['created_at'] ?? 'now'))) ?></p>
                    <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($item['body'] ?? null, $item['excerpt'] ?? null)) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="empty">No content in this <?= esc(strtolower($term['taxonomy_type'] ?? 'category')) ?> yet.</div>
    <?php endif; ?>
</div>
<?= $this->include('themes/minimal/partials/footer'); ?>