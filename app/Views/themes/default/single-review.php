<?= $this->include('themes/default/partials/header'); ?>
<div class="wrap">
    <article class="single">
        <?php if (! empty($item->featured_image)): ?>
            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" style="width:100%;max-height:360px;object-fit:cover;border-radius:8px;margin-bottom:20px;">
        <?php endif; ?>

        <p class="meta" style="color:#2563eb;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Review</p>
        <h1><?= esc($item->title) ?></h1>

        <?php $customdata = $item->custom_data; ?>
        <?php if (! empty($customdata['rating'])): ?>
            <p class="meta">Rating: <?= esc($customdata['rating']) ?>/5</p>
        <?php endif; ?>

        <div class="content">
            <?= \App\Libraries\ContentRenderer::render($item->body ?? null, $item) ?>
        </div>
    </article>

    <?php if (! empty($related)): ?>
        <section class="related" style="margin-top:40px;">
            <h2>Related Content</h2>
            <div class="grid">
                <?php foreach ($related as $r): ?>
                    <div class="card">
                        <div class="body">
                            <h3><a href="<?= localized_url('/content/' . $r->slug) ?>"><?= esc($r->title) ?></a></h3>
                            <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($r->body ?? null, $r->excerpt ?? null)) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<?= $this->include('themes/default/partials/footer'); ?>